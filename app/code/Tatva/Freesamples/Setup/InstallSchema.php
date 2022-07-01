<?php
namespace Tatva\Freesamples\Setup;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('video')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('video')
				)
			->addColumn(
				'video_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				10,
				[
				'identity' => true,
				'nullable' => false,
				'primary'  => true,
				'auto_increment' => true
				],
				'video_id'
				)
			->addColumn(
				'product_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				11,
				[
				'nullable => false',
				'unsigned' => 'true',
				'default'=> 0
				],
				'product_id'
				)
			->addColumn(
				'video_path',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[
				'nullable => false'
				],
				'video_path'
				);
			

				$installer->getConnection()->createTable($table);
			}

            $installer->endSetup();
        }
    }
