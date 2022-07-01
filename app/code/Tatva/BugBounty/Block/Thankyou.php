<?php

namespace Tatva\BugBounty\Block;

class Thankyou extends \Magento\Framework\View\Element\Template
{

    protected $_customerSession;
    protected $_filterProvider;
    protected $_redirect;
    protected $_response;
    protected $_cookieManager;

    public function __construct
    (
        \Magento\Backend\Block\Template\Context $context, 
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Customer\Model\Session $customerSession, 
        \Magento\Framework\App\Response\RedirectInterface $redirectInterface, 
        \Magento\Cms\Model\Template\FilterProvider $filterProvider, 
        array $data = []
    )
    {
        $this->_redirect = $redirectInterface;
        $this->_response = $response;
        $this->_cookieManager = $cookieManager;
        $this->_customerSession = $customerSession;
        $this->_filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getStaticBlock()
    {   
        if(!empty($this->_cookieManager->getCookie('thankyouEmail')))
        {
            $block = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId('bug-bounty-thank-you')->toHtml(); 
            $array = array();
            $array['user_email_id'] = $this->_cookieManager->getCookie('thankyouEmail');
            $array['previous_page'] = $this->_cookieManager->getCookie('previousPage');
            $filter = $this->_filterProvider->getBlockFilter();
            $filter->setVariables($array);
            $this->_cookieManager->deleteCookie('thankyouEmail');
            $this->_cookieManager->deleteCookie('previousPage');
            return $filter->filter($block);
        }
        else
        {
            $this->_redirect->redirect($this->_response,'bug-bounty');
        }
    }
}
