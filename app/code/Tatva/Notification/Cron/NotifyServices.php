<?php
namespace Tatva\Notification\Cron;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Stdlib\DateTime\DateTime;

class NotifyServices
{
    protected $_notification;


    public function __construct(
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Tatva\Notification\Model\NotificationFactory $notification,
        TypeListInterface $cacheTypeList, 
        Pool $cacheFrontendPool,
        DateTime $gmtDate
    ) {
        $this->_configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->_notification = $notification;
        $this->currentGMTDate = $gmtDate;
    }

    public function scheduleTemplateFinder()
    {
        $content = "Simplify your search process with SlideTeam's Template Finder. To find the right template in seconds, click <a href='https://www.slideteam.net/business_powerpoint_diagrams/'> here</a>.";
        $this->createNotification($content);
    }

    public function schedulePopularProduct()
    {
        $content = "See what the world is downloading for a kickass presentation. Check out our popular products <a href='https://www.slideteam.net/professional-powerpoint-templates'> here</a> and get inspired.";
        $this->createNotification($content);
    }

    public function scheduleDesignService()
    {
        $content = "We have an entire team of experts who can work on your custom presentation. Get in touch with our design agency <a href='https://www.slideteam.net/powerpoint_presentation_design_services/'> here</a>.";
        $this->createNotification($content);
    }

    public function scheduleResearchService() 
    {
        $content = "SlideTeam can give you valuable insights into your industry, products/services, competitors, and customers. Avail our Business Research Services <a href='https://www.slideteam.net/powerpoint_presentation_design_services/business_research_services'> here</a>.";
        $this->createNotification($content);
    }

    public function scheduleEbook()
    {
        $content = "Get in touch with your inner creative with our downloadable resources. Access our PowerPoint Ebooks <a href='https://www.slideteam.net/powerpoint-ebooks-for-slide-template-design/'> here</a> and become a brilliant presentation designer.";
        $this->createNotification($content);
    }

    public function createNotification($content)
    {
        $publishdate = $this->currentGMTDate->gmtDate('Y-m-d H:i:s');
        $this->_notification->create()->setContent($content)->setPublisheAt($publishdate)->setStatus(1)->setCustomerType(0)->setType(2)->save();

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