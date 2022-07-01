<?php
namespace Tatva\Customproductreview\Setup;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('slideteam_product_review')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('slideteam_product_review')
                )
            ->addColumn(
                'review_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                'identity' => true,
                'nullable' => false,
                'primary'  => true,
                'unsigned' => true,
                'auto_increment' => true
                ],
                'review_id'
                )
            ->addColumn(
                'review_detail',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '2M',
                [
                'nullable => false',
                ],
                'review_detail'
                );


            $installer->getConnection()->createTable($table);
        }


    }
}
