<?php
namespace Tatva\Deleteaccount\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

class Feedback 
{
	protected $_logger;
	protected $deletedcustomerbkpFactory;
	protected $_dateFactory;
	protected $directoryList;

	public function __construct
	(
		\Tatva\Deleteaccount\Model\DeletedcustomerbkpFactory $deletedcustomerbkpFactory,
		\Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,        
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        DirectoryList $dirlist,
		\Psr\Log\LoggerInterface $logger 
	)
	{
		$this->_dateFactory = $dateTimeDateTimeFactory;
		$this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
		$this->_scopeConfig = $scopeConfig;
        $this->directoryList = $dirlist;
		$this->deletedcustomerbkpFactory = $deletedcustomerbkpFactory;
		$this->_logger = $logger; 
	}

 	public function execute()
 	{
 		$varDirPath = $this->directoryList->getPath("var");
        $date = $this->_dateFactory->create()->gmtDate("Y-m-d H:i:s");     
        $strtime = strtotime($date);
        $startdate = date("Y-m-d H:i:s",strtotime("-1 month",$strtime));
        //$enddate = date("Y-m-d",$date);        
        $filedate = date("Y-m-d",$strtime);

		$collection1 = $this->deletedcustomerbkpFactory->create()->getCollection()->addFieldToFilter('feedback', array('like' => '%I just wanted to download free products.%'))->addFieldToFilter('deleted_date',array('lt'=>$date))->addFieldToFilter('deleted_date',array('gt'=>$startdate));
			$feedbackop1 = sizeof($collection1);
		$collection2 = $this->deletedcustomerbkpFactory->create()->getCollection()->addFieldToFilter('feedback', array('like' => '%I need more industry specific design.%'))->addFieldToFilter('deleted_date',array('lt'=>$date))->addFieldToFilter('deleted_date',array('gt'=>$startdate));
			$feedbackop2 = sizeof($collection2);
		$collection3 = $this->deletedcustomerbkpFactory->create()->getCollection()->addFieldToFilter('feedback', array('like' => "%You don't have the designs I am looking for.%"))->addFieldToFilter('deleted_date',array('lt'=>$date))->addFieldToFilter('deleted_date',array('gt'=>$startdate));
			$feedbackop3 = sizeof($collection3);
		$collection4 = $this->deletedcustomerbkpFactory->create()->getCollection()->addFieldToFilter('feedback', array('like' => '%Price is too high.%'))->addFieldToFilter('deleted_date',array('lt'=>$date))->addFieldToFilter('deleted_date',array('gt'=>$startdate));
			$feedbackop4 = sizeof($collection4);
		$collection5 = $this->deletedcustomerbkpFactory->create()->getCollection()->addFieldToFilter('feedback', array('like' => '%Others%'))->addFieldToFilter('deleted_date',array('lt'=>$date))->addFieldToFilter('deleted_date',array('gt'=>$startdate));
			$feedbackop5 = sizeof($collection5);

        $filepath = 'Deleteaccountfeedback/feedback_' . $filedate . '.csv';
        $file = 'feedback_' . $filedate . '.csv';
        $this->directory->create('Deleteaccountfeedback');
        $stream = $this->directory->openFile($filepath, 'w+');
        
        $header = [
            'Account delete reson',
            'Count',
        ];

        $header1 = [
        	'I need more industry specific design.',
        ];

        $header2 = [
        	'Others',
        ];

        $stream->writeCsv($header);

        $feedback[] = array('I just wanted to download free products.',$feedbackop1);
        $feedback[] = array('I need more industry specific design.',$feedbackop2);
        $feedback[] = array("You don't have the designs I am looking for.",$feedbackop3);
        $feedback[] = array('Price is too high.',$feedbackop4);
        $feedback[] = array('Others',$feedbackop5);
        //$new = 0;
        foreach($feedback as $item)
        {            
            $itemData = [];
            $itemData[] = $item[0];
           	$itemData[] = $item[1];

            $stream->writeCsv($itemData);
            //$new = 1;
        }

        //$stream = $this->directory->openFile($filepath, 'a');
        $stream->writeCsv($header1);

        foreach ($collection2->getData() as $data) {
                $comment = $data['feedback'];
                $text = explode('-', $comment);
                $email = $data['email_id'];

                if(!empty($text[1])){
	                $itemData = [];
	                $itemData[] = $text[1];
	           		$itemData[] = $email;

	            	$stream->writeCsv($itemData);
            	}
        }

        $stream->writeCsv($header2);

        foreach ($collection5->getData() as $data) {
                $comment = $data['feedback'];
                $textothers = explode('-', $comment);
                $email = $data['email_id'];

                if(!empty($textothers[1])){
	                $itemData = [];
	                $itemData[] = $textothers[1];
	           		$itemData[] = $email;

	            	$stream->writeCsv($itemData);
	           	}
        }

        $mail = new \Zend_Mail();
            $message = "Please find an attachment for the account delete feedback reasons.";
            //$message .= "Brochures Downloaded Start Date :: ".$startdate;
            //$message .= "<br/>Brochures Downloaded End Date :: ".$enddate;
            $mail->setFrom("support@slideteam.net",'Slideteam Support');
            $mail->setSubject('Monthly account deletion report for SlideTeam');
            $mail->setBodyHtml($message);

            $at = new \Zend_Mime_Part(file_get_contents($varDirPath."/".$filepath,true));

            $at->type        = \Zend_Mime::TYPE_OCTETSTREAM;
            $at->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
            $at->encoding    = \Zend_Mime::ENCODING_BASE64;
            $at->filename    = $file;

            $mail->addAttachment($at);

            
            $to_email = explode(',',$this->_scopeConfig->getValue('button/feedback/to_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $cc_email = explode(',',$this->_scopeConfig->getValue('button/feedback/cc_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));

            
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

		$this->_logger->info('feedback_Cron has been run successfully-'.$feedbackop1.$feedbackop2.$feedbackop3.$feedbackop4.$feedbackop5);
       	return $this;
	}   
}