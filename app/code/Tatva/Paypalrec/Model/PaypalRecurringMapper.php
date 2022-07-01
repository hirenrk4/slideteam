<?php
namespace Tatva\Paypalrec\Model;


class PaypalRecurringMapper extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'tatva_paypalrec_pp_recurring_mapper';

	protected $_cacheTag = 'tatva_paypalrec_pp_recurring_mapper';

	protected $_eventPrefix = 'tatva_paypalrec_pp_recurring_mapper';

	protected function _construct()
	{
		$this->_init('Tatva\Paypalrec\Model\ResourceModel\PaypalRecurringMapper');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}