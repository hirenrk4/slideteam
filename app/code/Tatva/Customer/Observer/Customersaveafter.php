<?php
/**
* Copyright � 2016 Magento. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Tatva\Customer\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class Customersaveafter implements ObserverInterface
{
     /**
     * $EmarsysHelper
     *
     * @type \Tatva\Emarsys\Helper\Data
     */
    protected $EmarsysHelper;
    /**
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
      \Magento\Framework\Message\ManagerInterface $messageManager,
      \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
      \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_httpClientFactory   = $httpClientFactory;
        $this->_messageManager = $messageManager;
        $this->EmarsysHelper = $EmarsysHelper;
        $this->emarsysApiHelper = $emarsysApiHelper;
    }

    /**
    * @param \Magento\Framework\Event\Observer $observer
    * @return void
    */
    public function execute(EventObserver $observer)
    {
        
        $customer = $observer->getEvent()->getData('customer');
          //Emarsys integration start//
               $field4 =  $this->EmarsysHelper->isApiEnabled();
                if($field4)
                {
                    
                    $contact = array(
                                "1"=>$customer->getFirstname(),
                                "2"=>$customer->getLastname(),
                                "3"=>$customer->getEmail(),
                                "485"=>$customer->getId()
                              );                  
                    $encode = json_encode($contact); 

                    try {
                      
                        $apiHelper = $this->emarsysApiHelper;

                        $result = $apiHelper->send('POST', 'contact/getdata', '{"keyId": "485","keyValues":["'.$customer->getId().'"],"fields": ["3"]}');

                        if(count($result->data->errors))
                        {
                            $apiHelper->send('POST', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                        }
                        else
                        {
                            $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                        }                       
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
                //Emarsys integration end//
    }
}