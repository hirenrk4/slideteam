<?php
namespace Tatva\Notification\Setup;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('tatva_notification')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('tatva_notification')
                )
            ->addColumn(
                'notification_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                'identity' => true,
                'nullable' => false,
                'primary'  => true,
                'unsigned' => true,
                'auto_increment' => true
                ],
                'Notification Id'
                )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable' => false,
                ],
                'Created By'
                )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                [],
                'Status'
            )                            
            ->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                [],
                'Type'
            )
            ->addColumn(
                'customer_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                [],
                'Customer Type'
            )
            ->addColumn(
                'paid_duration',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                [],
                'Paid Duration'
            )
            ->addColumn(
                'content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Content'
            )
            ->addColumn(
                'publishe_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Publishe At'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            );
            $installer->getConnection()->createTable($table);
            $installer->getConnection()->addIndex(
                $installer->getTable('tatva_notification'),
                $setup->getIdxName(
                    $installer->getTable('tatva_notification'),
                    ['created_by','content'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['created_by','content'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        if (!$installer->tableExists('tatva_delete_notification')) {
            $table = $installer->getConnection()
            ->newTable('tatva_delete_notification')
            ->addColumn(
                'delete_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Delete ID'
            )
            ->addColumn(
                'notification_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Notification Id'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned'=>true, 'nullable'=>false, 'default' => '0'],
                'Customer Id'
            )
            ->addIndex(
                $installer->getIdxName(
                    'tatva_delete_notification',
                    ['notification_id','customer_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['notification_id','customer_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'tatva_delete_notification',
                    'notification_id',
                    'tatva_notification',
                    'notification_id'
                ),
                'notification_id',
                $installer->getTable('tatva_notification'),
                'notification_id',
                Table::ACTION_CASCADE
            )            
            ->setComment('Delete Notification Table');

            $installer->getConnection()->createTable($table);
        }
    }
}
