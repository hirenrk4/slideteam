<?php

namespace Tatva\Catalog\Block\Category;

/**
 * Class View
 * @api
 * @package Magento\Catalog\Block\Category
 * @since 100.0.2
 */
use Magento\Catalog\Model\Category;

class View extends \Magento\Catalog\Block\Category\View {

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;

    protected $_urlInterface;
    protected $_resourceConnection;
   

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, 
            \Magento\Catalog\Model\Layer\Resolver $layerResolver, 
            \Magento\Framework\Registry $registry, \Magento\Catalog\Helper\Category $categoryHelper, 
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Model\CategoryFactory $catalogFactory, 
            Category $model,
            \Magento\Framework\UrlInterface $urlInterface,
            \Magento\Framework\App\ResourceConnection $resourceConnection,
            array $data = []
    ) {
        $this->_categoryHelper = $categoryHelper;
        $this->_catalogLayer = $layerResolver->get();
        $this->_coreRegistry = $registry;
        $this->_scopeConfig = $scopeConfig;
        $this->_catalogFactory = $catalogFactory;
        $this->model = $model;
        $this->_urlInterface = $urlInterface;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->createBlock(\Magento\Catalog\Block\Breadcrumbs::class);

        $category = $this->getCurrentCategory();
        if ($category) {
            $title = $category->getMetaTitle();
            if ($title) {
                $this->pageConfig->getTitle()->set($title);
            }
            $description = $category->getMetaDescription();
            if ($description) {
                $this->pageConfig->setDescription($description);
            }
            $keywords = $category->getMetaKeywords();
            if ($keywords) {
                $this->pageConfig->setKeywords($keywords);
            }
            if ($this->_categoryHelper->canUseCanonicalTag()) {
                $this->pageConfig->addRemotePageAsset(
                    $this->_urlInterface->getCurrentUrl(),
                    'canonical',
                    ['attributes' => ['rel' => 'canonical']]
                );
            }

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($this->getCurrentCategory()->getName());
            }
        }

        return $this;
    }

    public function getConfigValue($confidValue) {

        return $this->_scopeConfig->getValue(
                        $confidValue, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getRegistryVar($registryVar) {
        return $this->_coreRegistry->registry($registryVar);
    }

    public function getcategoryById($id) {
        return $this->_catalogFactory->create()->load($id);
    }

    public function getChildrenCategories($category)
    {
        $collection = $category->getCollection();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection->addAttributeToSelect(
            'url_key'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'all_children'
        )->addAttributeToSelect(
            'is_anchor'
        )->addAttributeToFilter(
            'is_active',
            1
        )->addIdFilter(
            $category->getChildren()
        )->setOrder(
            'name',
            \Magento\Framework\DB\Select::SQL_ASC
        );

        return $collection;
    }

    public function getCategoryCollectionById($catId) {
        $categoryCollection = $this->model->getCollection()->addAttributeToSelect('category_header')->addFieldToFilter('category_header', array('neq' => ''))->addFieldToFilter('entity_id', $catId);
        return $categoryCollection;
    }

    public function getParentCategoryId($id) {
        return $this->_catalogFactory->create()->load($id)->getParentId();
    }

    public function getCategoryLevel($id) {
        return $this->_catalogFactory->create()->load($id)->getLevel();
    }

    public function getOnePageCategories()
    {
        $onepageIds = $this->_coreRegistry->registry("onepage_cat_ids");
        $categoryId = $this->_scopeConfig->getValue('resume/general/onepagecategoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $curCat = $this->getRegistryVar('current_category');
        $catid = $curCat->getId();

        if(empty($onepageIds))
        {
            $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryId;
            $connection = $this->_resourceConnection->getConnection();
            $results = $connection->fetchAll($sql);
            if(!empty($results))
            {
                $onepageIds = explode(',',$results[0]['category_list']);
                $this->_coreRegistry->register("onepage_cat_ids",$onepageIds);
                if(in_array($catid,$onepageIds))
                {
                    return 1;
                }
            }
        }
        else
        {
            if(in_array($catid,$onepageIds))
            {
                return 1;
            }
        }
        return 0;
    }

    public function getDocumentReportCategories()
    {
        $documentreportIds = $this->_coreRegistry->registry("documentreport_cat_ids");
        $categoryId = $this->_scopeConfig->getValue('resume/general/document_report_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $curCat = $this->getRegistryVar('current_category');
        $catid = $curCat->getId();

        if(empty($documentreportIds))
        {
            $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryId;
            $connection = $this->_resourceConnection->getConnection();
            $results = $connection->fetchAll($sql);
            if(!empty($results))
            {
                $documentreportIds = explode(',',$results[0]['category_list']);
                $this->_coreRegistry->register("documentreport_cat_ids",$documentreportIds);
                if(in_array($catid,$documentreportIds))
                {
                    return 1;
                }
            }
        }
        else
        {
            if(in_array($catid,$documentreportIds))
            {
                return 1;
            }
        }
        return 0;
    }

    public function getUkrainCrisisCategories()
    {
        $documentreportIds = $this->_coreRegistry->registry("ukraincrisis_cat_ids");
        $categoryId = $this->_scopeConfig->getValue('resume/general/ukrain_crisis_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $curCat = $this->getRegistryVar('current_category');
        $catid = $curCat->getId();

        if(empty($documentreportIds))
        {
            $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryId;
            $connection = $this->_resourceConnection->getConnection();
            $results = $connection->fetchAll($sql);
            if(!empty($results))
            {
                $documentreportIds = explode(',',$results[0]['category_list']);
                $this->_coreRegistry->register("ukraincrisis_cat_ids",$documentreportIds);
                if(in_array($catid,$documentreportIds))
                {
                    return 1;
                }
            }
        }
        else
        {
            if(in_array($catid,$documentreportIds))
            {
                return 1;
            }
        }
        return 0;
    }

    public function getLetterheadCategories()
    {
        $letterheadIds = $this->_coreRegistry->registry("letterhead_cat_ids");
        $categoryId = $this->_scopeConfig->getValue('resume/general/letterhead_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $curCat = $this->getRegistryVar('current_category');
        $catid = $curCat->getId();

        if(empty($letterheadIds))
        {
            $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryId;
            $connection = $this->_resourceConnection->getConnection();
            $results = $connection->fetchAll($sql);
            if(!empty($results))
            {
                $letterheadIds = explode(',',$results[0]['category_list']);
                $this->_coreRegistry->register("letterhead_cat_ids",$letterheadIds);
                if(in_array($catid,$letterheadIds))
                {
                    return 1;
                }
            }
        }
        else
        {
            if(in_array($catid,$letterheadIds))
            {
                return 1;
            }
        }
        return 0;
    }

    public function getOpCategory()
    {
        $categoryId = $this->_scopeConfig->getValue('resume/general/onepagecategoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $opCat = $this->_catalogFactory->create()->load($categoryId);
        return $opCat;
    }

    public function getDocReportCategory()
    {
        $categoryId = $this->_scopeConfig->getValue('resume/general/document_report_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $docrCat = $this->_catalogFactory->create()->load($categoryId);
        return $docrCat;
    }

    public function getUkrainCrisisCategory()
    {
        $categoryId = $this->_scopeConfig->getValue('resume/general/ukrain_crisis_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $docrCat = $this->_catalogFactory->create()->load($categoryId);
        return $docrCat;
    }

    public function getLetterheadCategory()
    {
        $categoryId = $this->_scopeConfig->getValue('resume/general/letterhead_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $letheadCat = $this->_catalogFactory->create()->load($categoryId);
        return $letheadCat;
    }
}
