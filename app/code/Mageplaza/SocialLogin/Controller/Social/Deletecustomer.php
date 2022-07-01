<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SocialLogin
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SocialLogin\Controller\Social;

class Deletecustomer extends \Magento\Framework\App\Action\Action
{

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Mageplaza\SocialLogin\Model\Social $social
    ){
        $this->_scopeConfig = $scopeConfig;
        $this->_social = $social;
        return parent::__construct($context);
    }

    public function execute()
    {
        $encryption = $this->getRequest()->getParam('id');

        $decode = base64_decode($encryption);

        $str1 = substr($decode, 2);
        $UserId = substr($str1, 0, -2);

        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fb_del.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($UserId);

        $status = $this->_social->CheckSocialFacebookCustomer($UserId,"facebook");

        if($status == "403")
        {
            echo "Please wait for sometime for deleting your data from our system";
        }
        else
        {
            echo "Your all data from slideteam were deleted.";
        }
        
    }
}
