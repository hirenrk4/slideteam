<?php
namespace Tatva\Customer\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

class CustomerRegistration
{
    protected $_scopeConfig;
    protected $_customerCollectionFactory;
    protected $directoryList;
    protected $_dateFactory;

    public function __construct(       
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        DirectoryList $dirlist,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory
    ) {     
        $this->_scopeConfig  = $scopeConfig; 
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->directoryList = $dirlist;
        $this->_dateFactory = $dateTimeDateTimeFactory;
    }

    public function execute()
    {
        $varDirPath = $this->directoryList->getPath("var");
        $date = $this->_dateFactory->create()->gmtDate("Y-m-d H:i:s");
        $date1 = date("Y-m-d H:i:s",strtotime("-30 minutes",strtotime($date)));
        $date2 = date("Y-m-d H:i:s",strtotime($date));
        $startdate = $this->converToTz($date1,'America/Los_Angeles','GMT');
        $enddate = $this->converToTz($date2,'America/Los_Angeles','GMT');

        $customerCollection = $this->_customerCollectionFactory->create();
        $customerCollection->addAttributeToSelect('contact_number');
        $customerCollection->addAttributeToFilter('contact_number',array('neq'=>''));
        $customerCollection->getSelect()->where('created_at >= DATE_SUB(NOW(),INTERVAL 30 MINUTE)');

        if(!empty($customerCollection->getData()))
        {
            $filepath = 'newcustomer/customer_register_list.csv';
            $file = 'customer_register_list.csv';
            $stream = $this->directory->openFile($filepath, 'w+');

            $header = [
                'Customer Id',
                'Name',
                'Email',
                'Phone'
            ];

            $stream->writeCsv($header);
            $new = 0;

            foreach ($customerCollection->getData() as $customer) {
                $itemData = [];
                $name = $customer['firstname'].' '.$customer['lastname'];
                $itemData = [
                    $customer['entity_id'],
                    $name,
                    $customer['email'],
                    $customer['contact_number']
                ];
                $stream->writeCsv($itemData);
                $new = 1;
            }
            if($new == 1)
            {
                $mail = new \Zend_Mail();
                $message = "Please find an attachment for list of new customers registered with Phone number from Time ".$startdate." California Time to ".$enddate." California Time.<br/>";
                $mail->setFrom("support@slideteam.net",'SlideTeam Support');
                $mail->setSubject('New customers registered with Phone Number in the last 30 mins');
                $mail->setBodyHtml($message);

                $at = new \Zend_Mime_Part(file_get_contents($varDirPath."/".$filepath,true));

                $at->type        = \Zend_Mime::TYPE_OCTETSTREAM;
                $at->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
                $at->encoding    = \Zend_Mime::ENCODING_BASE64;
                $at->filename    = $file;

                $mail->addAttachment($at);

                $to_email = explode(',',$this->_scopeConfig->getValue('button/newcustomeradd/to_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $cc_email = explode(',',$this->_scopeConfig->getValue('button/newcustomeradd/cc_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));

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

    protected function converToTz($dateTime="", $toTz='', $fromTz='')
    {
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('Y-m-d h:i:s a');
        return $dateTime;
    }
}