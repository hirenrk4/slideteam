<?php
namespace Tatva\ZohoCrm\Cron;

class AccessToken
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;
    /**
    * HelperData
    * @var \Tatva\ZohoCrm\Helper\Data
    */
    protected $helperData;
    /**
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param \Magento\Framework\HTTP\Client\Curl $curl
    */
    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Tatva\ZohoCrm\Helper\Data $helperData
    ) {
        $this->curl = $curl;
        $this->helperData = $helperData;
    }
    public function execute()
    {         
        if($this->helperData->isEnabled()){
            try{
                $postUrl = 'https://accounts.zoho.in/oauth/v2/token';

                $data = [
                    'client_id'=>$this->helperData->getClientId(),
                    'grant_type'=>'refresh_token',
                    'client_secret'=>$this->helperData->getClientSecret(),
                    'refresh_token'=>$this->helperData->getRefreshToken()
                ];
                $headers = ["Content-Type" => "application/x-www-form-urlencoded"];

                $this->curl->setHeaders($headers);
                $this->curl->post($postUrl, $data);

                $response = $this->curl->getBody();
                $response = json_decode($response);
                $response = json_decode(json_encode($response), true);

                if(array_key_exists('access_token',$response) && !empty($response['access_token'])){
                    $this->helperData->setToken($response['access_token']);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}