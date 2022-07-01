<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Vgroup65\Testimonial\Model\TestimonialFactory;
use Vgroup65\Testimonial\Helper\Data;
use Magento\Framework\Image\AdapterFactory;
abstract class Testimonial extends \Magento\Backend\App\Action
{
    protected $_coreRegistry;

    protected $_resultPageFactory;

    protected $_testimonialFactory;
    
    protected $helper;
    
    protected $_imageFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        TestimonialFactory $testimonialFactory,
        Data $helper,
        AdapterFactory $imageFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_testimonialFactory = $testimonialFactory;
        $this->helper = $helper;
        $this->_imageFactory = $imageFactory;
    }
 
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vgroup65_testimonial::manage_testimonial');
    }
}