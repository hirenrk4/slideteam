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
namespace Magento\PayPalRecurringPayment\Model\Api;

use Magento\Payment\Model\Method\Logger;
use \Magento\Payment\Model\Info as PaymentInfo;

/**
 * Recurring payments implementation via PayPal Name-Value Pair API
 */
class Nvp extends \Magento\Paypal\Model\Api\Nvp
{
    /**
     * CreateRecurringPayment request map
     *
     * @var string[]
     */
    protected $_createRecurringPaymentRequest = array(
        'TOKEN', 'SUBSCRIBERNAME', 'PROFILESTARTDATE', 'PROFILEREFERENCE', 'DESC', 'MAXFAILEDPAYMENTS', 'AUTOBILLAMT',
        'BILLINGPERIOD', 'BILLINGFREQUENCY', 'TOTALBILLINGCYCLES', 'AMT', 'TRIALBILLINGPERIOD', 'TRIALBILLINGFREQUENCY',
        'TRIALTOTALBILLINGCYCLES', 'TRIALAMT', 'CURRENCYCODE', 'SHIPPINGAMT', 'TAXAMT', 'INITAMT', 'FAILEDINITAMTACTION','INVNUM'
    );

    /**
     * CreateRecurringPayment response map
     *
     * @var string[]
     */
    protected $_createRecurringPaymentResponse = array(
        'PROFILEID', 'PROFILESTATUS','INVNUM'
    );

    /**
     * Request/response for ManageRecurringPaymentStatus map
     *
     * @var string[]
     */
    protected $_manageRecurringPaymentStatusRequest = array('PROFILEID', 'ACTION','INVNUM');

    /**
     * Request for GetRecurringPaymentDetails
     *
     * @var string[]
     */
    protected $_getRecurringPaymentDetailsRequest = array('PROFILEID','INVNUM');

    /**
     * Response for GetRecurringPaymentDetails
     *
     * @var string[]
     */
    protected $_getRecurringPaymentDetailsResponse = array('STATUS','INVNUM' , /* TODO: lot of other stuff */);

    /**
     * @var \Magento\RecurringPayment\Model\QuoteImporter
     */
    protected $_quoteImporter;

    protected $_ppRecurringMapperFactory;

    protected $customerSession;


    /**
     * @param \Magento\Customer\Helper\Address $customerAddress
     * @param \Magento\Logger $logger
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param Logger $logAdapterFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\RecurringPayment\Model\QuoteImporter $quoteImporter
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Helper\Address $customerAddress,
        \Psr\Log\LoggerInterface $logger,
        Logger $logAdapterFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Paypal\Model\Api\ProcessableExceptionFactory $processableExceptionFactory,
        \Magento\Framework\Exception\LocalizedExceptionFactory $frameworkExceptionFactory,
        \Magento\Framework\HTTP\Adapter\CurlFactory $curlFactory,
        \Magento\RecurringPayment\Model\QuoteImporter $quoteImporter,
        \Tatva\Paypalrec\Model\PaypalRecurringMapperFactory $ppRecurringMapperFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        array $data = array()
    ) {
        parent::__construct(
            $customerAddress,
            $logger,
            $logAdapterFactory,
            $localeResolver,
            $regionFactory,
            $countryFactory,
            $processableExceptionFactory,
            $frameworkExceptionFactory,
            $curlFactory,
            $data
        );
        $this->_quoteImporter = $quoteImporter;
        $this->_ppRecurringMapperFactory = $ppRecurringMapperFactory;
        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;

        $this->updateGlobalMap();
    }


    protected function updateGlobalMap()
    {
        $recurring_var_map = array(
            'TOKEN'                     => 'token',
            'SUBSCRIBERNAME'            => 'subscriber_name',
            'PROFILESTARTDATE'          => 'start_datetime',
            'PROFILEREFERENCE'          => 'internal_reference_id',
            'DESC'                      => 'schedule_description',
            'MAXFAILEDPAYMENTS'         => 'suspension_threshold',
            'AUTOBILLAMT'               => 'bill_failed_later',
            'BILLINGPERIOD'             => 'period_unit',
            'BILLINGFREQUENCY'          => 'period_frequency',
            'TOTALBILLINGCYCLES'        => 'period_max_cycles',
            'billing_amount' => 'amount',
            'TRIALBILLINGPERIOD'        => 'trial_period_unit',
            'TRIALBILLINGFREQUENCY'     => 'trial_period_frequency',
            'TRIALTOTALBILLINGCYCLES'   => 'trial_period_max_cycles',
            'TRIALAMT'                  => 'trial_billing_amount',
            'CURRENCYCODE'              => 'currency_code',
            'SHIPPINGAMT'               => 'shipping_amount',
            'TAXAMT'                    => 'tax_amount',
            'INITAMT'                   => 'init_amount',
            'FAILEDINITAMTACTION'       => 'init_may_fail',
            'INVNUM' => 'inv_num',
        );
        $this->_globalMap = array_merge($this->_globalMap,$recurring_var_map);

        $invoice = ['INVNUM'];
        $this->_paymentInformationResponse = array_merge($this->_paymentInformationResponse,$invoice);
    }


    /**
     * SetExpressCheckout call
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_SetExpressCheckout
     * TODO: put together style and giropay settings
     */
    public function callSetExpressCheckout()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_setExpressCheckoutRequest);
        $request = $this->_exportToRequest($this->_setExpressCheckoutRequest);
        $this->_exportLineItems($request);

        // import/suppress shipping address, if any
        $options = $this->getShippingOptions();
        if ($this->getAddress()) {
            $request = $this->_importAddresses($request);
            $request['ADDROVERRIDE'] = 1;
        } elseif ($options && (count($options) <= 10)) { // doesn't support more than 10 shipping options
            $request['CALLBACK'] = $this->getShippingOptionsCallbackUrl();
            $request['CALLBACKTIMEOUT'] = 6; // max value
            $request['MAXAMT'] = $request['AMT'] + 999.00; // it is impossible to calculate max amount
            $this->_exportShippingOptions($request);
        }

        
        $payments = $this->_quoteImporter->import($this->getQuote());
        if ($payments) {
            $i = 0;
            foreach ($payments as $payment) {
                $payment->setMethodCode(\Magento\Paypal\Model\Config::METHOD_WPP_EXPRESS);
                if (!$payment->isValid()) {
                    throw new \Magento\Framework\Exception\LocalizedException($payment->getValidationErrors());
                }
                $request["L_BILLINGTYPE{$i}"] = 'RecurringPayments';
                $request["L_PAYMENTREQUEST_{$i}_ITEMCATEGORY{$i}"] = 'Digital';
                $request["L_BILLINGAGREEMENTDESCRIPTION{$i}"] = $payment->getScheduleDescription();
                $i++;
            }
        }

        $response = $this->call(self::SET_EXPRESS_CHECKOUT, $request);
        $this->_importFromResponse($this->_setExpressCheckoutResponse, $response);
    }

    /**
     * GetExpressCheckoutDetails call
     *
     * @return void
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_GetExpressCheckoutDetails
     */
    public function callGetExpressCheckoutDetails()
    {
        $this->_prepareExpressCheckoutCallRequest($this->_getExpressCheckoutDetailsRequest);
        $request = $this->_exportToRequest($this->_getExpressCheckoutDetailsRequest);
        $response = $this->call(self::GET_EXPRESS_CHECKOUT_DETAILS, $request);
        $this->_importFromResponse($this->_paymentInformationResponse, $response);
        $this->_exportAddressses($response);
        
        // For our use in recurring mapping
        $this->setData('inv_num',$response['INVNUM']);
        $this->addUpdateRecurringMapper();
    }

    /**
     * We have skipped this as in our case there will be no any other simple purchaseable product.
     * DoExpressCheckout call
     *
     * @return void
     * @link https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_nvp_r_DoExpressCheckoutPayment
     */
    public function callDoExpressCheckoutPayment()
    {
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $quoteCollection = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
        // $quote = $quoteCollection->create()->load($quoteId);
        $quoteId = $_SESSION['checkout']['quote_id_1'];
        $quote = $this->quoteFactory->create()->load($quoteId);
        foreach ($quote->getAllItems() as $value) {
            $product_type = $value->getProductType();
        }

        if($product_type == 'downloadable') { 

            $this->_prepareExpressCheckoutCallRequest($this->_doExpressCheckoutPaymentRequest);
            $request = $this->_exportToRequest($this->_doExpressCheckoutPaymentRequest);
            $this->_exportLineItems($request);

            if ($this->getAddress()) {
                $request = $this->_importAddresses($request);
                $request['ADDROVERRIDE'] = 1;
            }

            $response = $this->call(self::DO_EXPRESS_CHECKOUT_PAYMENT, $request);
            $this->_importFromResponse($this->_paymentInformationResponse, $response);
            $this->_importFromResponse($this->_doExpressCheckoutPaymentResponse, $response);
            $this->_importFromResponse($this->_createBillingAgreementResponse, $response);
        }
    }

    /**
     * CreateRecurringPayment call
     *
     * @return void
     */
    public function callCreateRecurringPayment()
    {
        $request = $this->_exportToRequest($this->_createRecurringPaymentRequest);
        $response = $this->call('CreateRecurringPaymentsProfile', $request);
        $this->addUpdateRecurringMapper(true,$response['PROFILEID']);
        $this->_importFromResponse($this->_createRecurringPaymentResponse, $response);
        $this->_analyzeRecurringPaymentStatus($this->getRecurringPaymentStatus(), $this);
    }

    /**
     * ManageRecurringPaymentStatus call
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function callManageRecurringPaymentStatus()
    {
        $request = $this->_exportToRequest($this->_manageRecurringPaymentStatusRequest);
        if (isset($request['ACTION'])) {
            $request['ACTION'] = $this->_filterRecurringPaymentActionToNvp($request['ACTION']);
        }
        try {
            $this->call('ManageRecurringPaymentsProfileStatus', $request);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ((in_array(11556, $this->_callErrors) && 'Cancel' === $request['ACTION'])
                || (in_array(11557, $this->_callErrors) && 'Suspend' === $request['ACTION'])
                || (in_array(11558, $this->_callErrors) && 'Reactivate' === $request['ACTION'])
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We can\'t change the status because the current status doesn\'t match the real status.')
                );
            }
            throw $e;
        }
    }

    /**
     * GetRecurringPaymentDetails call
     *
     * @param \Magento\Framework\DataObject $result
     * @return void
     */
    public function callGetRecurringPaymentDetails(\Magento\Framework\DataObject $result)
    {
        $request = $this->_exportToRequest($this->_getRecurringPaymentDetailsRequest);
        $response = $this->call('GetRecurringPaymentsProfileDetails', $request);
        $this->_importFromResponse($this->_getRecurringPaymentDetailsResponse, $response);
        $this->_analyzeRecurringPaymentStatus($this->getStatus(), $result);
    }

    /**
     * Convert RP management action to NVP format
     *
     * @param string $value
     * @return string|null
     */
    protected function _filterRecurringPaymentActionToNvp($value)
    {
        switch ($value) {
            case 'cancel':
                return 'Cancel';
            case 'suspend':
                return 'Suspend';
            case 'activate':
                return 'Reactivate';
            default:
                break;
        }
    }

    /**
     * Check the obtained RP status in NVP format and specify the payment state
     *
     * @param string $value
     * @param \Magento\Framework\DataObject $result
     * @return void
     */
    protected function _analyzeRecurringPaymentStatus($value, \Magento\Framework\DataObject $result)
    {
        switch ($value) {
            case 'ActiveProfile':
            case 'Active':
                $result->setIsProfileActive(true);
                break;
            case 'PendingProfile':
                $result->setIsProfilePending(true);
                break;
            case 'CancelledProfile':
            case 'Cancelled':
                $result->setIsProfileCanceled(true);
                break;
            case 'SuspendedProfile':
            case 'Suspended':
                $result->setIsProfileSuspended(true);
                break;
            case 'ExpiredProfile':
            case 'Expired': // ??
                $result->setIsProfileExpired(true);
                break;
            default:
                break;
        }
    }


    protected function addUpdateRecurringMapper($update = false , $pp_recurring_pro_id = null)
    {   
        if($update){
            $ppRecurringMapperObj =  $this->_ppRecurringMapperFactory->create();
            $ppRecurringMapperCollection = $ppRecurringMapperObj->getCollection()
                ->addFieldToFilter('customer_id',$this->customerSession->getCustomerId())
                ->addFieldToFilter('checkout_token',$this->getData('token'));
            $ppRecurringMapperCollection->getSelect()->order('map_id desc')->limit(1);
            
            if($ppRecurringMapperCollection->getSize()){
                foreach ($ppRecurringMapperCollection as $item) {
                    $item->setData('rp_profile_id',$pp_recurring_pro_id);
                    $item->save();
                }
            }
        }
        else{
            $ppRecurringMapperObj =  $this->_ppRecurringMapperFactory->create();
            $data['customer_id'] = $this->customerSession->getCustomerId();
            $data['checkout_token'] = $this->getData('token');
            $data['invoice'] = $this->getData('inv_num');
            $data['checkout_token'] = $this->getData('token');
            $ppRecurringMapperObj->setData($data)->save();
        }
    }

}
