<?php
namespace Tatva\Downloadreport\Model;
use Magento\Framework\Model\AbstractModel;


class Downloadreport extends AbstractModel
{

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\PhpEnvironment\Request $request,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Tatva\Catalog\Model\ResourceModel\Productdownloadhistorylog\CollectionFactory $productDownloadHistoryLogFactory,
        \Tatva\Subscription\Model\ResourceModel\Subscription\CollectionFactory $subscriptionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
        ) {
        $this->resourceConnection = $resourceConnection;
        $this->_scopeConfig = $scopeConfig;
        $this->_request = $request;
        $this->productDownloadHistoryLogFactory = $productDownloadHistoryLogFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->date = $date;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
            );
    }

    public function getProductdownloadcount()
    {
        $rootdir = explode('app', dirname(__FILE__))[0] . 'pub/';
        $file = 'download_report.csv';

        $file_path = $rootdir . "Scripts" . DIRECTORY_SEPARATOR . $file;
 
        $configValue = $this->_scopeConfig->getValue('downloadreport/config/text',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);  
        $accesskey = $this->_scopeConfig->getValue('button/cus_download/accesskey',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($configValue=='')
        {
            $configValue = 1;
        }
        $collection=$this->productDownloadHistoryLogFactory->create();
      
        
        $collection->addFieldToSelect('customer_id');

        $collection->getSelect()
                    ->columns('COUNT(log_id) AS download_count')
                    ->group('customer_id')
                    ->having('COUNT(log_id) >=' .$configValue);

        $customer_array = array();
        $data = $collection->getData();
        foreach($data as $val)
        {
            $customer_array[] =$val['customer_id'];
        }
        $filter_customers= 0;

        if(!empty($customer_array)){
         $filter_customers = implode(",",$customer_array);   
        }
        
        $read = $this->resourceConnection->getConnection();
        $sql = "SELECT DISTINCT (m1.customer_id) FROM subscription_history m1 LEFT JOIN subscription_history m2 ON (m1.customer_id = m2.customer_id AND m1.subscription_history_id < m2.subscription_history_id) WHERE m2.subscription_history_id IS NULL AND m1.to_date<CURDATE() AND m1.customer_id IN ($filter_customers)";

        $result = $read->fetchCol($sql);
       
        
        $collection=$this->productDownloadHistoryLogFactory->create();
        $collection->addFieldToFilter("customer_id",array('nin'=>$result));
        $collection->getSelect()->join('customer_entity','main_table.customer_id = customer_entity.entity_id', array('customer_email' => 'email','firstname'=>'firstname', 'lastname'=>'lastname'));                            
      
        
        $collection->addFieldToSelect('customer_id');
        $collection->getSelect()
                    ->columns(array('COUNT(log_id) AS download_count','GROUP_CONCAT(ip) as gorup_ip'))
                    ->group('customer_id')
                    ->having('COUNT(log_id) >=' .$configValue);
        
        $fp = fopen($file_path, 'w');
        $csvHeader = array("Customer ID","Customer Name","Customer Email","Total Download Count","Download Count of Last OneWeek","Download Count in Current Subscription","Current Subscription Status","Ip Address","Ip Location");
        fputcsv( $fp, $csvHeader,",");
           
        foreach ($collection as $product){
        $cust_id = $product['customer_id'];
        $cust_name = $product['firstname']." ".$product['lastname'];
        $cust_email = $product['customer_email'];
        $download_count = $product['download_count'];
        $download_count_current_subscription = $this->getCurrentDownloadCount($product['customer_id']);
        $download_count_last_week = $this->getLastweekDownloadCount($product['customer_id']);
        $current_subscription_status = $this->getCurrentSubscriptionStatus($product['customer_id']);
        $downloadedips = array_unique(explode(',',$product['gorup_ip']));
        $cities = array();
        // foreach($downloadedips as $ip) 
        // {
            // $url = "http://api.ipstack.com/".$ip."?access_key=".$accesskey."&format=1";
            // $location = json_decode(file_get_contents($url));    
                
            // $city = $location->city;
            // if(!empty($city))
            // {
            //     $cities[] = $city;
            // }            
        // }
                
        fputcsv($fp, array($cust_id,$cust_name,$cust_email,$download_count,$download_count_last_week,$download_count_current_subscription,$current_subscription_status,implode(",", $downloadedips),implode(",", $cities)), ",");
        }
        fclose($fp);

        //$to = array('uminfy@yahoo.com','ron@slideteam.net','geetika.gosain@slideteam.net','victor@slideteam.net','ash@slideteam.net');
        $message = "Please find an attachment for the list of customers whose minimum downloads are $configValue";
        $mail = new \Zend_Mail();
        $mail->setFrom("support@slideteam.net",'SlideTeam Support');
        $mail->setSubject('Slideteam customers report');
        $mail->setBodyHtml($message);

        $at = new \Zend_Mime_Part(file_get_contents($file_path,true));
        //file_get_contents doesn't work if we run this file in cron


        $at->type        = \Zend_Mime::TYPE_OCTETSTREAM;
        $at->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $at->encoding    = \Zend_Mime::ENCODING_BASE64;
        $at->filename    = $file;

        $mail->addAttachment($at);

        $email_to = explode(',',$this->_scopeConfig->getValue('downloadreport/email_config/to_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
        $email_cc = explode(',',$this->_scopeConfig->getValue('downloadreport/email_config/cc_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        $mail->addTo($email_to,'Admin');
        $mail->addCc($email_cc,'Slideteam Sales');
        /*$mail->addCc("krunal.vakharia@tatvasoft.com",'Krunal');
        $mail->addCc("sumit.kumar@slidetech.in",'Sumit');
        $mail->addCc("sales@slideteam.net",'Slideteam Sales');*/
        $date = $this->date->gmtDate();
        try
        {
            if ($mail->send())
            {                
                unlink($file_path);
            }
            echo "Mail sent Successfully";
        } catch (Exception $ex)
        {
           
        }
    }
    public function getCurrentSubscriptionStatus($customer_id)
    {
        $collection = $this->subscriptionFactory->create();
        $collection->addFieldToFilter('customer_id',$customer_id);
        $collection->getSelect()->order('subscription_history_id desc');
        $collection->getSelect()->limit(1);

        foreach ($collection as $subscription) {
            return $subscription->getData('status_success');
        }
    }

    public function getCurrentDownloadCount($customer_id)
    {
        $collection = $this->subscriptionFactory->create();
        $collection->addFieldToFilter('customer_id',$customer_id);
        $collection->getSelect()->order('subscription_history_id desc');
        $collection->getSelect()->limit(1);

        if($collection)
        {
            foreach ($collection as $subscription) {
                $fromDate = $subscription->getFromDate();
                $productCollection = $this->productDownloadHistoryLogFactory->create();
                $productCollection->addFieldToFilter('customer_id',$customer_id);
                $productCollection->addFieldToFilter('download_date',array('gteq'=>$fromDate));
                $productCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
                $productCollection->getSelect()->columns('COUNT(DISTINCT(product_id)) as download_count');
                $collectionData = $productCollection->getData();
                $productsDownloaded = $collectionData[0]['download_count'];
                return $productsDownloaded;

            }   
        }
    }

    public function getLastweekDownloadCount($customer_id)
    {
        
        $currentdate = $this->date->gmtDate("Y-m-d H:i:s");
        $todate = $this->date->gmtDate("Y-m-d H:i:s", strtotime('-1 week', strtotime($currentdate)));
        
        $collection=$this->productDownloadHistoryLogFactory->create();
        $collection->addFieldToFilter("customer_id",$customer_id);
        $collection->addFieldToFilter('download_date',array('gteq'=>$todate));
        //$collection->addFieldToSelect('log_id');
        //$collection->getSelect()->columns('COUNT(log_id) AS download_count');
        $count = $collection->count();
        return $count;
    }
}