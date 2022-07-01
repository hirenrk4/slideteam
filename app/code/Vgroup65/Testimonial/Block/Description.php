<?php
namespace Vgroup65\Testimonial\Block;
class Description extends \Magento\Framework\View\Element\Template
{
    protected $_testimonial;
    protected $helper;
    protected $_configuration;
    
    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Vgroup65\Testimonial\Model\TestimonialFactory $testimonialFactory, \Vgroup65\Testimonial\Helper\Data $helper, \Vgroup65\Testimonial\Model\ConfigurationFactory $configurationFactory
                              ) {
        parent::__construct($context);
        $this->_testimonial = $testimonialFactory;
        $this->helper = $helper;
        $this->_configuration = $configurationFactory;
    }
    
    protected function _prepareLayout() { 
        $configuration = $this->getConfiguration();
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', array('label'=>'Home', 'title'=>'Home', 'link'=> $this->getUrl()));
        $breadcrumbs->addCrumb($configuration['top_menu_link'], array('label'=>$configuration['top_menu_link'], 'title'=> $configuration['top_menu_link'], 'link'=>$this->getUrl('testimonial/testimonial/index/')));
        
        //get Testimonial
        $getTestimonialDetails = $this->getTestimonialDetails();
        foreach($getTestimonialDetails as $testimonialDetails):
            $testimonialName = $testimonialDetails['first_name'] . " " . $testimonialDetails['last_name']; 
            $testimonialId = $testimonialDetails['testimonial_id'];
        endforeach;
        
        if(!empty($testimonialId)):
        $breadcrumbs->addCrumb($testimonialName, array('label'=>$testimonialName, 'title'=> $configuration['top_menu_link'], 'link'=>$this->getUrl('testimonial/description/index/', array('testimonial'=> $testimonialId))));
        endif;
        $this->getLayout()->getBlock('breadcrumbs')->toHtml();
        $this->pageConfig->getTitle()->set(__($configuration['top_menu_link']));

        parent::_prepareLayout();
    }

    public function getTestimonialDetails() {
        //get values of current limit
        $getParam = $this->getRequest()->getParam('testimonial');
        $testimonialId = $this->getRequest()->getParam('testimonial');
        $testimonialList = $this->_testimonial->create();
        $testimonialCollection = $testimonialList->getCollection();
        $testimonialCollection->addFieldToFilter('testimonial_id' , $getParam);
        return $testimonialCollection;
    }
    
    public function getHelper(){
        return $this->helper;
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
