<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Customer\Block\Account;

use Magento\Customer\Model\Url;
use Magento\Framework\View\Element\Template;


/**
 * Customer account navigation sidebar
 *
 * @api
 * @since 100.0.2
 */
class Forgotpassword extends \Magento\Customer\Block\Account\Forgotpassword
{
    protected $_customerSession;

    public function __construct(
        Template\Context $context,
        Url $customerUrl,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->customerUrl = $customerUrl;
   	$this->_customerSession = $customerSession;
        parent::__construct($context ,$customerUrl, $data);
    }

    public function isCustomerLogin()
    {
        return (!$this->_customerSession->isLoggedIn());
    }
}
