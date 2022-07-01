<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Checkout\Controller\Onepage;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class Success extends \Magento\Checkout\Controller\Onepage
{
   protected $_dateFactory;
   protected $_resourceConnection;
   protected $customerFactory;
   protected $_httpClientFactory;

   public function __construct(
      \Magento\Framework\App\Action\Context $context,
      \Magento\Customer\Model\Session $customerSession,
      CustomerRepositoryInterface $customerRepository,
      AccountManagementInterface $accountManagement,
      \Magento\Framework\Registry $coreRegistry,
      \Magento\Framework\Translate\InlineInterface $translateInline,
      \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Framework\View\LayoutFactory $layoutFactory,
      \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
      \Magento\Framework\View\Result\PageFactory $resultPageFactory,
      \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
      \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
      \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
      \Magento\Framework\App\ResourceConnection $resourceConnection,
      \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
      \Magento\Customer\Model\CustomerFactory $customerFactory,
      \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
      ) {
   $this->_resourceConnection = $resourceConnection;
   $this->_dateFactory = $dateTimeDateTimeFactory;
   $this->customerFactory = $customerFactory->create();
   $this->_httpClientFactory   = $httpClientFactory;
   parent::__construct(
      $context,$customerSession,$customerRepository,$accountManagement,$coreRegistry,$translateInline,$formKeyValidator,$scopeConfig,$layoutFactory,
      $quoteRepository,$resultPageFactory,$resultLayoutFactory,$resultRawFactory,$resultJsonFactory
  );
  }
    /**
     * Order success action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$this->_objectManager->get(\Magento\Checkout\Model\Session\SuccessValidator::class)->isValid()) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $lastorderId = $session->getLastOrderId();
        //$lid = $this->getOrderId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($lastorderId);
        
        $payment = $order->getPayment();
        $method_code = $payment->getMethodInstance()->getCode();

        if($method_code != "amasty_stripe")
        {
          $order->setStatus('processing')->save();
        }

        $this->postToGA($lastorderId,$order->getCustomerId(),$this->_dateFactory->create()->gmtDate('Y-m-d'),$order);
        $session->clearQuote();
        //@todo: Refactor it to match CQRS
        $resultPage = $this->resultPageFactory->create();
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            [
                'order_ids' => [$session->getLastOrderId()],
                'order' => $session->getLastRealOrder()
            ]
        );
        return $resultPage;
    }
    protected function postToGA($increment_id,$customer_id,$sub_from_date,$order){

      //Google Analytics snippet for Recurring Transactions Start
      $gtm_status=$this->scopeConfig->getValue('button/gtm_config/field1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      $skuSimpleProduct ="";
      $productname = "";
      $isEbook = "" ;
      foreach($order->getAllItems() as $item){
         $skuSimpleProduct = $item->getProduct()->getSku();
         $productname=$item->getProduct()->getName();
         $isEbook =$item->getProduct()->getIsEbook();
      }
      if($isEbook):
        if($gtm_status):
            $ga_id = $this->scopeConfig->getValue('button/gtm_config/ga_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $connection = $this->_resourceConnection->getConnection();
            $documentHost = "www.slideteam.net";
            
            
            if(isset($this->_postData['invoice_list_amount']))
            {
                $grandTotal = $this->_postData['invoice_list_amount'];    
            }
            elseif (isset($this->_postData['amount'])) {
                $grandTotal = $this->_postData['amount'];
            }
            else{
                $grandTotal = $order->getGrandTotal();
            }


            $result=$connection->query("SELECT entity_id FROM `sales_order` WHERE `increment_id` = ".$order->getIncrementId());
            $row = $result->fetch();
            $orderId = $row['entity_id'];
            //$customerData = $this->customerRepository->getById($customer_id);
            $customerData =$this->customerFactory->load($customer_id);
            $cid = $customerData->getCid();
            if(!isset($cid) || $cid=="" )
            {
            $cid = $customerData->getUuid();
                if(!isset($cid) || $cid=="" )
                {
                    $cid = sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
                    mt_rand( 0, 0xffff ),
                    mt_rand( 0, 0x0fff ) | 0x4000,
                    mt_rand( 0, 0x3fff ) | 0x8000,
                    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
                    );
                    $customerGetDataModel = $customerData->getDataModel();
                    $customerData->setUuid($cid);
                    $customerData->updateData($customerGetDataModel);
                    $customerData->save();
                }
            }
            $customerEmail = $customerData->getEmail();
            $customerEmailParts = explode("@",$customerEmail);
            $emailHost = $customerEmailParts[1];
            $customerCreatedDate = date('Y-m-d',strtotime($customerData->getCreatedAt()));
        
            $url = 'http://www.google-analytics.com/collect';

            $fields = array('v' => '1', 'tid' => $ga_id, 'cid' => $cid, 'uid' => $customer_id, 'uip' => $order->getRemoteIp(), 'dh' => $documentHost, 't' => 'transaction', 'ti' => $orderId, 'ta' => $emailHost, 'tr' => $grandTotal, 'ts' => '0', 'cd2' => $customer_id, 'cd5' => '','cd9' => $customerCreatedDate);

                

                $client = $this->_httpClientFactory->create();
                $client->setUri($url);
                $client->setHeaders(
                    [
                        'Accept: application/json'
                    ]
                );
                    $client->setParameterPost($fields);
                $client->setMethod(\Zend_Http_Client::POST);
                

                try {
                    $responseBody = $client->request()->getBody();
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            
                $fields_second = array('v' => '1', 'tid' => $ga_id, 'cid' => $cid, 'uid' => $customer_id, 'uip' => $order->getRemoteIp(), 'dh' => $documentHost, 't' => 'item', 'ti' => $orderId, 'in' => $productname, 'ic' => $skuSimpleProduct, 'iv' => 'Ebook', 'iq' => '1', 'ip' => $grandTotal, 'cd2' => $customer_id, 'cd5' => '','cd9' => $customerCreatedDate);

                $client = $this->_httpClientFactory->create();
                $client->setUri($url);
                $client->setHeaders(
                    [
                        'Accept: application/json'
                    ]
                );
                    $client->setParameterPost($fields_second);
                $client->setMethod(\Zend_Http_Client::POST);
                

                try {
                    $responseBody = $client->request()->getBody();
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }

            //Google Analytics snippet for Recurring Transactions End
          endif;
        endif;
    }
}
