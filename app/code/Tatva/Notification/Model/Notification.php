<?php
namespace Tatva\Notification\Model;
class Notification extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'tatva_notification';

	protected $_cacheTag = 'tatva_notification';

	protected $_eventPrefix = 'tatva_notification';

	protected function _construct()
	{
		$this->_init('Tatva\Notification\Model\ResourceModel\Notification');
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