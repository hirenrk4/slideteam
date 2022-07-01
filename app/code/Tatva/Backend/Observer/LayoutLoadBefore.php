<?php
namespace Tatva\Backend\Observer;

class LayoutLoadBefore implements \Magento\Framework\Event\ObserverInterface
{
    protected $_authSession;

    public function __construct
    (
        \Magento\Backend\Model\Auth\Session $authSession
    ) 
    {
        $this->_authSession = $authSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->_authSession->isLoggedIn() && $this->_authSession->getUser()->getRole()->getRoleName() != 'Administrators')
        {
            $layout = $observer->getLayout();   
            $layoutHandler = $layout->getUpdate()->getHandles();

            if(in_array('catalog_product_index', $layoutHandler))
            {
                $layout->getUpdate()->removeHandle('catalog_product_index');
                $layout->getUpdate()->addHandle('catalog_product_index_custom'); 
            }
        }

        return $this;
    }
}