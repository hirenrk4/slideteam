<?php
namespace Tatva\Catalog\Block;

class Allcategories extends \Magento\Framework\View\Element\Template
{
    protected $_request;
    protected $_categoryFactory;
    protected $pageConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory  $categoryFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Page\Config $pageConfig,
        array $data = []
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_request = $request;
        $this->pageConfig = $pageConfig;
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('World’s Finest Collection of Professional PowerPoint Templates - SlideTeam'));
        $this->pageConfig->setDescription(__('SlideTeam offers an exhaustive collection of pre-designed PowerPoint templates available for immediate download. You can use these engaging slides to get your message across'));
        return $this;
    }

    public function getMainCategories()
    {
        $categoryFactory = $this->_categoryFactory->create()->getCollection()
                                                  ->addAttributeToSelect('*')
                                                  ->addAttributeToFilter('is_active', 1)
                                                  ->setStoreId(1)
                                                  ->addOrderField('name')
                                                  ->load();
        $categoryData= [];
        $categoryData['Allcategories'] = $categoryFactory;

        return $categoryData;
    }

    public function getPowerpointCategories()
    {
        $categoryFactory = $this->_categoryFactory->create()->getCollection()
                                                  ->addAttributeToSelect('*')
                                                  ->addAttributeToFilter('is_active', 1)
                                                  ->setStoreId(1)
                                                  ->addOrderField('name')
                                                  ->load();
        $categoryFactory->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $categoryFactory->getSelect()->columns(array('e.entity_id','e.name','e.url_key','e.level','e.seo_url'));
    
        $categoryData= [];
        $categoryData['Allcategories'] = $categoryFactory;

        return $categoryData;
    }

}
