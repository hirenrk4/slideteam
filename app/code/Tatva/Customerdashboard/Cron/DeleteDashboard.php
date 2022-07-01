<?php
namespace Tatva\Customerdashboard\Cron;

class DeleteDashboard
{
    protected $_storeManager;
    protected $_transportBuilder;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceData,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Tatva\Customerdashboard\Model\CustomerdashboardFactory $customerDashboardFactory
    ) {
        $this->_resource = $resourceData;
        $this->_storeManager = $_storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->_customerDashboardFactory = $customerDashboardFactory;
    }

    public function execute()
    {
        $date = $this->_dateFactory->create()->gmtDate('Y-m-d H:i:s');
        $currentDate = $this->converToTz($date,'America/Los_Angeles','GMT');
        
        $collection = $this->_resource->getConnection()->fetchAll("SELECT * FROM `tatva_customer_dashboard` WHERE created_time <= DATE_SUB('".$currentDate."',INTERVAL 3 HOUR)");
        if(count($collection) > 0)
        {
            foreach ($collection as $value) {
                try {
                    $model = $this->_customerDashboardFactory->create();
                    $model->load($value['id']);
                    $model->delete();
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }

        }
        
    }
    protected function converToTz($dateTime="", $toTz='', $fromTz='')
    {
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone($toTz));
        $dateTime = $date->format('Y-m-d h:i:s');
        return $dateTime;
    }
}