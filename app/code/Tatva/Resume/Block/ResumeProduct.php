<?php 
namespace Tatva\Resume\Block;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class ResumeProduct extends \Magento\Framework\View\Element\Template
{

    protected $registry;

	public function __construct(
        Context $context,        
        array $data = [],
        Registry $registry
    )
    {    
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $product = $this->registry->registry('current_product');
        $this->pageConfig->getTitle()->set(__($product->getName()));
        return $this;
    }

    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

}
