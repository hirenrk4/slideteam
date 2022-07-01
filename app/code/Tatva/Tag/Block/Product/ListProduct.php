<?php

namespace Tatva\Tag\Block\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class ListProduct extends \Magento\Framework\View\Element\Template
{
    protected $_collection;

    /**
     * Unique Html Id
     *
     * @var string
     */
    protected $_uniqueHtmlId = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Tatva\Tag\Model\TagFactory
     */
    protected $tagTagFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $frameworkHelperDataHelper;
    
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Framework\Module\Manager $moduleManager,
         \Magento\Framework\Registry $registry,
         \Tatva\Tag\Model\ResourceModel\Tag\Collection $tagResourceCollection,
        \Magento\Framework\App\Request\Http $request,
        \Tatva\Tag\Model\TagFactory $tagTagFactory
    ) {
        $this->_catalogSession = $catalogSession;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storemanager;
        $this->_moduleManager = $moduleManager;
        $this->request = $request;
        $this->tagTagFactory = $tagTagFactory;
        $this->registry = $registry;
        $this->_tagResourceCollection = $tagResourceCollection;
        parent::__construct($context);
    }
    
    public function getCount()
    {
        return count($this->getTags());
    }

    public function getTags()
    {
        return $this->_getCollection()->getItems();
    }

    public function getProductId()
    {
        if ($product = $this->registry->registry('current_product')) {
            return $product->getId();
        }
        return false;
    }



      protected function _getCollection()
    {
        if( !$this->_collection && $this->getProductId() ) {

            $model = $this->tagTagFactory->create();
            $this->_collection = $this->_tagResourceCollection
                ->addPopularity()
                ->addStatusFilter($model->getApprovedStatus())
                ->addProductFilter($this->getProductId())
                ->setFlag('relation', true)
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->setActiveFilter()
                ->load();
        }
        return $this->_collection;
    }

    public function getResultCount()
    {
        if (!$this->getData('result_count')) {
            $size = $this->_getProductCollection()->getSize();
            $this->setResultCount($size);
        }
        return $this->getData('result_count');
    }


    protected function _beforeToHtml()
    {
        if (!$this->getProductId()) {
            return false;
        }

        return parent::_beforeToHtml();
    }


    public function renderTags($pattern, $glue = ' ')
    {
        $out = array();
        $total=count($this->getTags());
        $i=1;
        $colon=",";
        foreach ($this->getTags() as $tag) {
            if($i==$total)
            {

                $colon="";
            }
          $out[] = sprintf($pattern,
                $tag->getCustomTaggedProductsUrl(), ucfirst($this->escapeHtml($tag->getName())),$colon
            );
          $i++;
        }         

        return implode($glue,$out);
    }
}