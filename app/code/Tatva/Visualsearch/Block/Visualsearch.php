<?php

namespace Tatva\Visualsearch\Block;

use Magento\Framework\App\Filesystem\DirectoryList;

class Visualsearch extends \Magento\Framework\View\Element\Template
{
    protected $_page;
    protected $_catalogLayer;
    protected $_filesystem;
    protected $_imageFactory;
    protected $_storeManager;
    protected $_catalogResourceModelCategoryCollectionFactory;

    public function __construct
    (
        \Magento\Cms\Model\Page $page,
        \Magento\Framework\Image\Factory $imageFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $catalogResourceModelCategoryCollectionFactory,
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
    ) 
    {
        $this->_page = $page;
        $this->_storeManager = $storeManager;
        $this->_imageFactory = $imageFactory;
        $this->_filesystem = $filesystem;
        $this->_catalogLayer = $layerResolver->get();
        $this->_catalogResourceModelCategoryCollectionFactory = $catalogResourceModelCategoryCollectionFactory;
        $this->setCacheLifetime(3600);
        parent::__construct($context,$data);
    }

    public function getPageIdentifier()
    {
        return $this->_page->getIdentifier();
    }

    public function getCategoryLayer()
    {
        return $this->_catalogLayer;
    }

    public function getImageFactory()
    {
        return $this->_imageFactory;
    }

    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    public function getMediaPath()
    {
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    public function getMainCategoryCollection()
    {

        $categories = $this->_catalogResourceModelCategoryCollectionFactory->create()
        ->addAttributeToSelect(array("name","image","url_path","is_active"))
        ->addAttributeToFilter('level',2)
        ->addAttributeToFilter("is_active","1")
        ->addAttributeToFilter("include_in_menu","1")
        ->addAttributeToFilter('visual_search', 1)
        ->addAttributeToSort('position', 'asc');
        return $categories;
    }

    public function getMainChildCategoryCollection($ids)
    {
        $child_categories = $this->_catalogResourceModelCategoryCollectionFactory->create()
        ->addAttributeToSelect(array("name","image","url_path","is_active"))
        ->addAttributeToFilter('level',3)
        ->addAttributeToFilter('visual_search', 1)
        ->addIdFilter($ids);
        return $child_categories;
    }

    public function getCategoryCollection($sub_ids)
    {
        $child_sub_categories = $this->_catalogResourceModelCategoryCollectionFactory->create()
        ->addAttributeToSelect(array("name","image","url_path","is_active"))
        ->addAttributeToFilter('level',4)
        ->addAttributeToFilter('visual_search', 1)
        ->addIdFilter($sub_ids);
        return $child_sub_categories;  
    }
}