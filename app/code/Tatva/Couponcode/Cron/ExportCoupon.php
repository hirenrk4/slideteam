<?php
namespace Tatva\Couponcode\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;

class ExportCoupon
{
    protected $_storeManager;
    protected $_transportBuilder;
    protected $scopeConfig;
    protected $directoryList;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceData,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\Storage\WriterInterface $writerConfig,
        DirectoryList $dirlist
    ) {
        $this->_resource = $resourceData;
        $this->_storeManager = $_storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_writerConfig = $writerConfig;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);   
        $this->directoryList = $dirlist;
    }

    public function execute()
    {
        $varDirPath = $this->directoryList->getPath("var");
        $lastweeklyId = $this->scopeConfig->getValue('couponcode/couponcode_email/last_weekly_orderid');

        $Ordercollection = $this->_orderCollectionFactory->create()->addAttributeToSelect('*');
        $Ordercollection->addFieldToFilter("applied_rule_ids",array("neq"=>'null'));
        $Ordercollection->addFieldToFilter("status",array("in"=>array("complete","payment_completed")));  
        $Ordercollection->addFieldToFilter("entity_id",array('gt'=>$lastweeklyId));
        $Ordercollection->getSelect()->where("created_at >= (CURDATE() + INTERVAL -7 DAY)");
        
        
        $orderCouponData = array();
        foreach($Ordercollection as $orderData)
        {
            $i = 0;
            if(array_key_exists($orderData->getAppliedRuleIds(), $orderCouponData))
            {      
                $iteamloop = 0;          
                foreach ($orderData->getAllItems() as $item)
                {
                    if(!($item->getProductType() == "simple" || $item->getProductType() == "virtual"))
                    {
                        continue;
                    }
                    if($item->getsku() == "1month-1" || $item->getsku() == "6months-7" || $item->getsku() == "12months-2" || $item->getsku() == "Annual with custom design-2" || $item->getsku() == "4-user-enterprise-license-1" || $item->getsku() == "1month" || $item->getsku() == "6months" || $item->getsku() == "12months" || $item->getsku() == "Annual with custom design" || $item->getsku() == "4-user-enterprise-license" || $item->getsku() == "Annual 4 User License" || $item->getsku() == "Annual 20 User License" || $item->getsku() == "Annual Company Wide Unlimited User License" || $item->getsku() == "Annual 15 User Education License" || $item->getsku() == "Annual UNLIMITED User Institute Wide License")
                    {
                        $i = 1;                        
                    }
                    else
                    {
                        continue;
                    }
                    $orderCouponData[$orderData->getAppliedRuleIds()][3] = ($item->getsku() == "1month-1" || $item->getsku() == "1month") ? $orderCouponData[$orderData->getAppliedRuleIds()][3]+1 : $orderCouponData[$orderData->getAppliedRuleIds()][3]+0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][4] = ($item->getsku() == "6months-7" || $item->getsku() == "6months") ? $orderCouponData[$orderData->getAppliedRuleIds()][4]+1 : $orderCouponData[$orderData->getAppliedRuleIds()][4]+0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][5] = ($item->getsku() == "12months-2" || $item->getsku() == "12months") ? $orderCouponData[$orderData->getAppliedRuleIds()][5]+1 : $orderCouponData[$orderData->getAppliedRuleIds()][5]+0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][6] = ($item->getsku() == "Annual with custom design-2" || $item->getsku() == "Annual with custom design") ? $orderCouponData[$orderData->getAppliedRuleIds()][6]+1 : $orderCouponData[$orderData->getAppliedRuleIds()][6]+0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][7] = ($item->getsku() == "4-user-enterprise-license-1" || $item->getsku() == "4-user-enterprise-license") ? $orderCouponData[$orderData->getAppliedRuleIds()][7]+1 : $orderCouponData[$orderData->getAppliedRuleIds()][7]+0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][8] = ($item->getsku() == "Annual 4 User License") ? $orderCouponData[$orderData->getAppliedRuleIds()][8]+1 : $orderCouponData[$orderData->getAppliedRuleIds()][8]+0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][9] = ($item->getsku() == "Annual 20 User License") ? $orderCouponData[$orderData->getAppliedRuleIds()][9]+1 : $orderCouponData[$orderData->getAppliedRuleIds()][9]+0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][10] = ($item->getsku() == "Annual Company Wide Unlimited User License") ? $orderCouponData[$orderData->getAppliedRuleIds()][10]+1 : $orderCouponData[$orderData->getAppliedRuleIds()][10]+0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][11] = ($item->getsku() == "Annual 15 User Education License") ? $orderCouponData[$orderData->getAppliedRuleIds()][11]+1 : $orderCouponData[$orderData->getAppliedRuleIds()][11]+0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][12] = ($item->getsku() == "Annual UNLIMITED User Institute Wide License") ? $orderCouponData[$orderData->getAppliedRuleIds()][12]+1 : $orderCouponData[$orderData->getAppliedRuleIds()][12]+0;                    
                }
                
                if($i == 1)
                {
                    $orderCouponData[$orderData->getAppliedRuleIds()][0] = $orderData->getCouponCode();
                    $orderCouponData[$orderData->getAppliedRuleIds()][1] = $orderCouponData[$orderData->getAppliedRuleIds()][1]+1;
                    $orderCouponData[$orderData->getAppliedRuleIds()][2] = $orderCouponData[$orderData->getAppliedRuleIds()][2]+$orderData->getBaseGrandTotal();
                }
                
            }
            else
            {
                foreach ($orderData->getAllItems() as $item)
                {
                    if(!($item->getProductType() == "simple" || $item->getProductType() == "virtual"))
                    {
                        continue;
                    }
                    if($item->getsku() == "1month-1" || $item->getsku() == "6months-7" || $item->getsku() == "12months-2" || $item->getsku() == "Annual with custom design-2" || $item->getsku() == "4-user-enterprise-license-1" || $item->getsku() == "1month" || $item->getsku() == "6months" || $item->getsku() == "12months" || $item->getsku() == "Annual with custom design" || $item->getsku() == "4-user-enterprise-license" || $item->getsku() == "Annual 4 User License" || $item->getsku() == "Annual 20 User License" || $item->getsku() == "Annual Company Wide Unlimited User License" || $item->getsku() == "Annual 15 User Education License" || $item->getsku() == "Annual UNLIMITED User Institute Wide License")
                    {
                        $i = 1;                        
                    }
                    else
                    {
                        continue;
                    }
                    $orderCouponData[$orderData->getAppliedRuleIds()][3] = ($item->getsku() == "1month-1" || $item->getsku() == "1month") ? 1 : 0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][4] = ($item->getsku() == "6months-7" || $item->getsku() == "6months") ? 1 : 0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][5] = ($item->getsku() == "12months-2" || $item->getsku() == "12months") ? 1 : 0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][6] = ($item->getsku() == "Annual with custom design-2" || $item->getsku() == "Annual with custom design") ? 1 : 0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][7] = ($item->getsku() == "4-user-enterprise-license-1" || $item->getsku() == "4-user-enterprise-license") ? 1 : 0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][8] = ($item->getsku() == "Annual 4 User License") ? 1 : 0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][9] = ($item->getsku() == "Annual 20 User License") ? 1 : 0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][10] = ($item->getsku() == "Annual Company Wide Unlimited User License") ? 1 : 0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][11] = ($item->getsku() == "Annual 15 User Education License") ? 1 : 0;
                    $orderCouponData[$orderData->getAppliedRuleIds()][12] = ($item->getsku() == "Annual UNLIMITED User Institute Wide License") ? 1 : 0;
                }

                if($i == 1)
                {
                    $orderCouponData[$orderData->getAppliedRuleIds()][0] = $orderData->getCouponCode();
                    $orderCouponData[$orderData->getAppliedRuleIds()][1] = 1;
                    $orderCouponData[$orderData->getAppliedRuleIds()][2] = $orderData->getBaseGrandTotal();
                }
                
            }
            $lastweeklyId = $orderData->getId();
        }

        $this->_writerConfig->save('couponcode/couponcode_email/last_weekly_orderid', $lastweeklyId);

        $filepath = 'couponReport/couponreport_weekly.csv';
        $file = 'couponreport_weekly.csv';
        $this->directory->create('couponReport');
        $stream = $this->directory->openFile($filepath, 'w+');

        $header = [
            'Coupon Code',
            'Coupon used in last 7 days',
            'Total revenue in last 7 days',
            "Monthly",
            "Semi-Annual",
            "Annual",
            "Annual + Custom Design",
            "Team License",
            "Annual 4 User License",
            "Annual 20 User License",
            "Annual Company Wide Unlimited User License",
            "Annual 15 User Education License",
            "Annual UNLIMITED User Institute Wide License"
        ];

        $stream->writeCsv($header);
        $new = 0;
   
        foreach($orderCouponData as $data)
        {
            $itemData = [];
            $itemData = [
                $data['0'],
                $data['1'],
                $data['2'],
                $data['3'],
                $data['4'],
                $data['5'],
                $data['6'],
                $data['7'],
                $data['8'],
                $data['9'],
                $data['10'],
                $data['11'],
                $data['12']                
            ];

            $stream->writeCsv($itemData);
            $new = 1;
        }
        
        $mail = new \Zend_Mail();
        $message = "Please find an attachment for the Coupon Report used in last 7 days.";
        $mail->setFrom("support@slideteam.net",'SlideTeam Support');
        $mail->setSubject('Weekly Coupon Report');
        $mail->setBodyHtml($message);

        $at = new \Zend_Mime_Part(file_get_contents($varDirPath."/".$filepath,true));

        $at->type        = \Zend_Mime::TYPE_OCTETSTREAM;
        $at->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $at->encoding    = \Zend_Mime::ENCODING_BASE64;
        $at->filename    = $file;

        $mail->addAttachment($at);

        $to_email = explode(',',$this->scopeConfig->getValue('couponcode/couponcode_email/to_email'));
        $cc_email = explode(',',$this->scopeConfig->getValue('couponcode/couponcode_email/cc_email'));

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