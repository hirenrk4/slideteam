<?php

namespace Tatva\Notification\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Http\Context as AuthContext;
use Tatva\Notification\Model\NotificationFactory;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $current_date;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $currentGMTDate;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $authContext;

    /**     
     * @var \Tatva\Notification\Model\NotificationFactory
     */
    protected $notificationFactory;

    const XML_PATH_NOTIFICATION = 'notification/';

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        TimezoneInterface $date,
        DateTime $gmtDate,
        SessionFactory $session,
        NotificationFactory $notificationFactory,
        AuthContext $authContext
    ) {
        $this->storeManager  = $storeManager;
        $this->current_date =  $date;
        $this->currentGMTDate = $gmtDate;
        $this->customerSession = $session;
        $this->notificationFactory = $notificationFactory;
        $this->authContext = $authContext;
        parent::__construct($context);
    }
    /**
     * Get ConfigValue
     *
     * @param String $field   field
     * @param null   $storeId storeId
     *
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getEalierLimit()
    {
        return $this->getConfigValue(self::XML_PATH_NOTIFICATION .'general/ealier_limit');
    }

    public function getPopLimit()
    {       
        return $this->getConfigValue(self::XML_PATH_NOTIFICATION .'general/header_popup_limit');
    }
    public function getTotalLimit()
    {       
        return $this->getConfigValue(self::XML_PATH_NOTIFICATION .'general/total_limit');
    }
    public function isLoggedIn()
    {
        $customerSession = $this->customerSession->create();
        $isLoggedIn = $this->authContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);        
        if($customerSession->isLoggedIn() && $isLoggedIn) {
            return true;
        }else{        
            return false;
        }        
    }
    public function customerId()
    {
        $customerSession = $this->customerSession->create();
        return $customerSession->getCustomerId();
    }
    /**
     * Get Base Url
     *
     * @return mixed
     */
    public function getBaseUrl()
    {
        $baseUrl = $this->storeManager
                         ->getStore()
                         ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        return $baseUrl;
    }

    public function getDateDifference($date)
    { 
        $currentGMTDate=$this->currentGMTDate->gmtDate("Y-m-d H:i:s");        
        $diffDate = abs(strtotime($currentGMTDate)-strtotime($date));        
        $years = floor($diffDate/(365*60*60*24));
        $months = floor(($diffDate-$years*365*60*60*24)/(30*60*60*24));
        $days = floor(($diffDate-$years*365*60*60*24-$months*30*60*60*24)/(60*60*24));
        $hours = floor(($diffDate-$years*365*60*60*24-$months*30*60*60*24-$days*60*60*24)/(60*60));
        $minutes = floor(($diffDate-$years*365*60*60*24-$months*30*60*60*24-$days*60*60*24- $hours*60*60)/60);
        $seconds = floor(($diffDate-$years*365*60*60*24-$months*30*60*60*24 - $days*60*60*24-$hours*60*60-$minutes*60));
        $diffString='';
        if($years){
            $msg=($years==1)?" year ago":" years ago";
            $diffString=$years.$msg;
        }else if($months){
            $msg=($months==1)?" month ago":" months ago";
            $diffString=$months.$msg;
        }else if($days){
            $msg=($days==1)?" day ago":" days ago";
            $diffString=$days.$msg;
        }else if($hours){
            $msg=($hours==1)?" hour ago":" hours ago";
            $diffString=$hours.$msg;
        }else if($minutes){
            $msg=($minutes==1)?" minute ago":" minutes ago";
            $diffString=$minutes.$msg;
        }else if($seconds){
            $msg=($seconds==1)?" second ago":" seconds ago";
            $diffString=$seconds.$msg;
        }else{
            $diffString="0 second ago";
        }
        return $diffString;
    }

    public function getImageUrl($type)
    {     
        $imageUrl='';
        switch ($type) {
            case '0':
                $imageUrl='images/noti_icon4.png';
                break;
            
            case '1':
                $imageUrl='images/noti_icon6.png';
                break;
            
            case '2':
                $imageUrl='images/noti_icon9.png';
                break;
            
            default:
                break;
        }
        return $imageUrl;
    }   
    public function getAllCollection()
    {
        $to = $this->currentGMTDate->gmtDate('Y-m-d H:i:s');
        $from = strtotime('-'.$this->getTotalLimit().' day', strtotime($to));
        $from = date('Y-m-d', $from);
        $from = $from.' 00:00:00';

        $notifications = $this->notificationFactory->create()->getCollection();
        $notifications->addFieldToFilter('status', 1);
        // $notifications->addFieldToFilter('publishe_at', array('from' => $from, 'to' => $to));
        if($this->customerId()){
            $subquery = new \Zend_Db_Expr(
                '(SELECT * from tatva_delete_notification where  customer_id ='.$this->customerId().')'
            );

            $notifications->getSelect()
                    ->joinLeft( 
                        array('delete'=> $subquery), 
                        'main_table.notification_id = delete.notification_id'
                    )
                    ->where("COALESCE(delete.customer_id,0) NOT IN (".$this->customerId().")")
                    ->columns(array('main_table.notification_id'));
        }
        $notifications->setOrder('publishe_at','desc');
        return $notifications;
    }
    public function todayCollection()
    {
        $date = $this->currentGMTDate->gmtDate('Y-m-d');       
        $start=$date.' 00:00:00';
        $end=$date.' 23:59:59';

        $notifications = $this->getAllCollection();
        $notifications->addFieldToFilter('status', 1);
        $notifications->addFieldToFilter('publishe_at', array('from' => $start, 'to' => $end)); 

        return $notifications;
    }
    public function earlierCollection($pageNO)
    {
        $date = $this->currentGMTDate->gmtDate('Y-m-d');
        $ealierDate=$date.' 00:00:00';

        $notifications = $this->getAllCollection();
        $notifications->addFieldToFilter('status', 1);      
        // $notifications->addFieldToFilter('publishe_at', ['lteq' => $ealierDate]);
        $notifications->setOrder('publishe_at','desc');
        $notifications->setPageSize($this->getEalierLimit());       
                    
        if($pageNO <= $notifications->getLastPageNumber()){
            $notifications->setCurPage($pageNO);
        }else{
            $notifications=[];
        }
        return $notifications;
    }
    public function totalCount()
    {
        $notifications = $this->getAllCollection();     
        return $notifications->count();
    }
}
