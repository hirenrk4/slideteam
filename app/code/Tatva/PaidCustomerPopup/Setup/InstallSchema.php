<?php
namespace Tatva\PaidCustomerPopup\Setup;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('paid_customer_additional_data')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('paid_customer_additional_data')
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
                'Paid customer Data Id'
                )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                'nullable => false',
                ],
                'Customer Id'
            )
            ->addColumn(
                'company',
                 \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable => false',
                ],
                'Company Name'
            )
            ->addColumn(
                'designation',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [
                'nullable => false',
                ],
                'Designation'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            );
            $installer->getConnection()->createTable($table);
            $installer->getConnection()->addIndex(
                $installer->getTable('paid_customer_additional_data'),
                $setup->getIdxName(
                    $installer->getTable('paid_customer_additional_data'),
                    ['company','designation'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['company','designation'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }        
    }
}
