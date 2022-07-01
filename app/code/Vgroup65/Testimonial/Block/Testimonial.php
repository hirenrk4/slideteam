<?php
namespace Vgroup65\Testimonial\Block;

class Testimonial extends \Magento\Framework\View\Element\Template {

    protected $_pageSize = 10;
    protected $_testimonial;
    protected $_configuration;
    protected $helper;
    
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Vgroup65\Testimonial\Model\TestimonialFactory $testimonialFactory, \Vgroup65\Testimonial\Model\ConfigurationFactory $configurationFactory, \Vgroup65\Testimonial\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->_testimonial = $testimonialFactory;
        $this->_configuration = $configurationFactory;
        $this->helper = $helper;
    }
    
    public function getHelper(){
        return $this->helper;
    }

    protected function _prepareLayout() {
        $configuration = $this->getConfiguration();
        
        /** breadcrumbs */
        // $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        // $breadcrumbs->addCrumb('home', array('label'=>'Home', 'title'=>'Home', 'link'=> $this->getUrl()));
        // $breadcrumbs->addCrumb($configuration['top_menu_link'], array('label'=>$configuration['top_menu_link'], 'title'=> $configuration['top_menu_link'], 'link'=>$this->getUrl('testimonial/testimonial/index/')));
        // $this->getLayout()->getBlock('breadcrumbs')->toHtml();
        // 
        $this->pageConfig->getTitle()->set(__($configuration['top_menu_link']));
        
        parent::_prepareLayout();

        //get news count
        $testimonialListCount = $this->getTestimonialListCount()->count();
        
        $defaultLimit = array(6 => 6);

        if($testimonialListCount > 6 && $testimonialListCount <= 10){
            $defaultLimit = array(6 => 6 , 10 => 10);            
        }
        if($testimonialListCount > 10 && $testimonialListCount <= 15){
            $defaultLimit = array(6 => 6 , 10 => 10 , 15 => 15);
        }
        if($testimonialListCount > 15 && $testimonialListCount <= 25){
            $defaultLimit = array(6 => 6 , 10 => 10 , 15 => 15 , 20 => 20);
        }
        if($testimonialListCount > 25 && $testimonialListCount <= 50){
            $defaultLimit = array(6 => 6 , 10 => 10 , 15 => 15 , 20 => 20 , 50 => 50);
        }
        if($testimonialListCount > 50 && $testimonialListCount <= 100){
            $defaultLimit = array(6 => 6 , 10 => 10 , 15 => 15 , 20 => 20 , 50 => 50 , 100 => 100);
        }
        
        if ($this->getTestimonialList()) 
        {
            //$pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'fme.testimonial.pager')->setAvailableLimit(array(100 => 100))->setShowPerPage(true)->setCollection($this->getTestimonialList());

             $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'fme.testimonial.pager')->setAvailableLimit($defaultLimit)->setShowPerPage(true)->setCollection($this->getTestimonialList());
            
            $this->setChild('testimonial', $pager);
            $this->getTestimonialList()->load();
        }
        return $this;
    }

    public function getPagerHtml() 
    {
        return $this->getChildHtml('testimonial');
    }

    public function getTestimonialList() {
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 6;
        $testimonialList = $this->_testimonial->create();
        $testimonialCollection = $testimonialList->getCollection();
        $testimonialCollection->addFieldToFilter('status' , '1');
        $testimonialCollection->setOrder('testimonial_id', 'ASC');
        $testimonialCollection->setPageSize($pageSize);
        $testimonialCollection->setCurPage($page);
        return $testimonialCollection;
    }
    
    public function getTestimonialListCount() {
        $testimonialList = $this->_testimonial->create();
        $testimonialCollection = $testimonialList->getCollection();
        $testimonialCollection->addFieldToFilter('status' , '1');
        $testimonialCollection->setOrder('testimonial_id', 'DESC');
        return $testimonialCollection;
    }
    
    public function getConfiguration(){
        $configurationModule = $this->_configuration->create();
        $configurationCollection = $configurationModule->getCollection();
        $configurationCollection->addFieldToFilter('configuration_id' , 1);
        foreach($configurationCollection as $configuration):
            return $configuration;
        endforeach;
    }

}
