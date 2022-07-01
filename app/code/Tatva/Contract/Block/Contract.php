<?php

namespace Tatva\Contract\Block;

use Magento\Framework\View\Element\Template as Template;

class Contract extends \Magento\Framework\View\Element\Template {

    protected $customerSession;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Customer\Model\Session $customerSession, array $data = array()) {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function getCustomerSession() {
        return $this->customerSession;
    }

}
