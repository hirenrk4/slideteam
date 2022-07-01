<?php
namespace Tatva\Subscription\Setup;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('subscription_history')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('subscription_history')
				)
			->addColumn(
				'subscription_history_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				11,
				[
				'identity' => true,
				'nullable' => false,
				'primary'  => true,
				'unsigned' => true,
				'auto_increment' => true
				],
				'subscription_history_id'
				)
			->addColumn(
				'customer_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				11,
				[
				'nullable => false',
				'default'=> 0
				],
				'customer_id'
				)
			->addColumn(
				'increment_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable => false',
				],
				'increment_id'
				)
			->addColumn(
				'subscription_period',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'255',
				[
				'nullable => false',
				'default'=> ''
				],
				'subscription_period'
				)
			->addColumn(
				'from_date',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
				null,
				[
				'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
				],
				'from_date'
				)
			->addColumn(
				'to_date',
				\Magento\Framework\DB\Ddl\Table::TYPE_DATE,
				null,
				[],
				'to_date'
				)
			->addColumn(
				'admin_start_date',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
				null,
				[
				'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
				],
				'admin_start_date'
				)  
			->addColumn(
				'admin_end_date',
				\Magento\Framework\DB\Ddl\Table::TYPE_DATE,
				null,
				[],
				'admin_end_date'
				)
			->addColumn(
				'renew_date',
				\Magento\Framework\DB\Ddl\Table::TYPE_DATE,
				null,
				[],
				'renew_date'
				)
			->addColumn(
				'reminder_success',
				\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
				1,
				[
				'nullable => false',
				'default'=> 0
				],
				'reminder_success'
				)
			->addColumn(
				'status_success',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				null,
				[
				],
				'status_success'
				)
			->addColumn(
				'user_status_unsubscribe',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				1,
				[
				'nullable => false',
				'default'=> 0
				],
				'user_status_unsubscribe'
				)
			->addColumn(
				'user_status_unsubscribe_date',
				\Magento\Framework\DB\Ddl\Table::TYPE_DATE,
				null,
				[
				],
				'user_status_unsubscribe_date'
				)
			->addColumn(
				'paypal_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				],
				'paypal_id'
				)
			->addColumn(
				'txn_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				],
				'txn_id'
				)
			->addColumn(
				'created_time',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
				null,
				[
				'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
				],
				'created_time'
				)
			->addColumn(
				'update_time',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
				null,
				[
				'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE
				],
				'update_time'
				)->addColumn(
				'two_checkout_message_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				11,
				[
				],
				'two_checkout_message_id'
				)
				->addColumn(
					'download_limit',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					5,
					[
					'nullable'=>'false',
					'default'=> -1
					],
					'download_limit'
					)
				->addColumn(
					'admin_modified',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					1,
					[
					'nullable'=>'false',
					],
					'admin_modified'
					)
				->addColumn(
					'reminder_purchase',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					6,
					[
					'default'=>0,
					],
					'reminder_purchase'
					);

				$installer->getConnection()->createTable($table);
			}

//Table productdownload_history


			if (!$installer->tableExists('productdownload_history')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('productdownload_history')
					)
				->addColumn(
					'download_history_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					11,
					[
					'identity' => true,
					'nullable' => false,
					'primary'  => true,
					'unsigned' => true,
					'auto_increment' => true
					],
					'download_history_id'
					)
				->addColumn(
					'product_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					10,
					[
					'nullable => false',
					'unsigned' => true,
					],
					'product_id'
					)
				->addColumn(
					'category_ids',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					100,
					[
					'nullable => false',
					],
					'category_ids'
					)
				->addColumn(
					'main_category_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					'100',
					[
					'nullable => false',
					],
					'main_category_id'
					)
				->addColumn(
					'customer_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					11,
					[
					'default' => 0
					],
					'customer_id'
					)
				->addColumn(
					'download_count',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					11,
					[
					'nullable => false',
					'default' => 0
					],
					'download_count'
					)
                ->addForeignKey( // Add foreign key for table entity
                	$installer->getFkName(
                    'productdownload_history', // New table
                    'product_id', // Column in New Table
                    'catalog_product_entity', // Reference Table
                    'entity_id' // Column in Reference table
                    ),
                'product_id', // New table column
                $installer->getTable('catalog_product_entity'), // Reference Table
                'entity_id', // Reference Table Column
                // When the parent is deleted, delete the row with foreign key
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ;

                $installer->getConnection()->createTable($table);
            }


//Table productdownload_history_log

            
            if (!$installer->tableExists('productdownload_history_log')) {
            	$table = $installer->getConnection()->newTable(
            		$installer->getTable('productdownload_history_log')
            		)
            	->addColumn(
            		'log_id',
            		\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            		11,
            		[
            		'identity' => true,
            		'nullable' => false,
            		'primary'  => true,
            		'unsigned' => true,
            		'auto_increment' => true
            		],
            		'log_id'
            		)
            	->addColumn(
            		'product_id',
            		\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            		10,
            		[
            		'nullable => false',
            		'unsigned' => true,
            		],
            		'product_id'
            		)
            	->addColumn(
            		'customer_id',
            		\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            		11,
            		[
            		'nullable => false',
            		'default'=>0
            		],
            		'customer_id'
            		)
            	->addColumn(
            		'download_date',
            		\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            		null,
            		[
            		'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
            		],
            		'download_date'
            		)
            	->addColumn(
            		'ip',
            		\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            		255,
            		[],
            		'ip'
            		)
            	->addColumn(
            		'cookie_id',
            		\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            		6,
            		[],
            		'cookie_id'
            		) ->addColumn(
            		'browser',
            		\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            		255,
            		[],
            		'browser'
            		)
                ->addForeignKey( // Add foreign key for table entity
                	$installer->getFkName(
                    'productdownload_history_log', // New table
                    'product_id', // Column in New Table
                    'catalog_product_entity', // Reference Table
                    'entity_id' // Column in Reference table
                    ),
                'product_id', // New table column
                $installer->getTable('catalog_product_entity'), // Reference Table
                'entity_id', // Reference Table Column
                // When the parent is deleted, delete the row with foreign key
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ;

                $installer->getConnection()->createTable($table);
            }


            //Table productdownload_history


			if (!$installer->tableExists('mcsshareanddownloadproducts')) {
				$table = $installer->getConnection()->newTable(
					$installer->getTable('mcsshareanddownloadproducts')
					)
				->addColumn(
					'shareanddownloadproducts_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					11,
					[
					'identity' => true,
					'nullable' => false,
					'primary'  => true,
					'unsigned' => true,
					'auto_increment' => true
					],
					'shareanddownloadproducts_id'
					)
				->addColumn(
					'product_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					10,
					[
					'nullable => false',
					'unsigned' => true,
					],
					'product_id'
					)
				->addColumn(
					'shareanddownloadstatus',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					6,
					[
					'nullable => false',
					'default'=> 0
					],
					'shareanddownloadstatus'
					)
				->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    '',
                    [
                    'nullable => true',
                    ],
                    'created_at'
                )->addColumn(
                    'update_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    '',
                    [
                    'nullable => true',
                    ],
                    'update_at');
                ;

                $installer->getConnection()->createTable($table);
            }


            $installer->endSetup();
        }
    }
