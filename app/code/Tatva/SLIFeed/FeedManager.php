<?php

namespace Tatva\SLIFeed;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class FeedManager extends \SLI\Feed\FeedManager
{
    protected $filesystem;
    protected $helper;
    protected $updateFeedGenerator;
    protected $_scopeConfig;


    public function __construct(\SLI\Feed\FeedGenerator $feedGenerator, 
            \SLI\Feed\Helper\FTPUpload $ftpUpload, 
            \SLI\Feed\Logging\LoggerFactoryInterface $loggerFactory,
            \Magento\Store\Model\StoreManagerInterface $storeManager, 
            \SLI\Feed\Helper\GeneratorHelper $generatorHelper, 
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
            \SLI\Feed\Model\GenerateFlag $generateFlag,
            Filesystem $filesystem,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Tatva\SLIFeed\Helper\GeneratorHelper $helper,
            \Tatva\SLIFeed\FeedGenerator $updateFeedGenerator) {
        $this->filesystem = $filesystem;
        $this->helper = $helper;
        $this->updateFeedGenerator = $updateFeedGenerator;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($feedGenerator, $ftpUpload, $loggerFactory, $storeManager, $generatorHelper, $localeDate, $generateFlag);
    }
    
    public function updateAllStores($ftp = null, $force = false)
    {
        $results = [];

        // test before starting at all
        if (!$this->acquireLock($force)) {
            $results['all']['status'] = static::STATUS_LOCKED;
            return $results;
        }

        // init flag data for UI
        $flagData = [
            'has_errors' => false,
            'timeout_reached' => false,
            'message' => '',
        ];
        $this->generateFlag->setFlagData($flagData)->save();

             $storeId = 1;
            $results[$storeId]['name'] = $this->storeManager->getStore(1)->getName();
            $results[$storeId]['status'] = $this->doUpdateForStoreId($storeId, $ftp);

        $this->generateFlag->release($results, $this->localeDate->date());

        return $results;
    }

      public function processAllStores($ftp = null, $force = false)
    {
        $results = [];

        // test before starting at all
        if (!$this->acquireLock($force)) {
            $results['all']['status'] = static::STATUS_LOCKED;
            return $results;
        }

        // init flag data for UI
        $flagData = [
            'has_errors' => false,
            'timeout_reached' => false,
            'message' => '',
        ];
        $this->generateFlag->setFlagData($flagData)->save();


            $storeId = 1;
            $results[$storeId]['name'] = $this->storeManager->getStore(1)->getName();
            $results[$storeId]['status'] = $this->doProcessForStoreId($storeId, $ftp);
 
        $this->generateFlag->release($results, $this->localeDate->date());

        return $results;
    }
    
    protected function doProcessForStoreId($storeId, $ftp)
    {
        $logger = $this->loggerFactory->getGeneralLogger();

        if (!$this->generatorHelper->isAllowed($storeId)) {
            $logger->warning(sprintf('Feed generation disabled for storeId [%s]', $storeId));

            return static::STATUS_DISABLED;
        }

        try {

            $new_date = date("Ymd");
            $time = date('Hi',time());

            $feedFilename = sprintf($this->helper->getFeedFileTemplate(), $new_date,$time);
            if (!$this->feedGenerator->generateForStoreId($storeId, $feedFilename)) {
                $logger->error(sprintf('[%s] Feed generation failed', $storeId));

                return static::STATUS_FAILED;
            }

            // get ftp enabled/disabled settings from UI or from command line ($ftp)
            if ((null === $ftp && $this->ftpUpload->isAllowed($storeId)) || $ftp) {
                if (!$this->ftpUpload->writeFileToFTP($feedFilename, $storeId)) {
                    $logger->error(sprintf('[%s] FTP failed', $storeId));

                    return static::STATUS_FAILED;
                }
            }
        } catch (\Exception $e) {
            $logger->error(sprintf('[%s] Feed process failed: %s', $storeId, $e->getMessage()), ['exception' => $e]);
            $this->generateFlag->setError($e);

            return static::STATUS_FAILED;
        }

        return static::STATUS_SUCCESSFUL;
    }

    protected function doUpdateForStoreId($storeId, $ftp)
    {
        
        $logger = $this->loggerFactory->getGeneralLogger();

        if (!$this->generatorHelper->isAllowed($storeId)) {
            $logger->warning(sprintf('Feed generation disabled for storeId [%s]', $storeId));

            return static::STATUS_DISABLED;
        }
        
        try {
            
            $date = $this->_scopeConfig->getValue('sli_feed_generation/feed/date');
            $new_date = date("Ymd",strtotime($date));
            $time = date('Hi',time());

            $feedFilename = sprintf($this->helper->getFeedUpdateFileTemplate(), $new_date,$time);
            if (!$this->updateFeedGenerator->updateForStoreId($storeId, $feedFilename)) {
                $logger->error(sprintf('[%s] Feed generation failed', $storeId));

                return static::STATUS_FAILED;
            }

            // get ftp enabled/disabled settings from UI or from command line ($ftp)
            if ((null === $ftp && $this->ftpUpload->isAllowed($storeId)) || $ftp) {
                if (!$this->ftpUpload->writeFileToFTP($feedFilename, $storeId)) {
                    $logger->error(sprintf('[%s] FTP failed', $storeId));

                    return static::STATUS_FAILED;
                }
            }
        } catch (\Exception $e) {
            $logger->error(sprintf('[%s] Feed process failed: %s', $storeId, $e->getMessage()), ['exception' => $e]);
            $this->generateFlag->setError($e);

            return static::STATUS_FAILED;
        }

        return static::STATUS_SUCCESSFUL;
    }
    
}