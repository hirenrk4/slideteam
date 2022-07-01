<?php
namespace Tatva\Deleteaccount\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
	public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
		$installer = $setup;

		$installer->startSetup();

		if(version_compare($context->getVersion(), '0.0.2', '<')) {
			$installer->getConnection()->addColumn(
				$installer->getTable( 'tatva_delacc_customer_bkp' ),
				'feedback',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'nullable' => false,
					'length' => '255',
					'comment' => 'feedback',
				]
			);
		}

		/*if(version_compare($context->getVersion(), '0.0.3', '<')) {
			$installer->getConnection()->addColumn(
				$installer->getTable( 'salesrule' ),
				'customer_id',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					'nullable' => false,
					'length' => 11,
					'comment' => 'customer_id',
				]
			);
		}*/

		$installer->endSetup();
	}
}