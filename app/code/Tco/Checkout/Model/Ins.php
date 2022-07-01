<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tco\Checkout\Model;


class Ins extends \Magento\Framework\Model\AbstractModel
{
	protected $subscriptionFactory;

	protected $insCollectionFactory;

	/**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $_dateFactory;

	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
		\Magento\Backend\Model\SessionFactory $adminsession,
		\Tco\Checkout\Model\ResourceModel\Ins\CollectionFactory $insCollectionFactory,
		\Tatva\Subscription\Model\SubscriptionFactory $subscriptionFactory,
		\Magento\Framework\App\State $storeManager,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
	) {
        $this->_dateFactory = $dateTimeDateTimeFactory;
		$this->customerSession = $customerSession;
		$this->adminsession = $adminsession;
		$this->_storeManager = $storeManager;
		$this->insCollectionFactory = $insCollectionFactory;
		$this->subscriptionFactory = $subscriptionFactory;
		parent::__construct(
			$context,
			$registry,
			$resource,
			$resourceCollection,
			$data
		);
	}


	protected function _construct()
	{
		$this->_init('Tco\Checkout\Model\ResourceModel\Ins');
	}

	public function saveInsData($request)
	{

		$message_availability = false;

		if (isset($request["message_id"]) && isset($request["vendor_id"]) ) {
			$message_availability = $this->checkMessageAvailability($request["message_id"],$request["vendor_id"]);
		}


		if($message_availability === false){
			$ins_model = $this;
		}
		else{
			$ins_model = $message_availability;
			return $ins_model->getId();
		}


		$insMessage = array();

		foreach ($request as $k => $v) {
			$v = htmlspecialchars($v);
			$v = stripslashes($v);
			$ins_model->setData($k,$v);
		}
		$date = $this->_dateFactory->create()->gmtDate();
		$ins_model->setData("created_at",$date);

		if(!isset($request["vendor_order_id"]) && isset($request["merchant_order_id"]) && $request["merchant_order_id"]!=""){
			$ins_model->setData("vendor_order_id",$request["merchant_order_id"]);
		}

		try
		{
			$ins_model->save();

			if($message_availability === false){
				return $ins_model->getId();
			}
			else{
				return false;
			}

		}
		catch(Exception $e)
		{
			throw new Exception($e->getMessage(), 1);			
			return "";
		}

		return "";

	}
	
	
	public function checkMessageAvailability($message_id,$vendor_id)
	{
		$ins_model = $this->insCollectionFactory->create()
		->addFieldToFilter("message_id",$message_id)
		->addFieldToFilter("vendor_id",$vendor_id)
		->getFirstItem();

		if(is_object($ins_model) && $ins_model->getId() != ""){
			return $ins_model;
		}
		else{
			return false;
		}

	}


	public function checkSubscriptionAvailibility($order_increment_id)
	{
		$two_checkout_query = "select id from 2checkout_ins where vendor_order_id = '".$order_increment_id."'";
		$subscription_history_object =  $this->subscriptionFactory->create()
		->getCollection()
		->addFieldToFilter("increment_id",$order_increment_id)
		->addFieldToFilter("two_checkout_message_id",array("neq"=>""))
		->addFieldToFilter("two_checkout_message_id",array("in"=>new \Zend_Db_Expr($two_checkout_query)));

		$subscription_history_object->getSelect()->order("subscription_history_id desc");

		if(is_object($subscription_history_object) && sizeof($subscription_history_object)>0)
		{
			foreach($subscription_history_object as $sho)
			{
				if(is_object($sho) && $sho->getId()!="")
					return $sho;
			}

		}

		return "";

	}
	
}