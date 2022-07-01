<?php

namespace Tatva\Customdesignservice\Block;

use Magento\Framework\View\Element\Template as Template;

class Customdesignservice extends \Magento\Framework\View\Element\Template {

    protected $customerSession;
    protected $_page;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
            \Magento\Customer\Model\Session $customerSession, 
            \Magento\Cms\Model\Page $page, array $data = array()) {
        $this->customerSession = $customerSession;
        $this->_page = $page;

        parent::__construct($context, $data);
    }

    public function getCustomerSession() {
        return $this->customerSession;
    }

    public function getPageType() {
        $this->page_type = $this->_page->getIdentifier();
        return $this->page_type;
    }

}
