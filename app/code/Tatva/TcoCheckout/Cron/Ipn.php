<?php
namespace Tatva\TcoCheckout\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

class Ipn
{

    protected $_ipnDataFactory;
    protected $_dateTimeFactory;


    public function __construct(       
       \Tatva\TcoCheckout\Model\IpnDataFactory $ipnDataFactory,
       \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
       \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_ipnDataFactory = $ipnDataFactory;
        $this->_dateTimeFactory = $dateTimeFactory;
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $ipnTime = $this->_dateTimeFactory->create()->gmtDate();

        $strtime = strtotime($ipnTime)-60;
        $checktime = date("Y-m-d H:i:s",$strtime);
    
        $ipnCollection = $this->_ipnDataFactory->create()->getCollection();
        $ipnCollection->addFieldToFilter("ipn_time",array("lteq"=>$checktime));

        foreach($ipnCollection as $rowData)
        {            
            $post_fields = unserialize($rowData->getIpnData());

            $headers = array("Accept: application/json");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $checkout_ipn = "https://www.slideteam.net/tco/ins/notification/";
            curl_setopt($ch, CURLOPT_URL, $checkout_ipn);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $result = curl_exec($ch);
              
            if(curl_errno($ch)){
                echo curl_error($ch);
            }

            $responsecode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
              
            curl_close($ch);            

            if($responsecode != 200)
            {
                $isError = $rowData->getIsError();

                if(empty($isError))
                {
                    $mail = new \Zend_Mail();
                    $message = "";
                    foreach($post_fields as $field => $value)
                    {
                        $message.= $field.":".$value."<br/>\n";
                    }          
                    $mail->setFrom("support@slideteam.net",'SlideTeam Support');
                    $mail->setSubject('500 error in 2Checkout IPN');
                    $mail->setBodyHtml($message);


                    $to_email = explode(',',$this->_scopeConfig->getValue('button/2checkout/to_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                    $cc_email = explode(',',$this->_scopeConfig->getValue('button/2checkout/cc_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));

                    $send = 0;
                    if(!empty($to_email))
                    {
                        $mail->addTo($to_email);
                        $send = 1;
                    }
                    if(!empty($cc_email))
                    {
                        $mail->addCc($cc_email);
                    }
                    
                    if($send) :
                        $mail->send();
                    endif;
                }
                $rowData->setIsError(1)->save();
            }
            else
            { 
                $rowData->delete();
            }
            
        }

    }
}