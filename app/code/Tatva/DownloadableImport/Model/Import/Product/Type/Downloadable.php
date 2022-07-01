<?php
/**
 * Import entity of downloadable product type
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\DownloadableImport\Model\Import\Product\Type;

use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\Downloadable\Model\Url\DomainValidator;
use Magento\Framework\EntityManager\MetadataPool;
use \Magento\Store\Model\Store;

/**
 * Class Downloadable
 *
 * phpcs:disable Magento2.Commenting.ConstantsPHPDocFormatting
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Downloadable extends \Magento\DownloadableImportExport\Model\Import\Product\Type\Downloadable
{
    /**
     * Pair value separator.
     */
    const PAIR_VALUE_SEPARATOR = '=';

    /**
     * Default sort order
     */
    const DEFAULT_SORT_ORDER = 0;

    /**
     * Default number of downloads
     */
    const DEFAULT_NUMBER_OF_DOWNLOADS = 'unlimited';

    /**
     * Default is shareable
     */
    const DEFAULT_IS_SHAREABLE = 2;

    /**
     * Default website id
     */
    const DEFAULT_WEBSITE_ID = 0;

    /**
     * Patch for downloadable files samples
     */
    const DOWNLOADABLE_PATCH_SAMPLES = 'downloadable/files/samples';

    /**
     * Patch for downloadable files links
     */
    const DOWNLOADABLE_PATCH_LINKS = 'downloadable/files/links';

    /**
     * Patch for downloadable files link samples
     */
    const DOWNLOADABLE_PATCH_LINK_SAMPLES = 'downloadable/files/link_samples';

    /**
     * Type option for url
     */
    const URL_OPTION_VALUE = 'url';

    /**
     * Type option for file
     */
    const FILE_OPTION_VALUE = 'file';

    const TITLE = 'title';

    /**
     * Column with downloadable samples
     */
    const COL_DOWNLOADABLE_SAMPLES = 'downloadable_samples';

    /**
     * Column with downloadable links
     */
    const COL_DOWNLOADABLE_LINKS = 'downloadable_links';

    /**
     * Default group title
     */
    const DEFAULT_GROUP_TITLE = 'links';

    /**
     * Default links can be purchased separately
     */
    const CUSTOM_DEFAULT_PURCHASED_SEPARATELY = 0;

    /**
     * Error codes.
     */
    const ERROR_OPTIONS_NOT_FOUND = 'optionsNotFound';

    const ERROR_GROUP_TITLE_NOT_FOUND = 'groupTitleNotFound';

    const ERROR_OPTION_NO_TITLE = 'optionNoTitle';

    const ERROR_MOVE_FILE = 'moveFile';

    const ERROR_COLS_IS_EMPTY = 'emptyOptions';

    private const ERROR_LINK_URL_NOT_IN_DOMAIN_WHITELIST = 'linkUrlNotInDomainWhitelist';

    private const ERROR_SAMPLE_URL_NOT_IN_DOMAIN_WHITELIST = 'sampleUrlNotInDomainWhitelist';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::ERROR_OPTIONS_NOT_FOUND => 'Options for downloadable products not found',
        self::ERROR_GROUP_TITLE_NOT_FOUND => 'Group titles not found for downloadable products',
        self::ERROR_OPTION_NO_TITLE => 'Option no title',
        self::ERROR_MOVE_FILE => 'Error move file',
        self::ERROR_COLS_IS_EMPTY => 'Missing sample and links data for the downloadable product',
        self::ERROR_LINK_URL_NOT_IN_DOMAIN_WHITELIST =>
            'Link URL\'s domain is not in list of downloadable_domains in env.php.',
        self::ERROR_SAMPLE_URL_NOT_IN_DOMAIN_WHITELIST =>
            'Sample URL\'s domain is not in list of downloadable_domains in env.php.'
    ];

    /**
     * Entity model parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Ids products
     *
     * @var array
     */
    protected $productIds = [];

    /**
     * Array of cached options.
     *
     * @var array
     */
    protected $cachedOptions = [
        'link' => [],
        'sample' => []
    ];

    /**
     * Instance of empty sample
     *
     * @var array
     */
    protected $dataSample = [
        'sample_id' => null,
        'product_id' => null,
        'sample_url' => null,
        'sample_file' => null,
        'sample_type' => null,
        'sort_order' => self::DEFAULT_SORT_ORDER
    ];

    /**
     * Instance of empty sample title
     *
     * @var array
     */
    protected $dataSampleTitle = [
        'sample_id' => null,
        'store_id' => Store::DEFAULT_STORE_ID,
        'title' => null
    ];

    /**
     * Instance of empty link
     *
     * @var array
     */
    protected $dataLink = [
        'link_id' => null,
        'product_id' => null,
        'sort_order' => self::DEFAULT_SORT_ORDER,
        'number_of_downloads' => self::DEFAULT_NUMBER_OF_DOWNLOADS,
        'is_shareable' => self::DEFAULT_IS_SHAREABLE,
        'link_url' => null,
        'link_file' => null,
        'link_type' => null,
        'sample_url' => null,
        'sample_file' => null,
        'sample_type' => null
    ];

    /**
     * Instance of empty link title
     *
     * @var array
     */
    protected $dataLinkTitle = [
        'link_id' => null,
        'store_id' => Store::DEFAULT_STORE_ID,
        'title' => 'label'
    ];

    /**
     * Instance of empty link price
     *
     * @var array
     */
    protected $dataLinkPrice = [
        'price_id' => null,
        'link_id' => null,
        'website_id' => self::DEFAULT_WEBSITE_ID,
        'price' => 0
    ];

    /**
     * Option link mapping.
     *
     * @var array
     */
    protected $optionLinkMapping = [
        'sortorder' => 'sort_order',
        'downloads' => 'number_of_downloads',
        'shareable' => 'is_shareable',
        'url' => 'link_url',
        'file' => 'link_file',
    ];

    /**
     * Option sample mapping.
     *
     * @var array
     */
    protected $optionSampleMapping = [
        'sortorder' => 'sort_order',
        'url' => 'sample_url',
        'file' => 'sample_file',
    ];

    /**
     * Num row parsing file
     */
    protected $rowNum;

    /**
     * @var \Magento\DownloadableImportExport\Helper\Uploader
     */
    protected $uploaderHelper;

    /**
     * @var \Magento\DownloadableImportExport\Helper\Data
     */
    protected $downloadableHelper;

    /**
     * @var DomainValidator
     */
    private $domainValidator;

    /**
     * Downloadable constructor
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param array $params
     * @param \Magento\DownloadableImportExport\Helper\Uploader $uploaderHelper
     * @param \Magento\DownloadableImportExport\Helper\Data $downloadableHelper
     * @param DomainValidator $domainValidator
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrSetColFac,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $prodAttrColFac,
        \Magento\Framework\App\ResourceConnection $resource,
        array $params,
        \Magento\DownloadableImportExport\Helper\Uploader $uploaderHelper,
        \Magento\DownloadableImportExport\Helper\Data $downloadableHelper,
        DomainValidator $domainValidator,
        MetadataPool $metadataPool = null
    ) {
        parent::__construct($attrSetColFac, $prodAttrColFac, $resource, $params,$uploaderHelper,$downloadableHelper,$domainValidator, $metadataPool);
        $this->parameters = $this->_entityModel->getParameters();
        $this->_resource = $resource;
        $this->uploaderHelper = $uploaderHelper;
        $this->domainValidator = $domainValidator;
        $this->downloadableHelper = $downloadableHelper;
    }
    

    /**
     * Get fill data options with key link
     *
     * @param array $options
     * @return array
     */
    protected function fillDataTitleLink(array $options)
    {
        $result = [];
        $existingOptions = $this->connection->fetchAll(
            $this->connection->select()->from(
                ['dl' => $this->_resource->getTableName('downloadable_link')],
                [
                    'link_id',
                    'product_id',
                    'sort_order',
                    'number_of_downloads',
                    'is_shareable',
                    'link_url',
                    'link_file',
                    'link_type',
                    'sample_url',
                    'sample_file',
                    'sample_type'
                ]
            )->joinLeft(
                ['dlp' => $this->_resource->getTableName('downloadable_link_price')],
                'dl.link_id = dlp.link_id AND dlp.website_id=' . self::DEFAULT_WEBSITE_ID,
               // ['price_id', 'website_id']
                ['price_id']
            )->where(
                'product_id in (?)',
                $this->productIds
            )
        );
        foreach ($options as $option) {
            $existOption = $this->downloadableHelper->fillExistOptions($this->dataLinkTitle, $option, $existingOptions);
            if (!empty($existOption)) {
                $result['title'][] = $existOption;
            }
            $existOption = $this->downloadableHelper->fillExistOptions($this->dataLinkPrice, $option, $existingOptions);
            if (!empty($existOption)) {
                $result['price'][] = $existOption;
            }
        }
        		
        return $result;
    }
   
   
    protected function sampleGroupTitle(array $rowData)
    {
        $result = '';
        if (isset($rowData[self::COL_DOWNLOADABLE_SAMPLES])) {
            $options = explode(
                ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR,
                $rowData[self::COL_DOWNLOADABLE_SAMPLES]
            );
            foreach ($options as $option) {
                $arr = $this->parseSampleOption(explode($this->_entityModel->getMultipleValueSeparator(), $option));
                if (!isset($arr['group_title'])) {
                    $result =self::DEFAULT_GROUP_TITLE;
                    break;
                }
            }
        }
        return $result;
    }

    public function isRowValid(array $rowData, $rowNum, $isNewProduct = true)
    {
        $this->rowNum = $rowNum;
        $error = false;
        if (!$this->downloadableHelper->isRowDownloadableNoValid($rowData) && $isNewProduct) {
            $this->_entityModel->addRowError(self::ERROR_OPTIONS_NOT_FOUND, $this->rowNum);
            $error = true;
        }
        if ($this->downloadableHelper->isRowDownloadableEmptyOptions($rowData)) {
            $this->_entityModel->addRowError(self::ERROR_COLS_IS_EMPTY, $this->rowNum);
            $error = true;
        }
        if ($this->isRowValidSample($rowData) || $this->isRowValidLink($rowData)) {
            $error = true;
        }
        return !$error;
    }

    /**
     * Parse options for products
     *
     * @param array $rowData
     * @param int $entityId
     * @return $this
     */
    protected function parseOptions(array $rowData, $entityId)
    {
        if($rowData["store_view_code"] == "default" ){
            parent::parseOptions($rowData, $entityId);
        }
        return $this;

    }

    protected function isRowValidLink(array $rowData)
    {
        $hasLinkData = (
            isset($rowData[self::COL_DOWNLOADABLE_LINKS]) &&
            $rowData[self::COL_DOWNLOADABLE_LINKS] != ''
        );

        if (!$hasLinkData) {
            return false;
        }
        $rowData[self::COL_DOWNLOADABLE_LINKS]=$rowData[self::COL_DOWNLOADABLE_LINKS].','.self::TITLE.'='.$this->dataLinkTitle['title'];
        $linkData = $this->prepareLinkData($rowData[self::COL_DOWNLOADABLE_LINKS]);

        $result = $result ?? $this->isTitle($linkData);

        foreach ($linkData as $link) {
            if ($this->hasDomainNotInWhitelist($link, 'link_type', 'link_url')) {
                $this->_entityModel->addRowError(self::ERROR_LINK_URL_NOT_IN_DOMAIN_WHITELIST, $this->rowNum);
                $result = true;
            }

            if ($this->hasDomainNotInWhitelist($link, 'sample_type', 'sample_url')) {
                $this->_entityModel->addRowError(self::ERROR_SAMPLE_URL_NOT_IN_DOMAIN_WHITELIST, $this->rowNum);
                $result = true;
            }
        }

        return $result;
    }

    protected function addAdditionalAttributes(array $rowData)
    {
        return [
            'samples_title' => $this->sampleGroupTitle($rowData),
            'links_title' => $this->linksAdditionalAttributes($rowData, 'group_title', self::DEFAULT_GROUP_TITLE),
            'links_purchased_separately' => $this->linksAdditionalAttributes(
                $rowData,
                'purchased_separately',
                self::CUSTOM_DEFAULT_PURCHASED_SEPARATELY
            )
        ];
    }

    /**
     * Does link contain url not in whitelist?
     *
     * @param array $link
     * @param string $linkTypeKey
     * @param string $linkUrlKey
     * @return bool
     */
    private function hasDomainNotInWhitelist(array $link, string $linkTypeKey, string $linkUrlKey): bool
    {
        return (
            isset($link[$linkTypeKey]) &&
            $link[$linkTypeKey] === 'url' &&
            isset($link[$linkUrlKey]) &&
            strlen($link[$linkUrlKey]) &&
            !$this->domainValidator->isValid($link[$linkUrlKey])
        );
    }

}
