<?php

namespace Tatva\Unsubscribeuser\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $installer = $setup;

        $installer->startSetup();   
        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $installer->getConnection()->addColumn(
                $installer->getTable('tatva_unsubscribe_user'),
                'backend_comment',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment'=>'backend_comment'
                ]
            );

        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {

            $installer->getConnection()->addColumn(
                $installer->getTable('tatva_unsubscribe_user'),
                'is_export',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'dafult' => 0,
                    'comment'=>'is_export'
                ]
            );
        }
        $setup->endSetup();
    }
}
