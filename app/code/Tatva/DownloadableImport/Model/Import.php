<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Tatva\DownloadableImport\Model;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\ImportExport\Helper\Data as DataHelper;
use Magento\ImportExport\Model\Export\Adapter\CsvFactory;
use Magento\ImportExport\Model\Import\AbstractEntity as ImportAbstractEntity;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Model\Import\Adapter;
use Magento\ImportExport\Model\Import\ConfigInterface;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\Entity\Factory;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use Magento\ImportExport\Model\Source\Import\AbstractBehavior;
use Magento\ImportExport\Model\Source\Import\Behavior\Factory as BehaviorFactory;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;
use Magento\ImportExport\Model\History;

/**
 * Import model
 *
 * @api
 *
 * @method string getBehavior() getBehavior()
 * @method self setEntity() setEntity(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Import extends \Magento\ImportExport\Model\Import
{
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        DataHelper $importExportData,
        ScopeConfigInterface $coreConfig,
        ConfigInterface $importConfig,
        Factory $entityFactory,
        Data $importData,
        CsvFactory $csvFactory,
        FileTransferFactory $httpFactory,
        UploaderFactory $uploaderFactory,
        BehaviorFactory $behaviorFactory,
        IndexerRegistry $indexerRegistry,
        History $importHistoryModel,
        DateTime $localeDate,
        array $data = [],
        ManagerInterface $messageManager = null,
        Random $random = null
    ) {
        $this->_importExportData = $importExportData;
        $this->_coreConfig = $coreConfig;
        $this->_importConfig = $importConfig;
        $this->_entityFactory = $entityFactory;
        $this->_importData = $importData;
        $this->_csvFactory = $csvFactory;
        $this->_httpFactory = $httpFactory;
        $this->_uploaderFactory = $uploaderFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->_behaviorFactory = $behaviorFactory;
        $this->_filesystem = $filesystem;
        $this->importHistoryModel = $importHistoryModel;
        $this->localeDate = $localeDate;
        $this->messageManager = $messageManager ?: ObjectManager::getInstance()
            ->get(ManagerInterface::class);
        $this->random = $random ?: ObjectManager::getInstance()
            ->get(Random::class);
        parent::__construct($logger, $filesystem,$importExportData,$coreConfig,$importConfig,$entityFactory,$importData,$csvFactory,$httpFactory,$uploaderFactory,$behaviorFactory,$indexerRegistry,$importHistoryModel,$localeDate,$data,$messageManager,$random);
    }

    protected function _getSourceAdapter($sourceFile)
    {
        return \Tatva\DownloadableImport\Model\Import\Adapter::findAdapterFor(
            $sourceFile,
            $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT),
            $this->getData(self::FIELD_FIELD_SEPARATOR)
        );
    }

    public function getOperationResultMessages(ProcessingErrorAggregatorInterface $validationResult)
    {
        $messages = [];
        if ($this->getProcessedRowsCount()) {
            if (($validationResult->isErrorLimitExceeded() && 
                $validationResult->getValidationStrategy() === ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR) ||
                ($validationResult->getErrorsCount() > $validationResult->getAllowedErrorsCount() &&
                $validationResult->getValidationStrategy() === ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS)) {    
                $messages[] = __('Data validation failed. Please fix the following errors and upload the file again.');

                // errors info
                foreach ($validationResult->getRowsGroupedByErrorCode() as $errorMessage => $rows) {
                    $error = $errorMessage . ' ' . __('in row(s)') . ': ' . implode(', ', $rows);
                    $messages[] = $error;
                }
            } else {
                if ($this->isImportAllowed()) {
                    $messages[] = __('The validation is complete.');
                } else {
                    $messages[] = __('The file is valid, but we can\'t import it for some reason.');
                }
            }

            $messages[] = __(
                'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                $this->getProcessedRowsCount(),
                $this->getProcessedEntitiesCount(),
                $validationResult->getInvalidRowsCount(),
                $validationResult->getErrorsCount(
                    [
                        ProcessingError::ERROR_LEVEL_CRITICAL,
                        ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                    ]
                )
            );
        } else {
            $messages[] = __('This file does not contain any data.');
        }
        return $messages;
    }

    public function validateSource(AbstractSource $source)
    {
        $this->addLogComment(__('Begin data validation'));

        $errorAggregator = $this->getErrorAggregator();
        $errorAggregator->initValidationStrategy(
            $this->getData(self::FIELD_NAME_VALIDATION_STRATEGY),
            $this->getData(self::FIELD_NAME_ALLOWED_ERROR_COUNT)
        );

        try {
            $adapter = $this->_getEntityAdapter()->setSource($source);
            $adapter->validateData();
        } catch (\Exception $e) {
            $errorAggregator->addError(
                AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
                ProcessingError::ERROR_LEVEL_CRITICAL,
                null,
                null,
                $e->getMessage()
            );
        }

        $messages = $this->getOperationResultMessages($errorAggregator);
        $this->addLogComment($messages);

        //$result = !$errorAggregator->isErrorLimitExceeded();

        $result = ($errorAggregator->getValidationStrategy() === ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR) ? 
                        !$errorAggregator->getErrorsCount() : 
                        $errorAggregator->getErrorsCount() <= $errorAggregator->getAllowedErrorsCount();
        if ($result) {
            $this->addLogComment(__('Import data validation is complete.'));
        }
        return $result;
    }

   
}
