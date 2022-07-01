<?php
namespace Tatva\Customerdashboard\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
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
            if (!$installer->tableExists('tatva_customer_dashboard_history')) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('tatva_customer_dashboard_history')
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
            $triggerName = 'Customer_Dashboard_Deleted_Trigger';
            $event = 'DELETE';

            /** @var \Magento\Framework\DB\Ddl\Trigger $trigger */
            $trigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(\Magento\Framework\DB\Ddl\Trigger::TIME_BEFORE)
                ->setEvent($event)
                ->setTable($setup->getTable('tatva_customer_dashboard'));
            
            $trigger->addStatement($this->buildStatement($event));

            $setup->getConnection()->dropTrigger($trigger->getName());
            $setup->getConnection()->createTrigger($trigger);
        }   
        $installer->endSetup(); 
    }
    protected function buildStatement($event, $changelog = null)
    {
        switch ($event) {
            case \Magento\Framework\DB\Ddl\Trigger::EVENT_DELETE:
                $triggerSql = "
                        INSERT INTO tatva_customer_dashboard_history (`customer_id`,`customer_email`,`page_uri`,`event`,`created_time`) SELECT OLD.customer_id,OLD.customer_email,OLD.page_uri,OLD.event,OLD.created_time
                        ";
                return $triggerSql;
            default:
                return '';
        }
    }


}
?>