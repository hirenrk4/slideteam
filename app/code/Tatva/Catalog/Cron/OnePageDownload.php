<?php
namespace Tatva\Catalog\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

class OnePageDownload
{
    protected $connection;
    protected $_resource;
    protected $_dateFactory;   
    protected $directoryList;
    protected $_productCollectionFactory;
    protected $_categoryFactory;
    protected $_productdownloadhistorylogFactory;


    public function __construct(       
        \Magento\Framework\App\ResourceConnection $resourceData,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,        
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        DirectoryList $dirlist,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Tatva\Catalog\Model\ProductdownloadhistorylogFactory $productdownloadhistorylogFactory
    ) {     
        $this->_resource = $resourceData;  
        $this->_dateFactory = $dateTimeDateTimeFactory;        
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->_scopeConfig = $scopeConfig;
        $this->directoryList = $dirlist;
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productdownloadhistorylogFactory = $productdownloadhistorylogFactory;
    }

    public function execute()
    {
        $varDirPath = $this->directoryList->getPath("var");
        $date = $this->_dateFactory->create()->gmtDate("Y-m-d H:i:s");
        $strtime = strtotime($date);
        $startdate = date("Y-m-d",strtotime("-7 days",$strtime));
        $enddate = date("Y-m-d",strtotime("-1 days",$strtime));
        $filedate = date("Y-m-d",$strtime);

        $onepagecategoryid = explode(',',$this->_scopeConfig->getValue('resume/general/onepagecategoryName',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));

        //$categoryId = 'yourcategoryid';
        $category = $this->_categoryFactory->create()->load($onepagecategoryid);
        $productcollection = $this->_productCollectionFactory->create();
        $productcollection->addAttributeToSelect('*');
        $productcollection->addCategoryFilter($category);

        $productIds = array();
        foreach($productcollection as $product)
        {
            $productIds[] = $product->getId();
        }


        $collection = $this->_productdownloadhistorylogFactory->create()->getCollection();
        $collection->getSelect()->joinLeft(array('cpe'=>'catalog_product_entity'),'cpe.entity_id=main_table.product_id',array('cpe.entity_id','cpe.sku'));
        $collection->addFieldToFilter('download_date',array('lteq'=>$enddate));
        $collection->addFieldToFilter('download_date',array('gteq'=>$startdate));
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(array('cpe.entity_id','cpe.sku','COUNT(product_id) as download_count'));
        $collection->getSelect()->where('product_id in (?)',$productIds);
        $collection->getSelect()->group('product_id');        
        
        $filepath = 'specialproduct/onepagedownload_' . $filedate . '.csv';
        $file = 'onepagedownload_' . $filedate . '.csv';
        $this->directory->create('specialproduct');
        $stream = $this->directory->openFile($filepath, 'w+');
        
        $header = [
            'ProductId',
            'Product Sku',
            'Total Download Count'
        ];

        $stream->writeCsv($header);
        $new = 0;
        foreach($collection as $product)
        {            
            $itemData = [];
            $itemData = [
                $product->getEntityId(),
                $product->getSku(),
                $product->getDownloadCount()
            ];

            $stream->writeCsv($itemData);
            $new = 1;
        }

        if($new == 1)
        {
            $mail = new \Zend_Mail();
            $message = "Please find an attachment for the list of One-page documents downloaded.<br/>";
            $message .= "One-page documents Downloaded Start Date :: ".$startdate;
            $message .= "<br/>One-page documents Downloaded End Date :: ".$enddate;
            $mail->setFrom("support@slideteam.net",'SlideTeam Support');
            $mail->setSubject('One-page documents Download log');
            $mail->setBodyHtml($message);

            $at = new \Zend_Mime_Part(file_get_contents($varDirPath."/".$filepath,true));

            $at->type        = \Zend_Mime::TYPE_OCTETSTREAM;
            $at->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
            $at->encoding    = \Zend_Mime::ENCODING_BASE64;
            $at->filename    = $file;

            $mail->addAttachment($at);

            
            $to_email = explode(',',$this->_scopeConfig->getValue('resume/general/to_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $cc_email = explode(',',$this->_scopeConfig->getValue('resume/general/cc_email',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));

            
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