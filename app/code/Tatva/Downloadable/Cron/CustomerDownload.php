<?php
namespace Tatva\Downloadable\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

class CustomerDownload
{
    protected $_resourceCollection;

    protected $connection;

    protected $entityCollectionFactory;

    protected $productAction;

    protected $_dateFactory;
    protected $directoryList;


    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $resourceCollection,
        \Magento\Framework\App\ResourceConnection $resourceData,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Magento\Framework\Filesystem $filesystem,
        DirectoryList $dirlist,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->customerRepository = $customerRepository;
        $this->_resourceCollection = $resourceCollection;
        $this->_resource = $resourceData;
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->directoryList = $dirlist;
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $varDirPath = $this->directoryList->getPath("var");
        $date = $this->_dateFactory->create()->gmtDate("Y-m-d H:i:s");
        $strtime = strtotime($date);
        $date = date("d-m-Y",strtotime("-1 days",$strtime));
        $starttime = date("Y-m-d 00:00:00",strtotime("-1 days",$strtime));
        $endtime = date("Y-m-d 23:59:59",strtotime("-1 days",$strtime));

        $download_collection = $this->_resource->getConnection()->fetchAll("SELECT customer_id,group_concat(DISTINCT ip SEPARATOR ',') as ips,count(DISTINCT product_id) as download_count  from `productdownload_history_log` where download_date >= '".$starttime."' and download_date <= '".$endtime."' group by `customer_id` having download_count >= 100");

        $filepath = 'customer_downloaded_list.csv';
        $file = 'customer_downloaded_list.csv';
        //$this->directory->create('resumedownload');
        $stream = $this->directory->openFile($filepath, 'w+');

        $header = [
            'CustomerId',
            'Customer Email',
            'Total Download Count',
            'IP List',
            'City',
            'Country'
        ];

        $stream->writeCsv($header);
        $new = 0;
        
        foreach ($download_collection as $key => $downloadedData) {

            $itemData = [];
            $iplist = explode(",",$downloadedData["ips"]);
            $uniqueips = array_unique($iplist);
            $cities = array();
            $countries = array();

            foreach($uniqueips as $ip)
            {
                $accesskey = $this->_scopeConfig->getValue('button/cus_download/accesskey',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                $url = "http://api.ipstack.com/".$ip."?access_key=".$accesskey."&format=1";
                $location = json_decode(file_get_contents($url));    
                
                $city = $location->city;
                $country = $location->country_name;
                
                if(!empty($city))
                {
                    $cities[] = $city;   
                }

                if(!empty($country))
                {
                    $countries[] = $country;
                }
            }

            $distinctips = implode(",",$uniqueips);

            $customer_id = $downloadedData['customer_id'];
            $customer = $this->customerRepository->getById($customer_id);
            $customer_email = $customer->getEmail();

            $itemData = [
                $downloadedData['customer_id'],
                $customer_email,
                $downloadedData['download_count'],
                implode(",",$uniqueips),
                implode(",",$cities),
                implode(",",$countries)
            ];

            $stream->writeCsv($itemData);
            $new = 1;
        }

        if($new == 1)
        {
            $mail = new \Zend_Mail();
            $message = "Please find an attachment for list of customers downloaded more than 100 products on ".$date.".<br/>";
            //$meddage .= "Report Date :: ".$date;
            $mail->setFrom("support@slideteam.net",'SlideTeam Support');
            $mail->setSubject('List of customers who downloaded more than 100 products in a day');
            $mail->setBodyHtml($message);

            $at = new \Zend_Mime_Part(file_get_contents($varDirPath."/".$filepath,true));

            $at->type        = \Zend_Mime::TYPE_OCTETSTREAM;
            $at->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
            $at->encoding    = \Zend_Mime::ENCODING_BASE64;
            $at->filename    = $file;

            $mail->addAttachment($at);

            $to_email = explode(',',$this->_scopeConfig->getValue('button/cus_download/to_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $cc_email = explode(',',$this->_scopeConfig->getValue('button/cus_download/cc_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));

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

            try
            {
                if($send) :
                    $mail->send();
                endif;
            }catch(Exception $e)
            {
                echo $e->getMessage();
            }
        }
    }
}