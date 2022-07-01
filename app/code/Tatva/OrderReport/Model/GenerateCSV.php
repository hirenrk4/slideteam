<?php

/**
 * Grid Grid Model.
 * @category  Webkul
 * @package   Webkul_Grid
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Tatva\OrderReport\Model;
use Magento\Framework\App\Filesystem\DirectoryList;


class GenerateCSV extends \Magento\Framework\Model\AbstractModel
{
    
    public function __construct(
        \Tatva\OrderReport\Model\OrderReport $orderReport,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order $orderModel,
        DirectoryList $dirlist,
        array $data = []
    )
    {
        $this->OrderReport = $orderReport;
        $this->orderModel = $orderModel;
        $this->_fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->directoryList = $dirlist;
    }
    
    public function CsvGenerate($post)
    {
        $postfromdate = $post['start_date']." 00:00:00";
        $postenddate = $post['end_date']." 23:59:59";
        
        
        $from_date = date("Y-m-d H:i:s",strtotime("+7 hour",strtotime($postfromdate)));
        $to_date = date("Y-m-d H:i:s",strtotime("+7 hour",strtotime($postenddate)));
        
        if($post['report_type'] == "new")
        {
            $collection = $this->orderModel->getCollection();       
            $collection->addFieldToSelect(array("base_grand_total","created_at","increment_id"));
            $collection->addFieldToFilter("created_at",array("gteq"=>$from_date));
            $collection->addFieldToFilter("created_at",array("lteq"=>$to_date));
            $collection->addFieldToFilter("state",array("eq"=>"complete"));
            $collection->getSelect()->joinLeft(
                ['payment' => "sales_order_payment"],
                'payment.parent_id = main_table.entity_id',
                ['payment_method'=>"payment.method"]
            );
        }
        else
        {
            $collection = $this->OrderReport->getCollection();
            $collection->addFieldToFilter("subscription_start_date",array("gteq"=>$from_date));
            $collection->addFieldToFilter("subscription_start_date",array("lteq"=>$to_date));
            $collection->getSelect()->joinLeft(
                ['order_tbl' => "sales_order"],
                'order_tbl.increment_id = main_table.increment_id',
                ['order_id'=>"order_tbl.entity_id","created_at","base_grand_total"]
            );
            $collection->getSelect()->joinLeft(
                ['payment' => "sales_order_payment"],
                'payment.parent_id = order_tbl.entity_id',
                ['payment_method'=>"payment.method"]
            );
            $collection->getSelect()->where("created_at < '".$from_date."'");
        }
        
        $folderPath = "OrderReport";
        $time = time();
        $name = 'OrderReport_'.$time.'.csv';
        $filepath = 'OrderReport/'.$name;
        $this->directory->create('OrderReport');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();
        $columns = $this->getColumnHeader();
        foreach ($columns as $column) {
            $header[] = $column;
        }
        $stream->writeCsv($header);
        $id = 1;

        /* Write Header */
        
        foreach($collection as $row)
        {

            if($post['report_type'] == "new")
            {
                
                if($row->getPaymentMethod() == "cashondelivery")
                {
                    $Hostname = "invoice-new-stripe";
                    $transaction_id = $row->getEntityId();
                }
                else
                {
                    $Hostname = "www.slideteam.net";  
                    $transaction_id = $row->getIncrementId();  
                }
            }
            else
            {
                
                if($row->getPaymentMethod() == "cashondelivery")
                {
                    $Hostname = "invoice-recurring-stripe";
                }
                else
                {
                    $Hostname = "offline";
                }

                $transaction_id = $row->getOrderId();

                
            }

            $itemData = [];
            $itemData[] = $id;
            $itemData[] = $transaction_id;
            $itemData[] = $Hostname;
            $itemData[] = $row->getBaseGrandTotal();
            $itemData[] = $this->getPaymentMethodName($row->getPaymentMethod());
            $stream->writeCsv($itemData);

            $id++;
        }

        $varDirPath = $this->directoryList->getPath("var");
        //$meddage .= "Report Date :: ".$date;
        $mail = new \Zend_Mail();
        
        if($post['report_type'] == "new")
        {
            $message = "Please find an attachment for new orders data";
            $mail->setSubject('New Orders Csv');        
        }
        else
        {
            $message = "Please find an attachment for recurring orders data";
            $mail->setSubject('Recurring Orders Csv');  
        }
        
        $mail->setFrom("support@slideteam.net",'SlideTeam Support');
        $mail->setBodyHtml($message);
        $at = new \Zend_Mime_Part(file_get_contents($varDirPath."/".$filepath,true));

        $at->type        = \Zend_Mime::TYPE_OCTETSTREAM;
        $at->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $at->encoding    = \Zend_Mime::ENCODING_BASE64;
        $at->filename    = $name;

        $mail->addAttachment($at);
        $to_email = explode(',',$post['email']);
        $send = 0;

        if(!empty($to_email))
        {
            $mail->addTo($to_email);
            $send = 1;
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

    /* Header Columns */
    public function getColumnHeader() {
        $headers = ['Id','Transaction Id',"HostName","Amount","Payment Method"];
        return $headers;
    }

    public function getPaymentMethodName($methodName){
        if($methodName == "amasty_stripe"){
            return "Stripe";
        } 
        elseif($methodName == "cashondelivery") {
            return "Stripe";
        }
        elseif($methodName == "paypal_express") {
            return "Paypal";
        }
        elseif($methodName == "paypal_standard") {
            return "Paypal-o";
        }
        elseif($methodName == "tco_checkout") {
            return "tco_checkout";
        }
        elseif($methodName == "tco") {
            return "tco_checkout";
        } else {
            return "";
        }
    }
}