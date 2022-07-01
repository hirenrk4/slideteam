<?php

namespace Tatva\SLIFeed\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    protected $triggerFactory;

    public function __construct(
        \Magento\Framework\DB\Ddl\TriggerFactory $triggerFactory
    )     
    {
        $this->triggerFactory = $triggerFactory;
    }
    
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) 
    {
        //Your code for upgrade data base
        $installer = $setup;
        $installer->startSetup();
        
        if(version_compare($context->getVersion(), '1.0.2', '<')) {
            $triggerName = 'Catalog_Deleted_Trigger';
            $event = 'DELETE';

            /** @var \Magento\Framework\DB\Ddl\Trigger $trigger */
            $trigger = $this->triggerFactory->create()
                ->setName($triggerName)
                ->setTime(\Magento\Framework\DB\Ddl\Trigger::TIME_BEFORE)
                ->setEvent($event)
                ->setTable($setup->getTable('catalog_product_entity'));
            
            $trigger->addStatement($this->buildStatement($event));

            $setup->getConnection()->dropTrigger($trigger->getName());
            $setup->getConnection()->createTrigger($trigger);

            $installer->endSetup();
        }
        
    }
    protected function buildStatement($event, $changelog = null)
    {
        switch ($event) {
            case \Magento\Framework\DB\Ddl\Trigger::EVENT_DELETE:
                $triggerSql = "
                        SET @productId = OLD.entity_id;
                        SET @productQuery = (SELECT cp.value FROM catalog_product_entity_varchar cp 
                                                WHERE cp.entity_id = @productId 
                                                AND cp.attribute_id = 88 AND cp.store_id = 0);
                        INSERT INTO catalog_product_entity_deleted_log
                            (entity_id,sku,url_key)
                            VALUES (@productId,OLD.sku,@productQuery);
                        ";
                return $triggerSql;
            default:
                return '';
        }
    }
}