<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Free\Controller\Ajax;

class cancelLogin extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sessionManager = $sessionManager;
    }

    
    public function execute()
    {
        $this->sessionManager->unsLogin();
        $this->sessionManager->unsSocialLogin();
        $response = [
            'errors' => true,
            'message' => __('Customer cancel Login')
        ];
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}