<?php
namespace Tatva\DownloadableImport\Cron;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Import
{
    protected $_scopeconfig;
    protected $_productCollectionFactory;
    protected $_notification;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Tatva\Notification\Model\Notification $notification,
        TypeListInterface $cacheTypeList, 
        Pool $cacheFrontendPool,
        DateTime $gmtDate,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->_scopeconfig = $scopeConfig;
        $this->_productCollectionFactory = $productCollectionFactory; 
        $this->_configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->_notification = $notification;
        $this->currentGMTDate = $gmtDate;
        $this->_urlInterface = $urlInterface;
    }

    public function execute()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $lastimportproductid = $this->_scopeconfig->getValue("notification/general/productid", $storeScope);

        $collection = $this->_productCollectionFactory->create();
        $collection->addFieldToFilter("entity_id",array("gt"=>$lastimportproductid));
        $collection->addAttributeToSort("entity_id", "desc");
        
        $size = $collection->getSize();

        if($size > 0) {
            
            $collection->setPageSize(1);
            $entityid = $lastimportproductid;
            foreach($collection as $product)
            {
                $entityid = $product->getId();
            }
            
            $this->_configWriter->save('notification/general/productid', $entityid);

            $publishdate = $this->currentGMTDate->gmtDate('Y-m-d H:i:s');

            $numarray = array(9,11,12,13,14,15,16,17,18,19);
            shuffle($numarray);
            $slides = $size*$numarray[0];
           
            $content = "SlideTeam added ".$size." new products (e.g. Completely Researched Decks, Documents, Slide Bundles, etc), which included ".$slides." slides in total in the past 24 hours. Please <a href='https://www.slideteam.net/new-powerpoint-templates/'>click here</a> to view them.";
            
            $this->_notification->setContent($content)->setPublisheAt($publishdate)->setStatus(1)->setType(0)->save();

            $_types = [
                'config'
                ];
     
            foreach ($_types as $type) {
                $this->cacheTypeList->cleanType($type);
            }
            foreach ($this->cacheFrontendPool as $cacheFrontend) {
                $cacheFrontend->getBackend()->clean();
            }
        }
    }
}