<?php

namespace Tatva\Customer\Ui\Component\Listing;

class CustomerDataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected function _initSelect()
    {
        parent::_initSelect();
         
        $this->addMyCustomFilter();
        //$this->addSubscriberFilter();
        /*$this->getSelect()->joinLeft
        (
            ['newsletter_subscriber' => $this->getTable('newsletter_subscriber')],
            'main_table.entity_id = newsletter_subscriber.customer_id',
            ['subscriber_status']
        );*/
        $this->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(array('entity_id ','name','email','billing_telephone','created_at','website_id','created_in','deactivate_captcha','social_login.type','ipstack_customer_country_code as browser_country'));
        return $this;
    }

    public function addMyCustomFilter()
    {
        $this->getSelect()->joinLeft
        (
            ['social_login' => $this->getTable('mageplaza_social_customer')],
            'main_table.entity_id = social_login.customer_id',
            [ 'type']
        )
        ->group('main_table.entity_id');
        return $this;

    }

    public function addSubscriberFilter()
    {
        $this->getSelect()->joinLeft
        (
            ['subscriber' => $this->getTable('subscription_invitation')],
            'main_table.entity_id = subscriber.child_customer_id',
            [ 'child_customer_id']
        );
        //->group('main_table.entity_id');
        return $this;

    }
}