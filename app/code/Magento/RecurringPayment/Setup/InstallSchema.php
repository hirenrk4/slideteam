<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RecurringPayment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        // $installer->startSetup();

        /**
         * Create table 'recurring_payment'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('recurring_payment'))
            ->addColumn('payment_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ), 'Payment Id')
            ->addColumn('state', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, array(
                'nullable'  => false,
            ), 'State')
            ->addColumn('customer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
            ), 'Customer Id')
            ->addColumn('store_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
            ), 'Store Id')
            ->addColumn('method_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 32, array(
                'nullable'  => false,
            ), 'Method Code')
            ->addColumn('created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
                'nullable'  => false,
            ), 'Created At')
            ->addColumn('updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
            ), 'Updated At')
            ->addColumn('reference_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 32, array(
            ), 'Reference Id')
            ->addColumn('subscriber_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 150, array(
            ), 'Subscriber Name')
            ->addColumn('start_datetime', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, array(
                'nullable'  => false,
            ), 'Start Datetime')
            ->addColumn('internal_reference_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 42, array(
                'nullable'  => false,
            ), 'Internal Reference Id')
            ->addColumn('schedule_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, array(
                'nullable'  => false,
            ), 'Schedule Description')
            ->addColumn('suspension_threshold', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
            ), 'Suspension Threshold')
            ->addColumn('bill_failed_later', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ), 'Bill Failed Later')
            ->addColumn('period_unit', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, array(
                'nullable'  => false,
            ), 'Period Unit')
            ->addColumn('period_frequency', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
            ), 'Period Frequency')
            ->addColumn('period_max_cycles', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
            ), 'Period Max Cycles')
            ->addColumn('billing_amount', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '12,4', array(
                'nullable'  => false,
                'default'   => '0.0000',
            ), 'Billing Amount')
            ->addColumn('trial_period_unit', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, array(
            ), 'Trial Period Unit')
            ->addColumn('trial_period_frequency', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
            ), 'Trial Period Frequency')
            ->addColumn('trial_period_max_cycles', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
            ), 'Trial Period Max Cycles')
            ->addColumn('trial_billing_amount', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, array(
            ), 'Trial Billing Amount')
            ->addColumn('currency_code', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 3, array(
                'nullable'  => false,
            ), 'Currency Code')
            ->addColumn('shipping_amount', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '12,4', array(
            ), 'Shipping Amount')
            ->addColumn('tax_amount', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '12,4', array(
            ), 'Tax Amount')
            ->addColumn('init_amount', \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL, '12,4', array(
            ), 'Init Amount')
            ->addColumn('init_may_fail', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ), 'Init May Fail')
            ->addColumn('order_info', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', array(
                'nullable'  => false,
            ), 'Order Info')
            ->addColumn('order_item_info', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', array(
                'nullable'  => false,
            ), 'Order Item Info')
            ->addColumn('billing_address_info', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', array(
                'nullable'  => false,
            ), 'Billing Address Info')
            ->addColumn('shipping_address_info', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', array(
            ), 'Shipping Address Info')
            ->addColumn('payment_vendor_info', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', array(
            ), 'Payment Vendor Info')
            ->addColumn('additional_info', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', array(
            ), 'Additional Info')
            ->addIndex(
                $installer->getIdxName(
                    'recurring_payment',
                    array('internal_reference_id'),
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                array('internal_reference_id'), array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE))
            ->addIndex($installer->getIdxName('recurring_payment', array('customer_id')),
                array('customer_id'))
            ->addIndex($installer->getIdxName('recurring_payment', array('store_id')),
                array('store_id'))
            ->addForeignKey($installer->getFkName('recurring_payment', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id', $installer->getTable('customer_entity'), 'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)
            ->addForeignKey($installer->getFkName('recurring_payment', 'store_id', 'store', 'store_id'),
                'store_id', $installer->getTable('store'), 'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)
            ->setComment('Sales Recurring Payment');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'recurring_payment_order'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('recurring_payment_order'))
            ->addColumn('link_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
            ), 'Link Id')
            ->addColumn('payment_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ), 'Payment Id')
            ->addColumn('order_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, array(
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
            ), 'Order Id')
            ->addIndex(
                $installer->getIdxName(
                    'recurring_payment_order',
                    array('payment_id', 'order_id'),
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                array('payment_id', 'order_id'), array('type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE))
            ->addIndex($installer->getIdxName('recurring_payment_order', array('order_id')),
                array('order_id'))
            ->addForeignKey(
                $installer->getFkName(
                    'recurring_payment_order',
                    'order_id',
                    'sales_order',
                    'entity_id'
                ),
                'order_id', $installer->getTable('sales_order'), 'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)
            ->addForeignKey(
                $installer->getFkName(
                    'recurring_payment_order',
                    'payment_id',
                    'recurring_payment',
                    'payment_id'
                ),
                'payment_id', $installer->getTable('recurring_payment'), 'payment_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE, \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)
            ->setComment('Sales Recurring Payment Order');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}