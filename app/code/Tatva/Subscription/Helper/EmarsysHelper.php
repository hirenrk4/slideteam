<?php

namespace Tatva\Subscription\Helper;
use \Magento\Framework\App\Helper\Context;

class EmarsysHelper extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }


    public function send($requestType, $endPoint, $requestBody = '')
    {
        $error = false;
        if (!in_array($requestType, array('GET', 'POST', 'PUT', 'DELETE'))) {
            throw new Exception('Send first parameter must be "GET", "POST", "PUT" or "DELETE"');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        switch ($requestType)
        {
            case 'GET':
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
                break;
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);
                break;
        }
        curl_setopt($ch, CURLOPT_HEADER, false);

        $_username = $this->scopeConfig->getValue('button/emarsys_config/field5',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $_secret = $this->scopeConfig->getValue('button/emarsys_config/field6',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $_suiteApiUrl = $this->scopeConfig->getValue('button/emarsys_config/field7',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $requestUri = $_suiteApiUrl . $endPoint;
        curl_setopt($ch, CURLOPT_URL, $requestUri);

       
        $nonce = 'd36e316282959a9ed4c89851497a717f';
        $timestamp = gmdate("c");
        $passwordDigest = base64_encode(sha1($nonce . $timestamp . $_secret, false));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'X-WSSE: UsernameToken ' .
                'Username="'.$_username.'", ' .
                'PasswordDigest="'.$passwordDigest.'", ' .
                'Nonce="'.$nonce.'", ' .
                'Created="'.$timestamp.'"',
            'Content-type: application/json;charset=\\"utf-8\\"',
            )
        );

        $output = curl_exec($ch);

        $data = json_decode($output);

        curl_close($ch);

        return $data;
    }
}