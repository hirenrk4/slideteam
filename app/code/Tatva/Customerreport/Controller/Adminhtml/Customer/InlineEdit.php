<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Customerreport\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action\Context;
use Tatva\Subscription\Model\Subscription  as SubscriptionRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Tatva\Subscription\Api\Data\SubscriptionInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action
{

    protected $subscriptionRepository;
    protected $jsonFactory;
    /**
     * $EmarsysHelper
     *
     * @type \Tatva\Emarsys\Helper\Data
     */
    protected $EmarsysHelper;
    
    public function __construct(
        Context $context,
        SubscriptionRepository $subscriptionRepository,
        JsonFactory $jsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
      \Magento\Framework\Message\ManagerInterface $messageManager,
      \Magento\Customer\Api\CustomerRepositoryInterface $customer,
      \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
      \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper
        ) {
        parent::__construct($context);
        $this->subscriptionRepository = $subscriptionRepository;
        $this->jsonFactory = $jsonFactory;
        $this->_scopeConfig = $scopeConfig;
         $this->_httpClientFactory   = $httpClientFactory;
         $this->messageManager=$messageManager;
         $this->customer=$customer;
        $this->EmarsysHelper = $EmarsysHelper;
        $this->emarsysApiHelper = $emarsysApiHelper;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        
        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $blockId) {
                    /** @var \Magento\Cms\Model\Block $block */
                  

                    try {
                        $subscriptionModel=$this->subscriptionRepository;
                        $subscriptionModel->load($blockId);        
						            $postItems[$blockId]['renew_date'] = date("Y-m-d",strtotime($postItems[$blockId]['to_date'] . "+ 1 day"));
                        $subscriptionModel->setData(array_merge($subscriptionModel->getData(), $postItems[$blockId]));  
                        $subscriptionModel->setId($blockId); 
                         $this->subscriptionRepository->save();

                           $customer_id = $subscriptionModel->get()['customer_id'];
                           $subscription_expiry_date = $postItems[$blockId]['to_date'];
                           $customer_type = 1;   
                           $subscription_duration = null;
                           $period_full = $postItems[$blockId]['subscription_period'];
                           $customerData = $this->customer->getById($customer_id);
                           
                      if(isset($postItems[$blockId]['download_limit'])){
                         //Emarsys integration start//
                        $customer_type=($postItems[$blockId]['download_limit'] == 0?2:1);
                         $field4 =  $this->EmarsysHelper->isApiEnabled();
                         if($field4)
                            {
                               //$newsletter_subscribe = $customer->getIsSubscribed();
                               $contact = array(
                                  "490"=>$customer_type,
                                  "485"=>$customer_id
                                );
                                $encode = json_encode($contact);
                                try {
                                    $apiHelper = $this->emarsysApiHelper;
                                    $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                                } catch (\Exception $e) {
                                    echo $e->getMessage();
                                }
                            //Emarsys integration end//
                          }
                      
                      }
                       //Emarsys integration start//
                if($field4)
                {

                    switch($period_full)
                    {
                      case($period_full=="Monthly") : $subscription_duration = 1;break;
                      case($period_full=="Semi-annual") : $subscription_duration = 2;break;
                      case($period_full=="Annual") : $subscription_duration = 3;break;
                      case($period_full=="Annual + Custom Design") : $subscription_duration = 4;break;
                      case((stripos($period_full,'enterprise') !== false)) : $subscription_duration = 5;break;
                      case((stripos($period_full,'Monthly +') !== false)) : $subscription_duration = 6;break;
                      case((stripos($period_full,'Semi-annual +') !== false)) : $subscription_duration = 7;break;
                      case($period_full=="Annual 4 User License") : $subscription_duration = 11;break;
                      case($period_full=="Annual 20 User License") : $subscription_duration = 12;break;
                      case($period_full=="Annual Company Wide Unlimited User License") : $subscription_duration = 13;break;
                      case($period_full=="Annual 15 User Education License") : $subscription_duration = 14;break;
                      case($period_full=="Annual UNLIMITED User Institute Wide License") : $subscription_duration = 15;break;
                    }

                    $customerData =  $this->customer->getById($customer_id);
                    
                    $to_date=date("Y-m-d",strtotime($subscription_expiry_date));

                    $contact = array(
                      "1"=>$customerData->getFirstname(),
                      "2"=>$customerData->getLastname(),
                      "3"=>$customerData->getEmail(),
                      "485"=>$customer_id,
                      "488"=>$to_date,
                      "489"=>$subscription_duration,
                      "490"=>$customer_type
                    );
                    $encode = json_encode($contact);

                    try {
                         $apiHelper = $this->emarsysApiHelper;
                         $apiHelper->send('PUT', 'contact', '{"key_id": "485","contacts":['.$encode.']}');
                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
             catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t save the customer.'));
            }
         }
        }
    }

        return $resultJson->setData([
            //'messages' => $messages,
            'error' => $error
            ]);
    }

    protected function getErrorWithBlockId(SubscriptionInterface $block, $errorText)
    {
        return '[Block ID: ' . $block->getId() . '] ' . $errorText;
    }

}
