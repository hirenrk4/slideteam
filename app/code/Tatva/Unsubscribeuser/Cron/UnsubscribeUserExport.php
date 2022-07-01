<?php
namespace Tatva\Unsubscribeuser\Cron;

use Tatva\Unsubscribeuser\Model\UnsubscribeFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

class UnsubscribeUserExport
{
    protected $_scopeConfig;
    protected $_unsubscribeFactory;
    protected $date;
    protected $curl;
    protected $_customerRepositoryInterface;


    public function __construct(       
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        UnsubscribeFactory $UnsubscribeFactory,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Tatva\Subscription\Model\SubscriptionFactory $subscription,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\ResourceConnection $conn_res,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {     
        $this->_scopeConfig  = $scopeConfig; 
        $this->date = $date;
        $this->_unsubscribeFactory = $UnsubscribeFactory;
        $this->curl = $curl;
        $this->_conn_res = $conn_res;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_subscription = $subscription;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->directoryRead = $filesystem->getDirectoryRead(DirectoryList::VAR_DIR);
    }

    public function execute()
    {
        $unsubscribeCollection = $this->_unsubscribeFactory->create()->getCollection();
        $unsubscribeCollection->getSelect()->joinLeft(array("sub_history"=>"subscription_history"),"sub_history.subscription_history_id = main_table.subscription_id and main_table.customer_id = sub_history.customer_id",array("subscription_period","from_date","to_date","renew_date","paypal_id","two_checkout_message_id","stripe_checkout_message_id","increment_id","status_success"));
        $unsubscribeCollection->getSelect()->joinLeft(array("customer"=>"customer_entity"),"main_table.customer_id=customer.entity_id",array("firstname","lastname","email"))
        ->order(new \Zend_Db_Expr("FIELD(main_table.status,'Unsubscribed','Removed From Queue','pending') DESC"))
        ->order(array('sub_history.unsubscribe_order DESC'));

        $unsubscribeCollection->getSelect()->where("customer.entity_id IS NOT NULL");
        $unsubscribeCollection->getSelect()->where("sub_history.unsubscribe_order != 0");

        $time = time();
        $filepath = 'export/unsubscribe_list'.$time.'.csv';
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $header = ["Id","First Name","Last Name","Email","Payment Method","Period","Starting Date","Ending Date","Renewal Date","Unsubscribe Status","Reason For Unsubscribe","No Of Renews","Backend Comment"];
        $stream->writeCsv($header);

        //$conn_res = $objectManager->get('Magento\Framework\App\ResourceConnection');
        //$connection = $this->_conn_res->getConnection();

        $orgStartDate = $orgEndDate = $this->date->gmtDate();
        $other_connection = $this->_conn_res->getConnection();

        foreach($unsubscribeCollection as $unsubscribe)
        {
            if(!empty($unsubscribe->getTwoCheckoutMessageId()))
            {
                $paymentMethod = "2checkout";
            }
            elseif(!empty($unsubscribe->getStripeCheckoutMessageId()))
            {
                $paymentMethod = "Stripe";
            }
            elseif(!empty($unsubscribe->getPaypalId()))
            {
                $paymentMethod = "Paypal";
            }
            else
            {
                $paymentMethod = "No Payment Method Define";
            }
            $increment_id = $unsubscribe->getIncrementId();
            $customerid = $unsubscribe->getCustomerId();
            $period = $unsubscribe->getSubscriptionPeriod();

            $sql = "Select (select count(sub_history.subscription_history_id) from subscription_history as `sub_history` where sub_history.increment_id = '".$increment_id."' and sub_history.customer_id = '".$customerid."' and sub_history.from_date <= '".$orgStartDate."') as 'is_renew',
            (select sub_history.to_date from subscription_history as sub_history where sub_history.customer_id = '".$customerid."' and sub_history.increment_id = '".$increment_id."' ORDER BY sub_history.subscription_history_id DESC LIMIT 1) as current_to_date from subscription_history as sub_history where sub_history.customer_id = '".$customerid."' and sub_history.increment_id = '".$increment_id."' and sub_history.subscription_period = '".$period."'";

            $sub_result = $other_connection->fetchAll($sql); 

            $renews = $sub_result[0]['is_renew'] - 1;
            
            // if($renews >= 1) :
            //     if(strtotime($sub_result[0]['current_to_date']) < strtotime($orgEndDate) && strtotime($unsubscribe->getRenewDate()) >= strtotime($orgStartDate) && strtotime($unsubscribe->getRenewDate()) <= strtotime($orgEndDate) && $row['status_success'] == "Paid") :
            //         $renew_status = "Expired";
            //     elseif(strtotime($unsubscribe->getRenewDate()) >= strtotime($orgStartDate) && strtotime($unsubscribe->getRenewDate()) <= strtotime($orgEndDate) && ($unsubscribe->getStatusSuccess() == "Unsubscribed" || $unsubscribe->getStatusSuccess() == "Requested Unsubscription" || $unsubscribe->getStatusSuccess() == "Refunded" || $unsubscribe->getStatusSuccess() == "Cancelled")) :
            //         $renew_status = "Unsubscribed";
            //     else:
            //         $renew_status = "Renewal";
            //     endif;
            // else :
            //     $renew_status = "New";
            // endif;

            $data = [];
            $data[] = $unsubscribe->getSubscriptionId();
            $data[] = $unsubscribe->getFirstname();
            $data[] = $unsubscribe->getLastname();
            $data[] = $unsubscribe->getEmail();
            $data[] = $paymentMethod;
            $data[] = $unsubscribe->getSubscriptionPeriod();
            $data[] = $unsubscribe->getFromDate();
            $data[] = $unsubscribe->getToDate();
            $data[] = $unsubscribe->getRenewDate();
            $data[] = $unsubscribe->getStatus();
            $data[] = $unsubscribe->getReason();
            $data[] = $renews;
            //$data[] = $renew_status;
            $data[] = $unsubscribe->getBackendComment();
            $stream->writeCsv($data);
        }

        $file_dir = $this->directoryRead->getAbsolutePath() . 'export/';
        $file = 'unsubscribe_list'.$time.'.csv';

        $this->_inlineTranslation->suspend();

        $senderEmail = $this->_scopeConfig->getValue("trans_email/ident_sales/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);     
        $sender = [
            'name' => 'Slideteam',
            'email' => $senderEmail
        ];
         
        $sentToEmail = "harish.slideteam@gmail.com";
        $sentToName = "geetika";

        $transport = $this->_transportBuilder
        ->setTemplateIdentifier('unsubscribeuser_email_template_export')
        ->setTemplateOptions(
            [
                'area' => 'frontend',
                'store' => $this->storeManager->getStore()->getId()
            ])
        ->setTemplateVars([
                "name"=>"Admin"
            ])
        ->setFrom($sender)
        ->addTo($sentToEmail,$sentToName)
        ->addAttachment(file_get_contents($file_dir.$file),$file)
        ->getTransport();
             
        $transport->sendMessage();
    }
}