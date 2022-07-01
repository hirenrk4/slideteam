<?php
namespace Tatva\Paypalrec\Model\ResourceModel;


class Result extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context,
		$connectionName = null
	){
		parent::__construct(
			$context,
			$connectionName
		);
	}

	protected function _construct()
	{
		$this->_init('paypal_result', 'id');
	}

	public function addpayment($object)
	{
	  $write = $this->_getWriteAdapter($object);
	  $write->insert('paypal_result',$object->getArray()); 
	}

}