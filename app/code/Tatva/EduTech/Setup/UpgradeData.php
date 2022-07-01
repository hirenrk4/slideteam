<?php

namespace Tatva\EduTech\Setup; 
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Eav\Setup\EavSetupFactory; 

class UpgradeData implements UpgradeDataInterface 
{
    public function __construct(
        EavSetupFactory $eavSetupFactory
        ) 
    { 
        $this->eavSetupFactory = $eavSetupFactory; 
    } 

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) { 
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.0.3', '<')) 
        {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'tax_class_id', 'is_visible', '0'); 
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'quantity_and_stock_status', 'is_visible', '0');
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'weight', 'is_visible', '0');
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'sku', 'frontend_class', 'validate-length maximum-length-150');
        } 
        $setup->endSetup(); 
    } 

}