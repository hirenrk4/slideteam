<?php
namespace Tatva\Couponcode\Cron;

class CouponStatus
{
    protected $_storeManager;
    protected $_transportBuilder;
    protected $scopeConfig;
    protected $salesrulecollection;
    protected $_dateFactory;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceData,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $salesrulecollection,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->salesrulecollection = $salesrulecollection;
        $this->_dateFactory = $dateTimeDateTimeFactory;
        $this->_resource = $resourceData;
        $this->_storeManager = $_storeManager;
        $this->_transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
    
    }

    public function execute()
    {
       

        $coupon_collection = $this->_resource->getConnection()->fetchAll("SELECT `main_table`.*, `rule_coupons`.`code`,DATEDIFF(to_date,CURDATE()) AS expire_period FROM `salesrule` AS `main_table` LEFT JOIN `salesrule_coupon` AS `rule_coupons` ON `main_table`.`rule_id` = `rule_coupons`.`rule_id` AND `rule_coupons`.`is_primary` = 1 WHERE `main_table`.`name` != '40% discount' AND `is_active` = 1 AND to_date >= CURDATE() AND DATEDIFF(to_date,CURDATE()) <= 7");
        if(count($coupon_collection) > 0)
        {
            $vars = array();
            $coupon_data = '';
            foreach ($coupon_collection as $key => $cpnValue) {
                $coupon_data.='<span style="font-size:14px;">'.$cpnValue['name'].' <span style="padding:0 5px">-</span> '.date("jS F, Y", strtotime($cpnValue['to_date'])).'</span><br/><br/>';
            }

            $vars['coupondata'] = $coupon_data;
        
            $templateId = $this->scopeConfig->getValue("couponcode/couponcode_email/template_id", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            $storeId = $this->_storeManager->getStore()->getId();

            $from = array("email" => $this->scopeConfig->getValue("trans_email/ident_sales/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE), "name" => $this->scopeConfig->getValue("trans_email/ident_sales/name", \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $to = $this->scopeConfig->getValue("couponcode/couponcode_email/to_email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $Cc = $this->scopeConfig->getValue("couponcode/couponcode_email/cc_email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
            $emails = explode(",",$Cc);
            $toEmails = explode(",", $to);


            try {
                $this->_transportBuilder->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId))
                    ->setTemplateVars($vars)
                    ->setFrom($from);

                if(!empty($toEmails))
                {
                    foreach ($toEmails as $value) {
                        $this->_transportBuilder->addTo($value);
                    }
                }

                if(!empty($emails))
                {
                    foreach($emails as $email)
                    {
                        $this->_transportBuilder->addCc($email);
                    }
                }
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                
            } catch (Exception $e) {
                
            }

            $now = $this->_dateFactory->create()->gmtDate("Y-m-d H:i:s");
            $todate = $this->_dateFactory->create()->gmtDate("Y-m-d H:i:s", strtotime('-24 hours', strtotime($now)));
            $collection = $this->salesrulecollection->create();
            $collection->getSelect()->joinInner(array('src'=>'coupon_customer_relation'),'src.sales_rule_id=main_table.rule_id',array('src.sales_rule_id'));
            $collection->addFieldToFilter('main_table.to_date',array('lt'=>$todate));
            $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
            $collection->getSelect()->columns('main_table.rule_id');
            foreach ($collection as $item) 
            {
                    $item->delete();
            }

        }

        
    }
}