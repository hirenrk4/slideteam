<?php
namespace Tatva\TcoCheckout\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
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
	}
}			