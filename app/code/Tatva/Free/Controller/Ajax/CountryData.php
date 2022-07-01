<?php
/**
 * Copyright © 2022 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Free\Controller\Ajax;

/**
 *
 * @method \Magento\Framework\App\RequestInterface getRequest()
 * @method \Magento\Framework\App\Response\Http getResponse()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CountryData extends \Magento\Framework\App\Action\Action
{
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    
    /**
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }
    public function execute()
    {
        $blockInstance = $this->_objectManager->get('Tatva\Customer\Block\Form\Register');

        $response = $blockInstance->getCountryData();

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */

        $resultJson = $this->resultJsonFactory->create();
        
        return $resultJson->setData($response);
    }
}