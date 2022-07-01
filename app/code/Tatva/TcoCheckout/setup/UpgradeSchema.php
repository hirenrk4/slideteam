<?php
namespace Tatva\TcoCheckout\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {

		$installer = $setup;

		$installer->startSetup();

		if(version_compare($context->getVersion(), '0.1.1', '<')) {

			if (!$installer->tableExists('2checkout_ipn_cron')) {

				$table = $installer->getConnection()->newTable(
					$installer->getTable('2checkout_ipn_cron')
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
					'auto_increment' => true
					],
					'id'
				)
				->addColumn(
					'ipn_data',
	                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	                null,
	                ['unsigned' => true],
	                'Ipn Data'
				);

				$installer->getConnection()->createTable($table);
			}

			$installer->getConnection()->addColumn(
				$installer->getTable( '2checkout_ipn_cron'),
				'ipn_time',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
					'nullable' => false,					
					'comment' => 'Ipn Time',
					'after' => 'ipn_data'
				]
			);
		}

		if(version_compare($context->getVersion(), '0.1.2', '<')) {

			$installer->getConnection()->addColumn(
				$installer->getTable( '2checkout_ipn_cron'),
				'is_error',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
					'nullable' => true,					
					'comment' => 'is error',
					'after' => 'ipn_time'
				]
			);	
		}

		if(version_compare($context->getVersion(), '0.1.3', '<')) {

			$installer->getConnection()->addColumn(
				$installer->getTable( '2checkout_ipn_cron'),
				'customer_email',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => true,					
					'comment' => 'Customer Email',
					'after' => 'ipn_time'
				]
			);	
		}

		$installer->endSetup();
	}
}