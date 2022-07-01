<?php
namespace Tatva\Customerdashboard\Setup;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('tatva_customer_dashboard')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('tatva_customer_dashboard')
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
				'customer_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				11,
				[
				'nullable => false',
				'default'=> 0
				],
				'Customer ID'
				)
			->addColumn(
				'customer_email',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'255',
				[],
				'Customer Email'
				)
			->addColumn(
				'page_uri',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'255',
				[
				'nullable => false',
				'default'=> ''
				],
				'Page URI'
				)
			->addColumn(
				'event',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'255',
				[
				'nullable => true',
				'default'=> ''
				],
				'Event'
				)
			->addColumn(
				'created_time',
				\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
				'',
				[
					'nullable => true',
				],
				'created_time'
				);
				$installer->getConnection()->createTable($table);

        }
    }
}
