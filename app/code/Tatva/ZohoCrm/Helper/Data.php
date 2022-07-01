<?php

namespace Tatva\ZohoCrm\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;

class Data extends AbstractHelper
{
    public static $zohoApiUrl = "https://www.zohoapis.in/crm/v2/";
	/**
     * ResourceConfig
     *
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $resourceConfig;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $countryFactory;
    protected $cacheTypeList;
    protected $resourceConnection;
	const XML_PATH_ZOHOCRM = 'zohocrm/';
	/**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context context
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig resourceConfig
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     */
	public function __construct(
		Context $context,
		\Magento\Config\Model\ResourceModel\Config $resourceConfig,
		\Magento\Framework\HTTP\Client\Curl $curl,
		\Magento\Directory\Model\CountryFactory $countryFactory,
		\Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        ResourceConnection $resourceConnection
	){
		$this->resourceConfig = $resourceConfig;
		$this->curl = $curl;
		$this->countryFactory = $countryFactory;
		$this->cacheTypeList = $cacheTypeList;
        $this->resourceConnection = $resourceConnection;
		parent::__construct($context);
	}

	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

	public function isEnabled()
    {
        return $this->getConfigValue(self::XML_PATH_ZOHOCRM .'general/enable');
    }

	public function getClientId()
    {
        return $this->getConfigValue(self::XML_PATH_ZOHOCRM .'general/client_id');
    }

    public function getClientSecret()
    {
        return $this->getConfigValue(self::XML_PATH_ZOHOCRM .'general/client_secret');
    }

    public function getRefreshToken()
    {
        return $this->getConfigValue(self::XML_PATH_ZOHOCRM .'general/refresh_token');
    }

    public function getToken()
    {
        return $this->getConfigValue(self::XML_PATH_ZOHOCRM .'general/token');
    }

    public function getTokenQuery()
    {
        $connection = $this->resourceConnection->getConnection();
        $sql = "SELECT value  FROM `core_config_data` WHERE `path` = 'zohocrm/general/token'";
        $result = $connection->fetchAll($sql);
        return $result[0]['value'];
    }

    public function getCountries()
    {
        return $this->getConfigValue(self::XML_PATH_ZOHOCRM .'general/countries');
    }

    public function setToken($value)
    {
    	$this->resourceConfig->saveConfig(self::XML_PATH_ZOHOCRM .'general/token',$value,'default',0);
    	$this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
    	$this->cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
    }

    public function send($requestType, $endPoint, $requestBody='')
    {
        $apiUrl = self::$zohoApiUrl.$endPoint;
        $data = [
            'data' =>  [
                0=>$requestBody
            ]
        ];                
        $headers = ["Authorization" => "Zoho-oauthtoken ".$this->getTokenQuery(), "Content-Type" => "application/json"];
        $this->curl->setHeaders($headers);
        switch ($requestType)
        {
            case 'POST':                
                $requestBody['Layout']=array(
                            "name" => "Inside Sales Leads",
                            "id"=> "177166000000221404"
                        );
                $data = json_encode($data);
                $this->curl->post($apiUrl, $data);
                break;
            case 'PUT':
                $data = json_encode($data);
                $this->curl->put($apiUrl, $data);
                break;
        }        

        $response = $this->curl->getBody();
        $response = json_decode($response);
        $response = json_decode(json_encode($response), true);

        $writer = new \Zend\Log\Writer\Stream(BP . "/var/log/ZOHO_CRM/zohocrm_trace".date('d-m-Y').".log");
        //$writer = new \Zend\Log\Writer\Stream(BP . "/var/log/zohocrm_trace.log");
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<");        
        if(array_key_exists('data',$response)){
            $logger->info($response['data'][0]['status']);
        }
        $logger->info($response);
        $logger->info($data);
        $logger->info(">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>");
    }

    public function searchFromLeads($customer_id)
    {
        $response = $this->searchFromModule('Leads',$customer_id);
        return $response;
    }

    public function searchFromContacts($customer_id)
    {
        $response = $this->searchFromModule('Contacts',$customer_id);        
        return $response;
    }

    public function searchFromModule($moduleName,$customer_id)
    {
        $url=self::$zohoApiUrl.$moduleName.'/search?criteria=Customer_ID:equals:'.$customer_id.'&fields=Customer_ID';        
        $headers = ["Authorization" => "Zoho-oauthtoken ".$this->getTokenQuery(), "Content-Type" => "application/json"];
        $this->curl->setHeaders($headers);
        $this->curl->get($url);
        $response = $this->curl->getBody();
        $response =json_decode($response,true);
        $response['module']=$moduleName;
        return $response;
    }
    public function createCustomer($requestBody)
    {
        $requestBody['Layout']=array(
                            "name" => "Inside Sales Leads",
                            "id"=> "177166000000221404"
                        );
        $this->send('POST','Leads',$requestBody);
    }
    public function editCustomer($requestBody,$customer_id)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . "/var/log/ZOHO_CRM/zohocrm_search_trace".date('d-m-Y').".log");
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('editCustomer');

        $response = $this->searchFromLeads($customer_id);
        $logger->info('Leads Search');
        $logger->info($response);
        if(array_key_exists('data',$response) && !empty($response['data'][0]['id'])){
            $id =$response['data'][0]['id'];
            $requestBody['id']=$id;
            $requestBody['Layout']=array(
                            "name" => "Inside Sales Leads",
                            "id"=> "177166000000221404"
                        );
            $this->send('PUT','Leads',$requestBody);
        }else{
            $logger->info('Contacts Search');
            $response = $this->searchFromContacts($customer_id);
            $logger->info($response);
            if(array_key_exists('data',$response) && !empty($response['data'][0]['id'])){
                $id =$response['data'][0]['id'];
                $requestBody['id']=$id;
                $requestBody['Layout']=array(
                                "name" => "Inside Sales Contact",
                                "id"=> "177166000000344051"
                            );
                $this->send('PUT','Contacts',$requestBody);
            }
        }
    }

    public function isAllowCountry($isd_code,$country_code)
    {
    	$collection = $this->countryFactory->create()->getCollection()
    			->addFieldToFilter('country_id',$country_code)
    			->addFieldToFilter('isd_code',$isd_code);  
    	$countriesCollection = $collection->getData();
    	if(!empty($countriesCollection) && !empty($this->getCountries())){
    			$countries=explode(",",$this->getCountries());
    			$res = in_array($countriesCollection[0]['country_id'], $countries)?1:0;
    		return $res;
    	}
    	return 0;   	
    }

    public function isAllowIpCountry($country_code)
    {
        if(!empty($country_code) && !empty($this->getCountries())){
                $countries=explode(",",$this->getCountries());
                $res = in_array($country_code, $countries)?1:0;
            return $res;
        }
        return 0;
    }
    
    public function getCountryname($countryCode){    
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }

    public function compareSubscription($period_Name)
    {
        switch ($period_Name) {
            case 'Monthly':
                $period_Name = "Individual: Monthly";
                break;
            case 'Semi-annual':
                $period_Name = "Individual: Semi Annual";
                break;
            case 'Annual':
                $period_Name = "Individual: Annual";
                break;
            case 'Annual + Custom Design':
                $period_Name = "Individual Annual + Custom Slides";
                break;
            case 'Annual 4 User License':
                $period_Name = "Business: 4 Users";
                break;
            case 'Annual 20 User License':
                $period_Name = "Annual 20 User License";
                break;
            case 'Annual Company Wide Unlimited User License':
                $period_Name = "Business: Company Wide";
                break;
            case 'Annual 15 User Education License':
                $period_Name = "Education: 15 Users";
                break;
            case 'Annual UNLIMITED User Institute Wide License':
                $period_Name = "Education: Institute Wide";
                break;
        }
        return $period_Name;
    }
}