<?php
namespace Tatva\Catalog\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

class Productdelete
{
    protected $connection;
    protected $_resource;
    protected $_dateFactory;   
    protected $directoryList;


    public function __construct(       
        \Magento\Framework\App\ResourceConnection $resourceData,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,        
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        DirectoryList $dirlist
    ) {     
        $this->_resource = $resourceData;  
        $this->_dateFactory = $dateTimeDateTimeFactory;        
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->_scopeConfig = $scopeConfig;
        $this->directoryList = $dirlist;
    }

    public function execute()
    {
        $varDirPath = $this->directoryList->getPath("var");
        $date = $this->_dateFactory->create()->gmtDate("Y-m-d H:i:s");     
        $strtime = strtotime("-1 day",strtotime($date));
        $previousTime = date("Y-m-d H:i:s",$strtime);
        $filedate = date("Y-m-d",$strtime);

        $connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('catalog_product_entity_deleted_log');

        $sql = "Select entity_id,sku,url_key FROM " . $tableName." where created_at >= '".$previousTime."' and created_at <= '".$date."'";        

        $deleteresult = $connection->fetchAll($sql);

        $filepath = 'productsdelete/productsdelete_' . $filedate . '.csv';
        $file = 'productsdelete_' . $filedate . '.csv';
        $this->directory->create('productsdelete');
        $stream = $this->directory->openFile($filepath, 'w+');
        
        $header = [
            'entity_id',
            'sku',
            'url_key'
        ];

        $stream->writeCsv($header);
        $new = 0;
        foreach($deleteresult as $data)
        {
            $itemData = [];
            $itemData = [
                $data['entity_id'],
                $data['sku'],
                $data['url_key']    
            ];

            $stream->writeCsv($itemData);
            $new = 1;
        }

        if($new == 1)
        {
            $mail = new \Zend_Mail();
            $message = "Please find an attachment for the list of products deleted in last 24 hrs.";
            $mail->setFrom("support@slideteam.net",'SlideTeam Support');
            $mail->setSubject('Product Delete log');
            $mail->setBodyHtml($message);


            $at = new \Zend_Mime_Part(file_get_contents($varDirPath."/".$filepath,true));

            $at->type        = \Zend_Mime::TYPE_OCTETSTREAM;
            $at->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
            $at->encoding    = \Zend_Mime::ENCODING_BASE64;
            $at->filename    = $file;

            $mail->addAttachment($at);

            
            $to_email = explode(',',$this->_scopeConfig->getValue('button/deleteproduct/to_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $cc_email = explode(',',$this->_scopeConfig->getValue('button/deleteproduct/cc_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));

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
            //$date = $this->date->gmtDate();
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