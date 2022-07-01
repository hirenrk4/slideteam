<?php

namespace Tco\Checkout\Helper;

class OrderCreate extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
    * @param Magento\Framework\App\Helper\Context $context
    * @param Magento\Store\Model\StoreManagerInterface $storeManager
    * @param Magento\Catalog\Model\Product $product
    * @param Magento\Framework\Data\Form\FormKey $formKey $formkey,
    * @param Magento\Quote\Model\Quote $quote,
    * @param Magento\Customer\Model\CustomerFactory $customerFactory,
    * @param Magento\Sales\Model\Service\OrderService $orderService,
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Tco\Checkout\Model\Checkout $paymentMethod
    ) {
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->_formkey = $formkey;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        $this->_paymentMethod = $paymentMethod;
        parent::__construct($context);
    }
 
    /**
     * Create Order On Your Store
     * 
     * @param array $orderData
     * @return array
     * 
    */
    public function CreateOrder($quote,$params) 
    {       
        $code = "tco_checkout";
        $orderNumber = $params['sale_id'];

        $customerModel = $this->customerFactory->create();
        $customerModel->setWebsiteId(1);
        $customerModel->loadByEmail($params['customer_email']);
        $customerId =  $customerModel->getEntityId();        

        //$orderTotal = number_format($quote->getGrandTotal(), 2, '.', '');
        $quote = $this->quote->load($quote->getId());
        $quote->collectTotals()->save();
        $quote->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER);
        $quote->setCustomerEmail($params['customer_email']);
        $quote->setCustomerId($customerId);

        
        $country = $this->getBillingCountry($params['bill_country']);
        $fullname = explode(" ",$params['customer_name']);
        
        if(!isset($fullname[1]) || empty($fullname[1])) :
            $lastname = $fullname[0];
        else :
            $lastname = $fullname[1];
        endif;

        $finalAddress = $params['bill_street_address']." ".$params['bill_street_address2'];
        if(empty($params['bill_street_address']) || $params['bill_street_address'] == "-")
        {
            if(empty($params['bill_street_address2']) || $params['bill_street_address2'] == "-")
            {
                $finalAddress = "Sample Address";     
            } 
            else
            {
                $finalAddress = $params['bill_street_address2'];
            }
        }

        $finalCity = $params['bill_city'];
        if(empty($params['bill_city']) || $params['bill_city'] == "-")
        {
            $finalCity = "Sample City";
        }

        $finalCountry = $country;
        if(empty($country))
        {
            $finalCountry = "US";   
        }

        $finalState = $params['bill_state'];
        if(empty($params['bill_state']) || $params['bill_state'] == "-")
        {
            $finalState = "Sample State";
        }

        $finalPostcode = $params['bill_postal_code'];
        if(empty($params['bill_postal_code']) || $params['bill_postal_code'] == "-")
        {
            $finalPostcode = "123456";
        }

        $tempOrder=[     
            'billing_address' =>[
                'firstname'    => $fullname[0],
                'lastname'     => $lastname,
                'street' => $finalAddress,
                'city' => $finalCity,
                'country_id' => $finalCountry,
                'region' => $finalState,
                'postcode' => $finalPostcode,
                'save_in_address_book' => 0]
        ];
        
        $quote->getBillingAddress()->addData($tempOrder['billing_address']);
        $quote->getShippingAddress()->addData($tempOrder['billing_address']);

        $quote->getPayment()->setQuote($quote);
        $quote->setPaymentMethod($code);
        $quote->getPayment()->importData(['method' => $code]);
        $quote->setTotalsCollectedFlag(1);
        $quote->save();

        $quote->collectTotals()->save();
        
        try
        {            
            $order = $this->quoteManagement->submit($quote);
            $order->setEmailSent(0);
            $increment_id = $order->getRealOrderId();
            $order->setState("new")->setStatus("processing")->save();

            $payment = $order->getPayment();   
            $paymentParams = array(
                'invoice_id'=>$params['invoice_id'],
                'order_number'=>$orderNumber
            ); 
            if(!empty($params['vendor_order_id']))
            {
                $this->_paymentMethod->postProcessing($order, $payment, $paymentParams);

                $msg = "Order Id :: ".$params['vendor_order_id']." is created from 2checkout ipn.";
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-type: text/html; charset=iso-8859-1';

                // Additional headers
                $headers[] = 'From: support@slideteam.net';

                mail("ron@slideteam.net","2Checkout Subscription Order Issue",$msg,implode("\r\n", $headers));
                mail("geetika.gosain@slideteam.net","2Checkout Subscription Order Issue",$msg,implode("\r\n", $headers));
                mail("uminfy@yahoo.com","2Checkout Subscription Order Issue",$msg,implode("\r\n", $headers));
                mail("krunal.vakharia@tatvasoft.com","2Checkout Subscription Order Issue",$msg,implode("\r\n", $headers));    
            } else {
                $msg = "Invoice Id :: ".$params['invoice_id'].", Customer Email :: ".$params['customer_email'].", Sales Id :: ".$params['sale_id']." ";
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-type: text/html; charset=iso-8859-1';

                // Additional headers
                $headers[] = 'From: support@slideteam.net';

                mail("ron@slideteam.net","Order placed using 2Checkout Direct Link",$msg,implode("\r\n", $headers));
                mail("geetika.gosain@slideteam.net","Order placed using 2Checkout Direct Link",$msg,implode("\r\n", $headers));
                mail("uminfy@yahoo.com","Order placed using 2Checkout Direct Link",$msg,implode("\r\n", $headers));
                mail("krunal.vakharia@tatvasoft.com","Order placed using 2Checkout Direct Link",$msg,implode("\r\n", $headers));
            }      
            
            
        }
        catch(\Magento\Framework\Exception\LocalizedException $e)
        {
            throw $e;            
        }
    }
    
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

}
