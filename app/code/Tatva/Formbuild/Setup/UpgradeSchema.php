<?php
namespace Tatva\Formbuild\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $setup->startSetup();   
            $setup->getConnection()->addColumn(
                $setup->getTable('form_data'),
                'email_from',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Email From'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('form_data'),
                'email_to',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Email To'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('form_data'),
                'email_cc',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Email CC'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('form_data'),
                'email_bcc',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Email BCC'
                ]
            );
            $setup->getConnection()->addColumn(
                $setup->getTable('form_data'),
                'status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'length' => null,
                    'nullable' => false,
                    'default' => '1',
                    'comment' => 'Status'
                ]
            );
            $setup->endSetup();
        }
        
    }
}
