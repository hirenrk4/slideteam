<?php
namespace Tatva\Notification\Cron;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Blog
{
    protected $_scopeconfig;
    protected $_blogCollectionFactory;
    protected $_notification;


    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $blogCollectionFactory,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Tatva\Notification\Model\NotificationFactory $notification,
        TypeListInterface $cacheTypeList, 
        Pool $cacheFrontendPool,
        DateTime $gmtDate,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->_scopeconfig = $scopeConfig;
        $this->_blogCollectionFactory = $blogCollectionFactory; 
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
        $lastpublishblogid = $this->_scopeconfig->getValue("notification/general/blogid", $storeScope);

        $collection = $this->_blogCollectionFactory->create();
        $collection->addFieldToFilter('ID',array('gt'=>$lastpublishblogid));
        $collection->addFieldToFilter('post_status','publish');
        $collection->getSelect()->order('ID ASC');

        $size = $collection->getSize();


        if($size > 0) {
            $lastBlogId = '';
            foreach($collection as $blog)
            {
                $blogID = $blog->getID();
                $lastBlogId = $blogID;
                $title = $blog->getPostTitle();
                $url = $blog->getPermalink();
                $content = 'SlideTeam has published a new blog titled "'.$title.'". You can view it <a href="'.$url.'">here</a>.';


                $publishdate = $this->currentGMTDate->gmtDate('Y-m-d H:i:s');

                $this->_notification->create()->setContent($content)->setPublisheAt($publishdate)->setStatus(1)->setCustomerType(0)->setType(1)->save();
            }
            $this->_configWriter->save('notification/general/blogid',$lastBlogId);
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