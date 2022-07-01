<?php

namespace Tco\Checkout\Model;

use Magento\Quote\Model\Quote\Payment;

class Checkout extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'tco_checkout';
    protected $_code = self::CODE;
    protected $_isGateway = false;
    protected $_isOffline = false;
    protected $_canRefund = true;
    protected $_isInitializeNeeded = false;
    protected $helper;
    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array(
        'AFN', 'ALL', 'DZD', 'ARS', 'AUD', 'AZN', 'BSD', 'BDT', 'BBD',
        'BZD', 'BMD', 'BOB', 'BWP', 'BRL', 'GBP', 'BND', 'BGN', 'CAD',
        'CLP', 'CNY', 'COP', 'CRC', 'HRK', 'CZK', 'DKK', 'DOP', 'XCD',
        'EGP', 'EUR', 'FJD', 'GTQ', 'HKD', 'HNL', 'HUF', 'INR', 'IDR',
        'ILS', 'JMD', 'JPY', 'KZT', 'KES', 'LAK', 'MMK', 'LBP', 'LRD',
        'MOP', 'MYR', 'MVR', 'MRO', 'MUR', 'MXN', 'MAD', 'NPR', 'TWD',
        'NZD', 'NIO', 'NOK', 'PKR', 'PGK', 'PEN', 'PHP', 'PLN', 'QAR',
        'RON', 'RUB', 'WST', 'SAR', 'SCR', 'SGF', 'SBD', 'ZAR', 'KRW',
        'LKR', 'SEK', 'CHF', 'SYP', 'THB', 'TOP', 'TTD', 'TRY', 'UAH',
        'AED', 'USD', 'VUV', 'VND', 'XOF', 'YER'
    );
    protected $_formBlockType = 'Tco\Checkout\Block\Form\Checkout';
    protected $_infoBlockType = 'Tco\Checkout\Block\Info\Checkout';

    protected $httpClientFactory;
    protected $orderSender;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Tco\Checkout\Helper\Checkout $helper,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
    ) {
        $this->helper = $helper;
        $this->orderSender = $orderSender;
        $this->httpClientFactory = $httpClientFactory;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger
        );

        $this->_minAmount = $this->getConfigData('min_order_total');
        $this->_maxAmount = $this->getConfigData('max_order_total');
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote && (
                $quote->getBaseGrandTotal() < $this->_minAmount
                || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    public function buildCheckoutRequest($quote)
    {
        $billing_address = $quote->getBillingAddress();
        
        $params = array();

        $params["sid"]                  = $this->getConfigData("merchant_id");
        $params["merchant_order_id"]    = $quote->getReservedOrderId();
        $params["cart_order_id"]        = $quote->getReservedOrderId();
        $params["currency_code"]        = $quote->getQuoteCurrencyCode();
        $params["total"]                = round($quote->getGrandTotal(), 2);
        $params["card_holder_name"]     = $billing_address->getName();
        $params["street_address"]       = $billing_address->getStreet()[0];
        if (count($billing_address->getStreet()) > 1) {
            $params["street_address2"]  = $billing_address->getStreet()[1];
        }
        $params["city"]                 = $billing_address->getCity();
        $params["state"]                = $billing_address->getRegion();
        $params["zip"]                  = $billing_address->getPostcode();
        $params["country"]              = $billing_address->getCountryId();
        $params["email"]                = $quote->getCustomerEmail();
        $params["phone"]                = $billing_address->getTelephone();
        $params["return_url"]           = $this->getCancelUrl();
        $params["x_receipt_link_url"]   = $this->getReturnUrl();
        $params["purchase_step"]        = "payment-method";

        return $params;
    }

    public function validateResponse($orderNumber, $total, $key)
    {
        $secretWord = $this->getConfigData('secret_word');
        $merchantId = $this->getConfigData('merchant_id');

        $stringToHash = strtoupper(md5($secretWord . $merchantId . $orderNumber . $total));
        if ($stringToHash != $key) {
            $result = false;
        } else {
            $result = true;
        }
        return $result;
    }

    public function postProcessing(\Magento\Sales\Model\Order $order, \Magento\Framework\DataObject $payment, $response) {
        // Update payment details
        $payment->setTransactionId($response['invoice_id']);
        $payment->setIsTransactionClosed(0);
        $payment->setTransactionAdditionalInfo('tco_order_number', $response['order_number']);
        $payment->setAdditionalInformation('tco_order_number', $response['order_number']);
        $payment->setAdditionalInformation('tco_order_status', 'approved');
        $payment->place();


        // Update order status
        
        $order->setStatus($this->getOrderStatus());
        $order->setExtOrderId($response['order_number']);
        $order->save();

        //Set Billing Address
        $billingAddress = $order->getBillingAddress();
        $billingAddress->setFirstname($response['first_name'])
                        ->setLastname($response['last_name'])
                        ->setStreet($response['street_address'])
                        ->setCity($response['city'])
                        ->setCountryId($this->getBillingCountry($response['country']))
                        ->setRegion($response['state'])
                        ->setPostcode($response['zip'])
                        ->save();

        // Send email confirmation
        $this->orderSender->send($order);
    }

    //Country Code Conversion
    public function getBillingCountry($tcoCountry)
    {       
        $countryArray = array(
            "ALA"=>"AX","AFG"=>"AF","ALB"=>"AL","DZA"=>"DZ","ASM"=>"AS","AND"=>"AD","AGO"=>"AO","AIA"=>"AI","ATA"=>"AQ","ATG"=>"AG","ARG"=>"AR","ARM"=>"AM","ABW"=>"AW","AUS"=>"AU","AUT"=>"AT","AZE"=>"AZ","BHS"=>"BS","BHR"=>"BH","BGD"=>"BD","BRB"=>"BB","BLR"=>"BY","BEL"=>"BE","BLZ"=>"BZ","BEN"=>"BJ","BMU"=>"BM","BTN"=>"BT",
            "BOL"=>"BO","BIH"=>"BA","BWA"=>"BW","BVT"=>"BV","BRA"=>"BR","IOT"=>"IO","BRN"=>"BN","BGR"=>"BG","BFA"=>"BF","BDI"=>"BI","KHM"=>"KH","CMR"=>"CM","CAN"=>"CA","CPV"=>"CV","CYM"=>"KY","CAF"=>"CF","TCD"=>"TD","CHL"=>"CL","CHN"=>"CN","CXR"=>"CX","CCK"=>"CC","COL"=>"CO","COM"=>"KM","COG"=>"CG","COD"=>"CD","COK"=>"CK","CRI"=>"CR","CIV"=>"CI","HRV"=>"HR","CYP"=>"CY","CZE"=>"CZ","DNK"=>"DK","DJI"=>"DJ",
            "DMA"=>"DM","DOM"=>"DO","ECU"=>"EC","EGY"=>"EG","SLV"=>"SV","GNQ"=>"GQ","ERI"=>"ER","EST"=>"EE","ETH"=>"ET","FLK"=>"FK","FRO"=>"FO","FJI"=>"FJ","FIN"=>"FI","FRA"=>"FR","GUF"=>"GF","PYF"=>"PF","ATF"=>"TF","GAB"=>"GA","GMB"=>"GM","GEO"=>"GE","DEU"=>"DE","GHA"=>"GH","GIB"=>"GI","GRC"=>"GR","GRL"=>"GL","GRD"=>"GD","GLP"=>"GP","GUM"=>"GU","GTM"=>"GT","GGY"=>"GG","GIN"=>"GN","GNB"=>"GW","GUY"=>"GY",
            "HTI"=>"HT","HMD"=>"HM","HND"=>"HN","HKG"=>"HK","HUN"=>"HU","ISL"=>"IS","IND"=>"IN","IDN"=>"ID","IRQ"=>"IQ","IRL"=>"IE","IMN"=>"IM","ISR"=>"IL","ITA"=>"IT","JAM"=>"JM","JPN"=>"JP","JEY"=>"JE","JOR"=>"JO","KAZ"=>"KZ","KEN"=>"KE","KIR"=>"KI","KOR"=>"KR","KWT"=>"KW","KGZ"=>"KG","LAO"=>"LA","LVA"=>"LV","LBN"=>"LB","LSO"=>"LS","LBR"=>"LR","LBY"=>"LY","LTE"=>"LI","LTU"=>"LT","LUX"=>"LU","MAC"=>"MO","MKD"=>"MK",
            "MDG"=>"MG","MWI"=>"MW","MYS"=>"MY","MDV"=>"MV","MLI"=>"ML","MLT"=>"MT","MHL"=>"MH","MTQ"=>"MQ","MRT"=>"MR","MUS"=>"MU","MYT"=>"YT","MEX"=>"MX","FSM"=>"FM","MDA"=>"MD","MCO"=>"MC","MNG"=>"MN","MNE"=>"ME","MSR"=>"MS","MAR"=>"MS","MOZ"=>"MZ","MMR"=>"MM","NAM"=>"NA","NRU"=>"NR","NPL"=>"NP","NLD"=>"NL","NCL"=>"NC","NZL"=>"NZ","NIC"=>"NI","NER"=>"NE","NGA"=>"NG","NIU"=>"NU","NFK"=>"NF","MNP"=>"MP",
            "NOR"=>"NO","OMN"=>"OM","PAK"=>"PK","PLW"=>"PW","PSE"=>"PS","PAN"=>"PA","PNG"=>"PG","PRY"=>"PY","PER"=>"PE","PHL"=>"PH","PCN"=>"PN","POL"=>"PL","PRT"=>"PT","PRI"=>"PR","QAT"=>"QA","REU"=>"RE","ROU"=>"RO","RUS"=>"RU","RWA"=>"RW","WSM"=>"WS","SMR"=>"SM","STP"=>"ST","SAU"=>"SA","SEN"=>"SN","SRB"=>"RS","SCG"=>"RS","SYC"=>"SC","SLE"=>"SL","SGP"=>"SG",
            "SVK"=>"SK","SVN"=>"SI","SLB"=>"SB","SOM"=>"SO","ZAF"=>"ZA","SGS"=>"GS","ESP"=>"ES","LKA"=>"LK","SUR"=>"SR","SJM"=>"SJ","SWZ"=>"SZ","SWE"=>"SE","CHE"=>"CH","TWN"=>"TW","TJK"=>"TJ","TZA"=>"TZ","THA"=>"TH","TLS"=>"TL","TGO"=>"TG","TKL"=>"TK","TON"=>"TO","TTO"=>"TT","TUN"=>"TN","TUR"=>"TR","TKM"=>"TM","TCA"=>"TC","TUV"=>"TV","UGA"=>"UG","UKR"=>"UA","ARE"=>"AE","GBR"=>"GB","USA"=>"US","UMI"=>"UM","URY"=>"UY",
            "UZB"=>"UZ","VUT"=>"VU","VAT"=>"VA","VEN"=>"VE","VNM"=>"VN","WLF"=>"WF","ESH"=>"EH","YEM"=>"YE","ZMB"=>"ZM","ZWE"=>"ZW","SHN"=>"SH","KNA"=>"KN","LCA"=>"LC","SPM"=>"PM","VCT"=>"VC","ZAR"=>"ZM","YUG"=>"YE","VGB"=>"UM","VIR"=>"VI","ANT"=>"AQ","FXX"=>"FR","BES"=>"NL"
        );
        
        return $countryArray[$tcoCountry];
    }

    public function getCgiUrl()
    {
        $url = $this->getConfigData('sandbox') ?
            $this->getConfigData('cgi_url_sandbox') : $this->getConfigData('cgi_url');
        return $url;
    }

    public function getApiUrl()
    {
        $url = $this->getConfigData('sandbox') ?
            $this->getConfigData('api_url_sandbox') : $this->getConfigData('api_url');
        return $url;
    }
    
    public function getRedirectUrl()
    {
        $url = $this->helper->getUrl($this->getConfigData('redirect_url'));
        return $url;
    }

    public function getReturnUrl()
    {
        $url = $this->helper->getUrl($this->getConfigData('return_url'));
        return $url;
    }

    public function getCancelUrl()
    {
        $url = $this->helper->getUrl($this->getConfigData('cancel_url'));
        return $url;
    }

    public function getInline()
    {
        $value = $this->getConfigData('inline');
        return $value;
    }

    public function getOrderStatus()
    {
        $value = $this->getConfigData('order_status');
        return $value;
    }

    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if ($amount <= 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid amount for refund.'));
        }

        if (!$payment->getParentTransactionId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid transaction ID.'));
        }

        $orderNumber = $payment->getAdditionalInformation('tco_order_number');

        $args = array(
            'sale_id' => $orderNumber,
            'category' => 5,
            'comment' => 'Refund issued by merchant.',
            'amount' => $amount,
            'currency' => 'vendor'
        );

        $client = $this->httpClientFactory->create();
        $path = 'sales/refund_invoice';
        $url = $this->getApiUrl();
        $client->setUri($url . $path);
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setAuth($this->getConfigData('api_user'), $this->getConfigData('api_pass'));

        $client->setHeaders(
           [
               'Accept: application/json'
           ]
        );
        $client->setParameterPost($args);
        $client->setMethod(\Zend_Http_Client::POST);

        try {
            $response = $client->request();
            $responseBody = json_decode($response->getBody(), true);
            if (isset($responseBody['errors'])) {
                $this->_logger->critical(sprintf('Error Refunding Invoice: "%s"', $responseBody['errors'][0]['message']));
                throw new \Magento\Framework\Exception\LocalizedException(__($responseBody['errors'][0]['message']));
            } elseif (!isset($responseBody['response_code']) || !isset($responseBody['response_message'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Error refunding transaction.'));
            } elseif ($responseBody['response_code'] != 'OK') {
                throw new \Magento\Framework\Exception\LocalizedException(__($responseBody['response_message']));
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }

        return $this;
    }
}