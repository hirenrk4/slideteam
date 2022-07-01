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

class Delete extends \Magento\Framework\App\Action\Action
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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fb_del.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Fb Start");
        
        $signed_request = $this->getRequest()->getPost('signed_request');
        
        $logger->info($signed_request);
        
        $data = $this->parsesignedrequest($signed_request);
        $user_id = $data['user_id'];

        $encryption = "51".$user_id."64";
        
        
        $logger->info("user id :: ".$user_id);
        $logger->info("encryption :: ".$encryption);
        
        $encode = base64_encode($encryption);

        $status_url = 'https://www.slideteam.net/sociallogin/social/deletecustomer?id='.$encode;
        //$confirmation_code = 'abc123';

        if(!empty($user_id)) :
            $confirmation_code = $this->_social->deleteSocialData($user_id,"facebook");
        endif;

        $data = array(
          'url' => $status_url,
          'confirmation_code' => $confirmation_code
        );

        echo json_encode($data);
    }

    public function parsesignedrequest($signed_request) {

        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        $secret = $this->_scopeConfig->getValue('sociallogin/facebook/app_secret');

        // decode the data
        $sig = $this->base64urldecode($encoded_sig);
        $data = json_decode($this->base64urldecode($payload), true);
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fb_del.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Response");
        $logger->info($data);

        // confirm the signature
        $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
        if ($sig !== $expected_sig) {
        error_log('Bad Signed JSON signature!');
        return null;
        }        

        return $data;
    }

    function base64urldecode($input) {

        return base64_decode(strtr($input, '-_', '+/'));
    }

}
