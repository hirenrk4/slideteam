<?php
namespace Tatva\Customer\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('2checkout_ins')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('2checkout_ins')
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
				'message_type',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				[
				'nullable' => false
				],
				'message_type'
				)
			->addColumn(
				'message_description',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[
				'nullable' => true,
				'default'=> NULL
				],
				'message_description'
				)
			->addColumn(
				'timestamp',
				\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
				null,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'timestamp'
				)
			->addColumn(
				'md5_hash',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[
				'nullable' => false
				],
				'md5_hash'
				)
			->addColumn(
				'message_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				11,
				[
				'nullable' => false,
				'default'=> 0
				],
				'message_id'
				)
			->addColumn(
				'key_count',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				4,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'key_count'
				)  
			->addColumn(
				'vendor_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				10,
				[
				'nullable' => false,
				'default'=> 0
				],
				'vendor_id'
				)
			->addColumn(
				'sale_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
				18,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'sale_id'
				)
			->addColumn(
				'sale_date_placed',
				\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
				null,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'sale_date_placed'
				)
			->addColumn(
				'vendor_order_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				15,
				[
				'nullable' => false,
				'default'=> 0
				],
				'vendor_order_id'
				)
			->addColumn(
				'invoice_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
				18,
				[
				'nullable' => false,
				'default'=> 0
				],
				'invoice_id'
				)
			->addColumn(
				'recurring',
				\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
				4,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'recurring'
				)
			->addColumn(
				'payment_type',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				100,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'payment_type'
				)
			->addColumn(
				'list_currency',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				3,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'list_currency'
				)
			->addColumn(
				'cust_currency',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				3,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'cust_currency'
				)
			->addColumn(
				'auth_exp',
				\Magento\Framework\DB\Ddl\Table::TYPE_DATE,
				null,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'auth_exp'
				)
			->addColumn(
				'fraud_status',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[
				'nullable' => true,
				'default'=> NULL
				],
				'fraud_status'
				)
			->addColumn(
				'invoice_status',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[
				'nullable' => true,
				'default'=> NULL
				],
				'invoice_status'
				)
			->addColumn(
				'invoice_list_amount',
				\Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
				10,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'invoice_list_amount'
				)
			->addColumn(
				'invoice_usd_amount',
				\Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
				'10,3',
				[
				'nullable' => true,
				'default'=> NULL
				],
				'invoice_usd_amount'
				)
			->addColumn(
				'invoice_cust_amount',
				\Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
				'10,3',
				[
				'nullable' => true,
				'default'=> NULL
				],
				'invoice_cust_amount'
				)
			->addColumn(
				'customer_first_name',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				100,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'customer_first_name'
				)
			->addColumn(
				'customer_last_name',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				100,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'customer_last_name'
				)
			->addColumn(
				'customer_name',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				100,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'customer_name'
				)
			->addColumn(
				'customer_email',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				200,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'customer_email'
				)
			->addColumn(
				'customer_phone',
				\Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
				15,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'customer_phone'
				)
			->addColumn(
				'customer_ip',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'customer_ip'
				)
			->addColumn(
				'customer_ip_country',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'customer_ip_country'
				)
			->addColumn(
				'bill_street_address',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[
				'nullable' => true,
				'default'=> NULL
				],
				'bill_street_address'
				)
			->addColumn(
				'bill_street_address2',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[
				'nullable' => true,
				'default'=> NULL
				],
				'bill_street_address'
				)
			->addColumn(
				'bill_city',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'bill_city'
				)
			->addColumn(
				'bill_state',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'bill_state'
				)
			->addColumn(
				'bill_postal_code',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'bill_postal_code'
				)
			->addColumn(
				'bill_country',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				4,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'bill_country'
				)
			->addColumn(
				'ship_status',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[
				'nullable' => true,
				'default'=> NULL
				],
				'ship_status'
				)
			->addColumn(
				'ship_tracking_number',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'ship_tracking_number'
				)
			->addColumn(
				'ship_name',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				100,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'ship_name'
				)
			->addColumn(
				'ship_street_address',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[
				'nullable' => true,
				'default'=> NULL
				],
				'ship_street_address'
				)
			->addColumn(
				'ship_street_address2',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[
				'nullable' => true,
				'default'=> NULL
				],
				'ship_street_address2'
				)
			->addColumn(
				'ship_street_address2',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[
				'nullable' => true,
				'default'=> NULL
				],
				'ship_street_address2'
				)
			->addColumn(
				'ship_city',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'ship_city'
				)
			->addColumn(
				'ship_state',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'ship_state'
				)
			->addColumn(
				'ship_postal_code',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'ship_postal_code'
				)
			->addColumn(
				'ship_country',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'ship_country'
				)
			->addColumn(
				'item_count',
				\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
				4,
				[
				'nullable' => true,
				'default'=> NULL
				],
				'item_count'
				)
			->addColumn(
				'created_at',
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
				null,
				[
				'nullable' => true,
				'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
				],
				'created_at'
				)
			;

			$installer->getConnection()->createTable($table);
		}

//Table paypal_result


		if (!$installer->tableExists('paypal_result')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('paypal_result')
				)
			->addColumn(
				'id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				15,
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
				'increment_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => false
				],
				'increment_id'
				)
			->addColumn(
				'paypal_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => false
				],
				'paypal_id'
				)
			->addColumn(
				'test_ipn',
				\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
				2,
				[
				'nullable' => false,
				'default' => 0
				],
				'test_ipn'
				)
			->addColumn(
				'txn_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'nullable' => true,
				'default' => NULL
				],
				'txn_id'
				)
			->addColumn(
				'recurring',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				10,
				[
				'nullable' => false
				],
				'recurring'
				)
			->addColumn(
				'subscription_date',
				\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
				null,
				[
				'nullable' => false
				],
				'subscription_date'
				)
			->addColumn(
				'reattempt',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				10,
				[
				'default' => NULL
				],
				'reattempt'
				)
			->addColumn(
				'period',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				10,
				[
				'default' => NULL
				],
				'period'
				)
			->addColumn(
				'amount',
				\Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'10,2',
				[
				'nullable' => false
				],
				'amount'
				)
			->addColumn(
				'trasaction_type',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'default' => NULL
				],
				'trasaction_type'
				)
			->addColumn(
				'result_data_from',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				50,
				[
				'default' => NULL
				],
				'result_data_from'
				)
			->addColumn(
				'success',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				2,
				[
				'default' => NULL
				],
				'success'
				)
			->addColumn(
				'success',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				2,
				[
				'default' => NULL
				],
				'success'
				)
			->addColumn(
				'success',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				2,
				[
				'default' => NULL
				],
				'success'
				)
			->addColumn(
				'payers_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				100,
				[
				'default' => NULL
				],
				'payers_id'
				)
			->addColumn(
				'sellers_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				100,
				[
				'default' => NULL
				],
				'sellers_id'
				)
			->addColumn(
				'transaction_log',
				\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'2M',
				[],
				'transaction_log'
				)
			;

			$installer->getConnection()->createTable($table);
		}

		$installer->endSetup();
	}
}
