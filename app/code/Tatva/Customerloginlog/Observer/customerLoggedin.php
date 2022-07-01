<?php
namespace Tatva\Customerloginlog\Observer;
use Magento\Framework\Event\ObserverInterface;

class customerLoggedin implements ObserverInterface
{
    protected $_customerSession;	
	protected $_scopeConfig;
	protected $_ipemail;
	protected $_ipcount;
	protected $_resourceConnection;
	protected $_productdownloadhistorylogFactory;
	
    public function __construct
    (
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Tatva\Customerloginlog\Model\LoginlogFactory $loginlog,
		\Tatva\Customerloginlog\Model\CustomerloginipcountFactory $ipcount,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Tatva\Customerloginlog\Helper\Email $ipemail,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		\Tatva\Catalog\Model\ProductdownloadhistorylogFactory $productdownloadhistorylogFactory
    ) 
    {
        $this->_date = $date;
        $this->_loginlog = $loginlog;
        $this->_ipcount = $ipcount;
        $this->_ipemail = $ipemail;
        $this->_remoteAddress = $remoteAddress;
        $this->_customerSession = $customerSession;
		$this->_scopeConfig = $scopeConfig;
		$this->_resourceConnection = $resourceConnection;
		$this->_productdownloadhistorylogFactory = $productdownloadhistorylogFactory;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer_id = $this->_customerSession->getCustomerId();
        $ip =  $this->_remoteAddress->getRemoteAddress();
        $date = $this->_date->gmtDate();
		
		$logmodel = $this->_loginlog->create();
        $data = $logmodel;
        $data->setCustomer_id($customer_id);
        $data->setIp($ip);
        $data->setTimestamp($date);
        $data->save();

		$currentTime = $date;
		$ipcountmodel = $this->_ipcount->create();
		$ipcollection = $ipcountmodel->getCollection()->addFieldToFilter('customer_id',['eq' => $customer_id])->getData();


        $connection = $this->_resourceConnection->getConnection();
		$tableName = $this->_resourceConnection->getTableName('productdownload_history_log');
		$sql = "Select count(DISTINCT product_id) as download_products FROM " . $tableName." where customer_id=".$customer_id." and download_date >= (select from_date from subscription_history where customer_id=".$customer_id." order by subscription_history_id desc limit 1)" ;
		$result = $connection->fetchAll($sql);
		$downloadproducts = $result[0]['download_products'];

		$accesskey = $this->_scopeConfig->getValue('button/cus_download/accesskey',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
		if(!empty($ipcollection))
		{
			$lastTimeDate = date('Y-m-d',strtotime($ipcollection[0]['timestamp']));
			$pastdate = date('Y-m-d',strtotime("-7 days"));	
			
			if(strtotime($pastdate) < strtotime($lastTimeDate))
			{				
				$finalDate = date('Y-m-d', strtotime('+1 day', strtotime($lastTimeDate)));
			}	
			else
			{
				$finalDate = $pastdate;
			}
			
			$logcollection = $logmodel->getCollection()->addFieldToFilter('customer_id',['eq' => $customer_id]); 
			$logcollection->addFieldToFilter("timestamp",['gteq'=>$finalDate]);
			$logcollection->addFieldToSelect("ip");
			$logcollection->getSelect()->group('ip');		   	
			$countlog = $logcollection->getSize();
			
			if($countlog >= 4)
			{
				$subquery1 = new \Zend_Db_Expr("Select 1 from subscription_history as sub_history where customer_id=".$customer_id." order by subscription_history_id desc limit 1");

				$subquery = new \Zend_Db_Expr("Select from_date from subscription_history as sub_history where customer_id=".$customer_id." order by subscription_history_id desc limit 1");
				$downloadCollection = $this->_productdownloadhistorylogFactory->create()->getCollection();
				$downloadCollection->addFieldToSelect(array('customer_id','download_date','download_items'=>'count(*)'));
				$downloadCollection->addFieldToFilter('customer_id',["eq"=>$customer_id]);				
				$downloadCollection->getSelect()->where("EXISTS ($subquery1)");
				$downloadCollection->getSelect()->where('download_date >= ('.$subquery.')');
				$downloadCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS)
				->columns([new \Zend_Db_Expr('COUNT(`main_table`.`log_id`) as download_items')]);
				
				foreach($downloadCollection->getData() as $downloadData)
				{
					if(array_key_exists("download_items",$downloadData))
					{
						$downloadCount = $downloadData['download_items'];
					}
					else
					{
						$downloadCount = 0;	
					}
				}
				
				$ipsModel = $ipcountmodel->load($ipcollection[0]['customerloginipcount_id']);
				$ipsModel->setCustomerId($customer_id)->setTimestamp($currentTime)->setDownloadCount($downloadCount)->save();
				//$this->sendTransEmail($this->_customerSession,$logcollection,$downloadCount,$downloadproducts);
			}
		}
		else
		{
		 	$pastdate = date('Y-m-d',strtotime("-7 days"));
			
			$logcollection = $logmodel->getCollection()->addFieldToFilter('customer_id',['eq' => $customer_id]);			
			$logcollection->addFieldToFilter("timestamp",array('gteq'=>$pastdate));
			$logcollection->addFieldToSelect("ip");
			$logcollection->getSelect()->group('ip');
			
			$count = $logcollection->getSize();
			if($count >= 4)
			{
				$subquery1 = new \Zend_Db_Expr("Select 1 from subscription_history as sub_history where customer_id=".$customer_id." order by subscription_history_id desc limit 1");

				$subquery = new \Zend_Db_Expr("Select from_date from subscription_history as sub_history where customer_id=".$customer_id." order by subscription_history_id desc limit 1");
				$downloadCollection = $this->_productdownloadhistorylogFactory->create()->getCollection();
				$downloadCollection->addFieldToSelect(array('customer_id','download_date','download_items'=>'count(*)'));
				$downloadCollection->addFieldToFilter('customer_id',["eq"=>$customer_id]);
				$downloadCollection->getSelect()->where("EXISTS ($subquery1)");
				$downloadCollection->getSelect()->where('download_date >= ('.$subquery.')');
				$downloadCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS)
				->columns([new \Zend_Db_Expr('COUNT(`main_table`.`log_id`) as download_items')]);
				
				foreach($downloadCollection->getData() as $downloadData)
				{
					if(array_key_exists("download_items",$downloadData))
					{
						$downloadCount = $downloadData['download_items'];	
					}
					else
					{
						$downloadCount = 0;	
					}	
				}

				$ipsModel = $ipcountmodel;
				$ipsModel->setCustomerId($customer_id)->setTimestamp($currentTime)->setDownloadCount($downloadCount)->save();
				//$this->sendTransEmail($this->_customerSession,$logcollection,$downloadCount,$downloadproducts);
			}
		}		
        return $this;
    }
	
	public function sendTransEmail($customer,$customerloginlog,$downloadCount,$downloadproducts)
	{		
		$emailSend = $this->_scopeConfig->getValue('contact/customemail/enable_mail_ips', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if($emailSend)
		{
		 	$receiverInfo = explode(',',$this->_scopeConfig->getValue("contact/customemail/recipient_email_to_ips",\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
			$cc = explode(',',$this->_scopeConfig->getValue("contact/customemail/recipient_email_cc_ips",\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
			$bcc = explode(',',$this->_scopeConfig->getValue("contact/customemail/recipient_email_bcc_ips",\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
			
			$fullname = $customer->getCustomer()->getName();
			$ipArray = array();
			$iplocation = array();
			$accesskey = $this->_scopeConfig->getValue('button/cus_download/accesskey',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
			foreach($customerloginlog as $ips)
			{
				$ipArray[] = $ips->getIp();
			}
			$ipStr = implode(',',$ipArray);
			$ipexplode = explode(",",$ipStr);
			foreach($ipexplode as $ip)
			{
				$url = "http://api.ipstack.com/".$ip."?access_key=".$accesskey."&format=1";
                $locationjson = json_decode(file_get_contents($url));

                $city = $locationjson->city;
                
                if(!empty($city))
                {
                	$iplocation[] =  $city;
                }
			} 

			$location = implode(",",$iplocation);
			
			$senderInfo = [
			    'name' => 'Slideteam',
			    'email' => "support@slideteam.net",
			];

			$emailTemplateVariables = array('CustomerId'=>$customer->getCustomer()->getId(),'CustomerName'=>$fullname,'CustomerEmail'=>$customer->getCustomer()->getEmail(),'CustomerIps'=>$ipStr,"DownloadItems"=>$downloadCount,"downloadProducts"=>$downloadproducts,"iplocation"=>$location);
			$this->_ipemail->CustomMailSend($emailTemplateVariables,$senderInfo,$receiverInfo,$cc,$bcc);			
		}		
	}
}