<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Flat sales order collection
 */
namespace Magento\RecurringPayment\Model\ResourceModel\Order;

class CollectionFilter
{
    /**
     * Add filter by specified recurring payment id(s)
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @param array|int $ids
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function byIds($collection, $ids)
    {
        $ids = (is_array($ids)) ? $ids : array($ids);
        $collection->getSelect()
            ->joinInner(
                array('rpo' => $collection->getTable('recurring_payment_order')),
                'main_table.entity_id = rpo.order_id',
                array())
            ->where('rpo.payment_id IN(?)', $ids);
        return $collection;
    }
}
