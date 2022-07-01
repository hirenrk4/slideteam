<?php
namespace Tatva\Customer\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.0', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('customer_entity'),
                'cid',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => false,
                    'comment' => 'CID'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('customer_entity'),
                'uuid',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => false,
                    'comment' => 'UUID'
                ]
            )
          ;
          $installer->getConnection()->addColumn(
                $installer->getTable('directory_country'),
                'isd_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 7,
                    'nullable' => false,
                    'comment' => 'isd_code'
                ]
            )
          ;
          $installer->getConnection()->addColumn(
                $installer->getTable('newsletter_subscriber'),
                'uuid',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => false,
                    'comment' => 'UUID'
                ]
            )
          ;
        }
        if (version_compare($context->getVersion(), '1.0.9', '<')) {
           
            // Create table 'tatva_customer_killed_sessions'
             if (!$installer->tableExists('tatva_customer_killed_sessions')) {
               
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('tatva_customer_killed_sessions')
                )->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, 'auto_increment' => true],
                    'ID'
                )->addColumn(
                    'customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false],
                    'Customer Id'
                )->addColumn(
                    'email',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false],
                    'Customer Email'
                )->addColumn(
                    'total_killed',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false],
                    'Total Killed Sessions'
                )->addColumn(
                    'location',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '16M',
                    ['nullable' => true],
                    'Session Killed Location'
                )->addColumn(
                    'timestamp',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '16M',
                    [],
                    'Session Killed Time'
                )->addColumn(
                    'subscription_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Type Of Subscription'
                )->addColumn(
                    'no_of_downloads',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    10,
                    ['nullable' => false],
                    'No Of Downloads'
                )->setComment(
                    'Customer Killed Sessions Table'
                );
                $installer->getConnection()->createTable($table);
            }
        }
        if (version_compare($context->getVersion(), '1.0.11', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('tatva_customer_killed_sessions'),
                'notes',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => false,
                    'comment' => 'notes'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('tatva_customer_killed_sessions'),
                'ip_address',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => false,
                    'comment' => 'ip_address'
                ]
            );
        }
        $installer->endSetup();
    }
}
    ?>