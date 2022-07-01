<?php

namespace Tatva\Translate\Plugin;

use Magento\Framework\View\Result\PageFactory;

class Product
{

    protected $_registry;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Tatva\Translate\Model\Translatedata $traslatedata,
        \Magento\Catalog\Model\ProductFactory $productfactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_registry = $registry;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->traslatedata = $traslatedata;
        $this->_productloader = $productfactory;
    }

    public function afterexecute(\Magento\Catalog\Controller\Product\View $product)
    {
        //$this->_view->loadLayout();

        $resultPage = $this->resultPageFactory->create();

        $lang  = $product->getRequest()->getParam('lang');

        if ($this->_registry->registry('current_product')) {
            $product_id = $this->_registry->registry('current_product')->getId();
            $product = $this->_productloader->create()->load($product_id);
            $languagestatus = $product->getData('languagestatus');

            if ($languagestatus == 1) {
                $languages = array("spanish", "german", "french", "portuguese");

                if (in_array(strtolower($lang), $languages)) {
                    $translatedata = $this->traslatedata->getTraslatedata($product_id, $lang);
                    $arrayvalue = array_column($translatedata, 'value', 'attribute_id');

                    $meta_title = $arrayvalue[74];
                    $meta_description = $arrayvalue[76];

                    $resultPage->getConfig()->setMetaData('title', $meta_title);
                    $resultPage->getConfig()->getTitle()->set($meta_title);
                    $resultPage->getConfig()->setDescription(__($meta_description));
                }
            }
        }
        //$this->_view->renderLayout();
        return $resultPage;
    }
}
