<?php
namespace Tatva\RecurringOrders\Model;

class Recurringorder extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'recurring_orders';

    protected function _construct()
    {
        $this->_init('Tatva\RecurringOrders\Model\ResourceModel\Recurringorder');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}