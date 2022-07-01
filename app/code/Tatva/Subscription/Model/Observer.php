<?php
namespace Tatva\Subscription\Model;

/*
 * @category    Zen
 * @package     Zen_Ordercomment
 * @version     1.0.1
 * @copyright   Copyright (c) 2011 Zensolutions s.r.o. [http://www.zensolutions.cz]
 * @license     Commercial per server
 */

class Observer
{

    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $checkoutTypeOnepage;

    /**
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $catalogProductConfigurationHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    public function __construct(
        \Magento\Checkout\Model\Type\Onepage $checkoutTypeOnepage,
        \Magento\Catalog\Helper\Product\Configuration $catalogProductConfigurationHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->checkoutTypeOnepage = $checkoutTypeOnepage;
        $this->catalogProductConfigurationHelper = $catalogProductConfigurationHelper;
        $this->resourceConnection = $resourceConnection;
    }
    public function setCustomerSubscription( \Magento\Framework\Event\Observer $observer )
    {

             foreach ($this->checkoutTypeOnepage->getQuote()->getAllItems() as $item) {
                    if($item->getProduct()->getTypeId() == 'configurable')
                    {
                      $helper = $this->catalogProductConfigurationHelper;
                      $options = $helper->getConfigurableOptions($item);
                      foreach ($options as $_option)
                      {
                        $observer->getEvent()->getOrder()->setOrderSubscriptionPeriod($_option['value']);
                        $current_date = date('Y-m-d');
                        $yesterday_date = date("Y-m-d", strtotime("-1 day"));

                        //$tomorrow = mktime(0,0,0,date("m")+3,date("d"),date("Y"));
                        $dateMonthAdded = date('Y-m-d',strtotime(date("Y-m-d", strtotime($current_date)) . "+".$_option['value']));
                        $dateMonthending = date('Y-m-d',strtotime(date("Y-m-d", strtotime($yesterday_date)) . "+".$_option['value']));

                        $observer->getEvent()->getOrder()->setOrderSubscriptionFromDate($current_date);
                        $observer->getEvent()->getOrder()->setOrderSubscriptionToDate($dateMonthending);
                        $observer->getEvent()->getOrder()->setOrderSubscriptionRenewDate($dateMonthAdded);
                        $customer_id = $this->checkoutTypeOnepage->getQuote()->getCustomerId();

                      /*   $model = Mage::getModel('subscription/subscriptionhistory');

                        $model->setSubscriptionPeriod($_option['value']);
                        $model->setCustomerId($customer_id);
                        $model->setFromDate($current_date);
                        $model->setToDate($dateMonthending);
                        $model->setRenewDate($dateMonthAdded);
                        $model->setStatusSuccess('Pending');
                        $model->setIncrementId($observer->getEvent()->getOrder()->getIncrementId());

                        $model->save();             */



                      }
                    }
             }


    }
    public function tagdelete(\Magento\Framework\Event\Observer $observer)
    {

        $resource = $this->resourceConnection;
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');

        $sql = 'select `t`.`tag_id` from `tag` as `t` left join `tag_relation` as `tr` on `tr`.`tag_id` = `t`.`tag_id` where `tr`.`product_id` is null';

        $results = $readConnection->fetchAll($sql);

        $tag_ids = "";

        foreach($results as $r)
        {
            $tag_ids .= $r["tag_id"].",";
        }

        $tag_ids = rtrim($tag_ids,",");
        "delete from `tag` where `tag_id` in (".$tag_ids.")";
        if($tag_ids)
        $writeConnection->query("delete from `tag` where `tag_id` in (".$tag_ids.")");
    }
}
