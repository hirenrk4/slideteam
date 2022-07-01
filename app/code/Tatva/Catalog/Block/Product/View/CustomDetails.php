<?php
namespace Tatva\Catalog\Block\Product\View;

use Magento\Framework\View\Element\Template;

class CustomDetails extends Template
{

    protected $_registry;
    /**
     * @var \Magento\Catalog\Helper\Output
     */
    public $catalogOutputHelper;
    /**
     * @var \Tatva\Translate\Model\Translatedata
     */
    public $traslatedata;

    protected $customerSession;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Helper\Output $catalogOutputHelper,
        \Tatva\Translate\Model\Translatedata $traslatedata,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry
    )
    {
        $this->_registry = $registry;
        $this->catalogOutputHelper = $catalogOutputHelper;
        $this->traslatedata = $traslatedata;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_registry->registry('product'));
        }
        return $this->getData('product');
    }

    public function customProductAttribute($product, $attributeHtml, $attributeName)
    {
        return $this->catalogOutputHelper->productAttribute($product, $attributeHtml, $attributeName);
    }

    public function getCurrentlangdata()
    {
        $lang = $this->getRequest()->getParam('lang');
        $product_id = $this->getProduct()->getId();
        $translatedata = $this->traslatedata->getTraslatedata($product_id, $lang);
        $arrayvalue = array_column($translatedata, 'value', 'attribute_id');

        return $arrayvalue;
    }

    public function getCustomerSession()
    {
        return $this->customerSession;
    }

}