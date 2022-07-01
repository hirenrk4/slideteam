<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PayPalRecurringPayment\Model;

use Exception;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;

/**
 * PayPal Recurring Instant Payment Notification processor model
 */
class Ipn extends \Magento\Paypal\Model\AbstractIpn implements \Magento\Paypal\Model\IpnInterface
{
    // This cart ipn is handled by core paypal ipn Magento\Paypal\Model\Ipn 
    const CART = 'cart';

    const RECURRING_PAYMENT = 'recurring_payment';

    const RECURRING_PAYMENT_PROFILE_CREATED = 'recurring_payment_profile_created';

    const RECURRING_PAYMENT_PROFILE_CANCEL = 'recurring_payment_profile_cancel';

    const RECURRING_PAYMENT_EXPIRED = 'recurring_payment_expired';

    const RECURRING_PAYMENT_SUSPENDED = 'recurring_payment_suspended';
    
    const PAYPAL_IVOICE_PAYMENT = 'invoice_payment';
    
    const RECURRING_PAYMENT_SKIPPED = 'recurring_payment_skipped';
    
    const SUBSCR_FAILED = 'subscr_failed';

    const NEW_CASE = 'new_case';

    const ADJUSTMENT = 'adjustment';
    
    const RECURRING_PAYMENT_SUSPENDED_DUE_TO_MAX_FAILED_PAYMENT = 'recurring_payment_suspended_due_to_max_failed_payment';

    /**
     * Recurring payment instance
     *
     * @var \Magento\RecurringPayment\Model\Payment
     */
    protected $_recurringPayment;
    
    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var \Magento\RecurringPayment\Model\PaymentFactory
     */
    protected $_recurringPaymentFactory;

    protected $_ppRecurringMapperFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;
    protected $_paypalrecMapperFactory;

    /**
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory
     * @param \Magento\RecurringPayment\Model\PaymentFactory $recurringPaymentFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Paypal\Model\ConfigFactory $configFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        OrderSender $orderSender,
        \Magento\RecurringPayment\Model\PaymentFactory $recurringPaymentFactory,
        \Tatva\Paypalrec\Model\PaypalRecurringMapperFactory $ppRecurringMapperFactory,
        \Tatva\Paypalrec\Model\ResourceModel\PaypalRecurringMapper\CollectionFactory $_paypalrecMapperFactory,
        array $data = array()
    ) {
        parent::__construct($configFactory, $logger, $curlFactory, $data);
        $this->_orderFactory = $orderFactory;
        $this->_recurringPaymentFactory = $recurringPaymentFactory;
        $this->_ppRecurringMapperFactory = $ppRecurringMapperFactory;
        $this->orderSender = $orderSender;
        $this->_paypalrecMapperFactory = $_paypalrecMapperFactory;
    }

    /**
     * Get ipn data, send verification to PayPal, run corresponding handler
     *
     * @return void
     * @throws Exception
     */
    public function processIpnRequest()
    {
        $this->_addDebugData('ipn', $this->getRequestData());
        
        $writer = new \Zend\Log\Writer\Stream(BP . "/var/log/payment_log/payal_ipn_".date("d-m-Y").".log");
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($this->getRequestData());

        try {
            $txn_type = $this->getRequestData('txn_type');
            if($txn_type == "invoice_payment" || $txn_type == "recurring_payment_skipped" || $txn_type == "subscr_failed" || $txn_type == "recurring_payment_suspended_due_to_max_failed_payment" || $txn_type == "new_case" || $txn_type == "adjustment")
            {
            	
                switch ($txn_type) {
                    // handle recurring_payment ipn
                    case self::PAYPAL_IVOICE_PAYMENT:
                        
                        break;
                    case self::RECURRING_PAYMENT_SKIPPED:
                        
                        break;
                    case self::SUBSCR_FAILED:
                        
                        break;
                    case self::NEW_CASE:
                        
                        break;
                    case self::ADJUSTMENT:
                        
                        break;
                    default:
                        
                        break;                  
                }
                exit;
            }
            else
            {
                $this->_getConfig();
                $this->_postBack();
                $this->_processRecurringPayment();
            }
        } catch (Exception $e) {
            $this->_addDebugData('exception', $e->getMessage());
            $this->_debug();
            
            throw $e;
        }
        $this->_debug();
    }

    /**
     * Get config with the method code and store id
     *
     * @return \Magento\Paypal\Model\Config
     * @throws Exception
     */
    protected function _getConfig()
    {
        $txn_type = $this->getRequestData('txn_type');
        $m1PaymentIpns = ["subscr_payment","subscr_signup","subscr_cancel","subscr_eot"];
        $isM1Payment = in_array($txn_type, $m1PaymentIpns);

        if($isM1Payment == false){   
            $recurringPayment = $this->_getRecurringPayment();
            
            if(is_object($recurringPayment))
            {
                $methodCode = $recurringPayment->getMethodCode();
                $parameters = array('params' => array($methodCode, $recurringPayment->getStoreId()));   
            }
            elseif(is_array($recurringPayment))
            {
                $methodCode = $recurringPayment[0]['method_code'];
                $parameters = array('params' => array($methodCode, $recurringPayment[0]['store_id']));  
            }
            
            $this->_config = $this->_configFactory->create($parameters);

            if (!$this->_config->isMethodActive($methodCode) || !$this->_config->isMethodAvailable() ) {
                throw new Exception(sprintf('Method "%s" is not available.', $methodCode));
            }
        }
        else{
            $order = $this->_getOrder();
            // $methodCode = $order->getPayment()->getMethod();
            // This is the patch as paypal_standard is not available
            $methodCode = "paypal_express";
            $parameters = array('params' => array($methodCode, $order->getStoreId()));
            $this->_config = $this->_configFactory->create($parameters);
        }

        return $this->_config;
    }

    /**
     * Load order
     *
     * @return \Magento\Sales\Model\Order
     * @throws Exception
     */
    protected function _getOrder()
    {
        $incrementId = $this->getRequestData('invoice');
        $this->_order = $this->_orderFactory->create()->loadByIncrementId($incrementId);
        if (!$this->_order->getId()) {
        	
            //throw new Exception(sprintf('Wrong order ID: "%s".', $incrementId));
             
            exit;
        }
        return $this->_order;
    }

    /**
     * Load recurring payment
     *
     * @return \Magento\RecurringPayment\Model\Payment
     * @throws Exception
     */
    protected function _getRecurringPayment()
    {
        $referenceId = $this->getRequestData('rp_invoice_id');
        $rec_profile_id = $this->getRequestData('recurring_payment_id');
        
        if(empty($referenceId))
        {
            $rec_profile_id = $this->getRequestData('recurring_payment_id');
            $paypalMappingCollection = $this->_paypalrecMapperFactory->create()->addFieldToSelect(array('rp_profile_id','customer_id'))->addFieldToFilter('rp_profile_id',$rec_profile_id);
            $paypalMappingCollection->setOrder('map_id','DESC');

            $customerId = NULL;
            
            foreach($paypalMappingCollection as $mapper)
            {
                $customerId = $mapper['customer_id'];
                break;
            }
            
            if(empty($customerId))
            {
                throw new Exception(
                    sprintf('Wrong recurring payment INTERNAL_REFERENCE_ID: "%s".', $referenceId)
                );   
            }
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('recurring_payment'); //gives table name with prefix

            //Select Data from table
            $sql = "Select * FROM " . $tableName." where customer_id=".$customerId." Order By payment_id desc";
            $result = $connection->fetchAll($sql);
            return $result;
        }
        else
        {
            $recurring_factory = $this->_recurringPaymentFactory->create();
            $this->_recurringPayment = $recurring_factory->loadByInternalReferenceId($referenceId);
            if (!$this->_recurringPayment->getId()) {
                throw new Exception(
                    sprintf('Wrong recurring payment INTERNAL_REFERENCE_ID: "%s".', $referenceId)
                );
            }
        }
        return $this->_recurringPayment;
    }

    /**
     * Process notification from recurring payments
     * @todo unable to use $this->orderSender->send($order); to send email to customer for processing recurring payment.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws Exception
     */
    protected function _processRecurringPayment()
    {
        $this->_getConfig();
    
        try {

            // Handle txn_type
            $transactionType = $this->getRequestData('txn_type');
            switch ($transactionType) {
                    // handle recurring_payment ipn
                case self::RECURRING_PAYMENT:
                    $this->_registerRecurringPayment();
                    break;
                    // handle recurring_payment_profile_created ipn
                case self::RECURRING_PAYMENT_PROFILE_CREATED:
                    $this->_registerRecurringProfileCreated();
                    break;
                    //handle transaction types for unsubscribe , cancel or suspend recurring profile
                case self::RECURRING_PAYMENT_PROFILE_CANCEL:
                case self::RECURRING_PAYMENT_EXPIRED: 
                case self::RECURRING_PAYMENT_SUSPENDED:
                    $this->_registerRecurringProfileUnsubscribe();
                    break;
                    //handle transaction types which are not described above
                default:
                    $this->_registerTransaction($transactionType);
                    break;
            }
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $comment = $this->_createIpnComment(__('Note: %1', $e->getMessage()), true);
            
            //TODO: add to payment comments
            //$comment->save();
            throw $e;
        }
    
    }


    protected function _registerRecurringPayment()
    {
        // handle payment_status
        $paymentStatus = $this->_filterPaymentStatus($this->getRequestData('payment_status'));
        
        if ($paymentStatus != \Magento\Paypal\Model\Info::PAYMENTSTATUS_COMPLETED) {

            exit;
            //throw new Exception("Cannot handle payment status '{$paymentStatus}'.");
        }
        // Register recurring payment notification, create and process order
        $price = $this->getRequestData('mc_gross') - $this->getRequestData('tax') - $this->getRequestData('shipping');
        
        $productItemInfo = new \Magento\Framework\DataObject;
        $type = trim($this->getRequestData('period_type'));
        if ($type == 'Trial') {
            $productItemInfo->setPaymentType(\Magento\RecurringPayment\Model\PaymentTypeInterface::TRIAL);
        } elseif ($type == 'Regular') {
            $productItemInfo->setPaymentType(\Magento\RecurringPayment\Model\PaymentTypeInterface::REGULAR);
        }
        $productItemInfo->setTaxAmount($this->getRequestData('tax'));
        $productItemInfo->setShippingAmount($this->getRequestData('shipping'));
        $productItemInfo->setPrice($price);

        $initialOrderId = $this->getOrderIdOfInitialOrder();
        
        if(!empty($initialOrderId)){
            $this->_recurringPayment->addOrderRelation($initialOrderId);
        }
        else{
            throw new Exception(sprintf('Initial Order Id of paypal recurring profile "%s" is not found.', $this->getRequestData('recurring_payment_id')));
        }
    }

    protected function _registerRecurringProfileCreated()
    {
        // Nothing to do for now on this IPN
    }

    protected function _registerRecurringProfileUnsubscribe()
    {
        // Logic for the unsubscribe is implemented using plugin
    }

    protected function _registerTransaction($transactionType)
    {
        $m1Ipns = ["subscr_payment","subscr_signup","subscr_cancel","subscr_eot"];
        $isM1Ipn = in_array($transactionType, $m1Ipns);
        if($isM1Ipn == false){
            throw new Exception(sprintf('Cannot handle Transaction Type : "%s" .', $this->getRequestData('txn_type')));
        }
    }


    /**
     * Generate an "IPN" comment with additional explanation.
     * Returns the generated comment or order status history object
     *
     * @param string $comment
     * @param bool $addToHistory
     * @return string|\Magento\Sales\Model\Order\Status\History
     */
    protected function _createIpnComment($comment = '', $addToHistory = false)
    {
        $message = __('IPN "%1"', $this->getRequestData('payment_status'));
        if ($comment) {
            $message .= ' ' . $comment;
        }
        if ($addToHistory) {
            //TODO: add to payment comments
        }
        return $message;
    }

    protected function getOrderIdOfInitialOrder()
    {
        $initialOrderId = null;
        $recurring_mapper_obj = null;
        if(!empty($this->getRequestData('recurring_payment_id'))){
            $ppRecurringMapperObj =  $this->_ppRecurringMapperFactory->create();
            $ppRecurringMapperCollection = $ppRecurringMapperObj->getCollection()
                ->addFieldToFilter('rp_profile_id',$this->getRequestData('recurring_payment_id'));
            $ppRecurringMapperCollection->getSelect()->order('map_id desc')->limit(1);
            
            if($ppRecurringMapperCollection->getSize()){
                foreach ($ppRecurringMapperCollection as $item) {
                    $recurring_mapper_obj = $item;
                }
                if(is_object($recurring_mapper_obj)){
                    $increment_id = $recurring_mapper_obj->getInvoice();
                    $initialOrder = $this->_orderFactory->create()->loadByIncrementId($increment_id);
                    $initialOrder->setStatus('payment_completed')->save();
                    $initialOrderId = $initialOrder->getId();
                }
            }
        }

        return $initialOrderId;
    }
}
