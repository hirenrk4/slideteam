<?php
namespace Tatva\Portfolio\Block;

class Navigation extends \Magento\Framework\View\Element\Template
{
    protected $model;
    protected $cms_page;
    protected $storeManager; 

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, 
 \Magento\Cms\Model\PageFactory $model,
            \Magento\Cms\Model\Page $cms_page,
 \Magento\Store\Model\StoreManagerInterface $storeManager,
            array $data = array()) {
        $this->model = $model;
        $this->cms_page = $cms_page;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    
    public function contentData(){
        $collection = $this->model->create();
        $postCollection = $collection->getCollection()
                ->addFieldToFilter('identifier',array('like' => '%'.'portfolio/'.'%'))
                ->addFieldToFilter('is_active',1)
                ->addFieldToSelect('page_id');
        return $postCollection;
        
    }
    
    public function previousPage(){
        $identifier = $this->cms_page->getIdentifier();
        $model = $this->cms_page->load($identifier,'identifier');
        $currentid  = $model->getId();
        $collection = $this->contentData();
        
        $allid = array();
        foreach ($collection as $data){
            $allid[] = $data['page_id'];
        }
        ////Get the key of current page from array////
        $key = array_search($currentid,$allid);
        ////If current page is first page , it return true to the phtml file and hide the previous link//// 
        if ($key == 0){
            return $complete = "true";
        }
        ////Get the key of previous page from array////
        $newkey = $key - 1;
        ////Get the page id pf ptrvious page from key////
        $value = $allid[$newkey];
        
        $preCollection = $this->cms_page->getCollection()
                ->addFieldToFilter('page_id',$value);
        
        foreach ($preCollection as $new) {
            $getIdentifier = $new->getIdentifier();
        }
        $identifier = substr($getIdentifier,10);
        ////Set url as per Url rewrite management////
        $url = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB)."powerpoint".$identifier;
        return $url; 
        
    }
    
    public function NextPage(){
        $identifier = $this->cms_page->getIdentifier();
        $model = $this->cms_page->load($identifier,'identifier');
        $currentid  = $model->getId();
        $collection = $this->contentData();
        
        $allid = array();
        foreach ($collection as $data){
            $allid[] = $data['page_id'];
        }
        ////Get the key of current page from array////
        $key = array_search($currentid,$allid);
        
        $result = count($allid);
        $lastkey = $result-1; 
        ////If current page is first page , it return true to the phtml file and hide the previous link//// 
        if ($key == $lastkey){
            return $complete = "true";
        }
        ////Get the key of previous page from array////
        $newkey = $key + 1;
        ////Get the page id pf ptrvious page from key////
        $value = $allid[$newkey];
        
        $nextCollection = $this->cms_page->getCollection()
                ->addFieldToFilter('page_id',$value);
        
        foreach ($nextCollection as $new) {
            $getIdentifier = $new->getIdentifier();
        }
        $identifier = substr($getIdentifier,10);
        ////Set url as per Url rewrite management////
        $url = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB)."powerpoint".$identifier;
        return $url; 
    }
    
}