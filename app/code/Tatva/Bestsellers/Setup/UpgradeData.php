<?php

namespace Tatva\Bestsellers\Setup; 
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
        if (version_compare($context->getVersion(), '0.3.0', '<')) 
        {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'most_popular',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Most Popular',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'group' => 'General',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => true,
                'filterable' => true,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
            );
        } 
        $setup->endSetup(); 
    } 

}