<?php
namespace Tatva\RecurringOrders\Setup;


class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('recurring_orders')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('recurring_orders')
            )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'ID'
                )
                ->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['nullable => false'],
                    'customer_id'
                )
                ->addColumn(
                    'cust_email',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Customer Email'
                )
                ->addColumn(
                    'recu_amount',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable => false'],
                    'Recurring Amount'
                )
                ->addColumn(
                    'recu_datetime',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    [],
                    'Recurring DateTime'
                )
                ->addColumn(
                    'pay_method',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Payment Mathod'
                )
                ->addColumn(
                    'nextrecu_datetime',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    [],
                    'Next Recurring DateTime'
                )
                ->setComment('Recurring Orders Table');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
