<?php
namespace Vgroup65\Testimonial\Model\ResourceModel;

class Configuration extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('testimonial_configuration', 'configuration_id');
	}
	
}