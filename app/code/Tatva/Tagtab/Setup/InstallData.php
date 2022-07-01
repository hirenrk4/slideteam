<?php
namespace Tatva\Tagtab\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
    
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            
                'catalog_product',
                 'product_tags', 
                [
                    'group'         => 'Tags',
                    'input'         => 'textarea',
                    'type'          => 'text',
                    'label'         => 'Product Tags',
                    'backend'       => '',
                    'visible'       => 1,
                    'required'      => 0,
                    'user_defined' => 1,
                    'searchable' => 0,
                    'filterable' => 0,
                    'comparable'    => 0,
                    'visible_on_front' => 0,
                    'visible_in_advanced_search'  => 0,
                    'is_html_allowed_on_front' => 0,
                    'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
               ]
               );
    }
}