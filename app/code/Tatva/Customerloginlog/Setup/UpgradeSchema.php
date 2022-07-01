<?php
/**
 * Etatvasoft Productattachment
 *
 * @category Etatvasoft
 * @package  Etatvasoft_Productattachment
 * @author   Etatvasoft <magento@etatvasoft.com>
 * @license  http://tatvasoft.com  Open Software License (OSL 3.0)
 * @link     http://tatvasoft.com
 */
namespace Tatva\Customerloginlog\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 *
 * @category Etatvasoft
 * @package  Etatvasoft_Productattachment
 * @author   Etatvasoft <magento@etatvasoft.com>
 * @license  http://tatvasoft.com  Open Software License (OSL 3.0)
 * @link     http://tatvasoft.com
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema, add sort_order
     *
     * @param SchemaSetupInterface   $setup   Setup
     * @param ModuleContextInterface $context Context
     *
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
    	$setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            
            $table = $setup->getConnection()->newTable(
                $setup->getTable('customerloginipcount')
            )->addColumn(
                'customerloginipcount_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Customerloginipcount ID'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Customer Id'
            )->addColumn(
                'timestamp',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false,'unsigned' => true],
                'Timestamp'
            )->setComment('Customerloginipcount Table');
            $setup->getConnection()->createTable($table);
			
            
        }  
		
		if (version_compare($context->getVersion(), '0.0.3') < 0) {
		 	
			$connection = $setup->getConnection();
			$tableName = "customerloginipcount";
			//$primaryKeyFields = "customer_id";
			$setup->getConnection()->addIndex(
            	$tableName,
                $connection->getIndexName($tableName, ['customer_id']),
                ['customer_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );   			
		}   
		
		if (version_compare($context->getVersion(), '0.0.4') < 0) {
		 	
			$connection = $setup->getConnection();
			$tableName = "customerloginlog";
			//$primaryKeyFields = "customer_id";
			$setup->getConnection()->addIndex(
            	$tableName,
                $connection->getIndexName($tableName, ['customer_id']),
                ['customer_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
            );   			
		}   

        if (version_compare($context->getVersion(), '0.0.5') < 0) 
        {
            $setup->getConnection()->addColumn(
                $setup->getTable('customerloginipcount'),
                'download_count',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Download Items Count'
                ]
            );
        }
		$setup->endSetup();   
    }
}
