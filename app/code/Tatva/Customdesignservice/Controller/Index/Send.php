<?php

namespace Tatva\Contract\Controller\Index;

use Magento\Framework\Controller\ResultFactory;

class Send extends \Magento\Framework\App\Action\Action {

    protected $_pageFactory;
    protected $customerSession;

    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $pageFactory, \Magento\Customer\Model\Session $customerSession) {
        $this->_pageFactory = $pageFactory;
        $this->customerSession = $customerSession;
        return parent::__construct($context);
    }

    public function execute() {
       
    }

}
