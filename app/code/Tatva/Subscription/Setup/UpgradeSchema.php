<?php
namespace Tatva\Subscription\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
     protected $triggerFactory;
     public function __construct(
        \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory
    )     
    {
        $this->triggerFactory = $triggerFactory;
    }
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
             $triggerName = 'after_download_history';
            $event = 'INSERT';

                 $trigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(\Magento\Framework\DB\Ddl\Trigger::TIME_AFTER)
                ->setEvent($event)
                ->setTable($setup->getTable('productdownload_history_log'));
            
            $trigger->addStatement($this->buildStatement($event));

            $setup->getConnection()->dropTrigger($trigger->getName());
            $setup->getConnection()->createTrigger($trigger);

          $installer->getConnection()->addColumn(
                $installer->getTable('product_import_details'),
                'product_categories',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment'=>'product_categories'
                ]
            );
          $installer->getConnection()->addColumn(
                $installer->getTable('product_import_details'),
                'product_url',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment'=>'product_url'
                ]
            );
            $installer->getConnection()->addColumn(
                $installer->getTable('product_import_details'),
                'product_download',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length'=>11,
                    'default'=>0,
                    'nullable' => true,
                    'comment'=>'product_download'
                ]
            )
          ;
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $tableName = $setup->getTable('subscription_history');
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('subscription_history'),
                    'subscription_start_date',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Subscription Start Date'
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('subscription_history'); 
            $sql = "ALTER TABLE " . $tableName . " CHANGE `status_success` `status_success` ENUM('','Paid','Unsubscribed','Requested Unsubscription','Failed','Processing','Cancelled') NULL";
            $connection->query($sql);
        }


        if (version_compare($context->getVersion(), '1.0.5', '<')) {
          $installer->getConnection()->addColumn(
                $installer->getTable('subscription_history'),
                'subscription_title',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Subscription Title'
                ]
            );
          $installer->getConnection()->addColumn(
                $installer->getTable('subscription_history'),
                'subscription_detail',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Subscription Detail'
                ]
            );
        }

        if(version_compare($context->getVersion(), '1.0.6', '<')){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('subscription_history'); 
            $sql = "ALTER TABLE " . $tableName . " CHANGE `status_success` `status_success` ENUM('','Paid','Unsubscribed','Requested Unsubscription','Failed','Processing','Cancelled','Refunded') NULL";
            $connection->query($sql);
        }

        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable('subscription_history'),
                'unsubscribe_order',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'default'=> 0,
                    'nullable' => true,
                    'comment' => 'Unsubscribe Order'
                ]
            );
        }
        
        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('subscription_invitation')
                )
            ->addColumn(
                'invitation_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                'identity' => true,
                'nullable' => false,
                'primary'  => true,
                'unsigned' => true,
                'auto_increment' => true
                ],
                'invitation_id'
                )
            ->addColumn(
                'parent_customer',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                'nullable => false',
                'default'=> 0
                ],
                'parent_customer'
                )
            ->addColumn(
                'child_customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                'nullable => false',
                'default'=> 0
                ],
                'child_customer_id'
                )
            ->addColumn(
                'customer_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '255',
                [
                'nullable => false',
                'default'=> ''
                ],
                'customer_email'
                )
            ->addColumn(
                'created_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                '255',
                [
                'nullable => false',
                'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                ],
                'customer_email'
                );
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addColumn(
                $installer->getTable('subscription_history'),
                'parent_customer_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length'=>11,
                    'default' => null,
                    'nullable' => true,
                    'comment' => 'parent_customer_id'
                ]
            );
        }
        $installer->endSetup(); 
    }
      protected function buildStatement($event, $changelog = null)
    {
        switch ($event) {
            case \Magento\Framework\DB\Ddl\Trigger::EVENT_INSERT:
                $triggerSql = "UPDATE product_import_details SET product_download = (product_download+1)
                                where productsku = (SELECT sku from catalog_product_entity where entity_id= New.product_id)";
                return $triggerSql;
            default:
                return '';
        }
    }


}
?>