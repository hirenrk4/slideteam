<?php
namespace Tatva\Paypalrec\Model;

class Result  extends \Magento\Framework\Model\AbstractModel
{	
	protected $increment_id;
	protected $paypal_id;
	protected $recurring;
	protected $subscription_date;
	protected $reattempt;
	protected $period;
	protected $amount;
	protected $trasaction_type;
	protected $result_data_from;
	protected $success;

	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
	) {
		parent::__construct(
			$context,
			$registry,
			$resource,
			$resourceCollection,
			$data
		);
	}

	public function _construct()
	{
		parent::_construct();
		$this->_init('Tatva\Paypalrec\Model\ResourceModel\Result');
	}

	public function getArray()
	{
		return array(
			"increment_id" => $this->increment_id,
			"paypal_id" => $this->paypal_id,
			"recurring" => $this->recurring,
			"subscription_date" => $this->subscription_date,
			"reattempt" => $this->reattempt,
			"period" => $this->period,
			"amount" => $this->amount,
			"trasaction_type" => $this->trasaction_type,
			"result_data_from" => $this->result_data_from,
			"success" => $this->success,
		);
	}

	public function getUpdateArray()
	{
		$resultArray=array();
		$temp=$this->getArray();
		foreach($temp as $key=>$value):
			if($value!="" && $value!=NULL):
				$resultArray[$key]=$value;
			endif;
		endforeach;
		return $resultArray;
	}
}

?>
