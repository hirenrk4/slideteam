<?php
namespace Tatva\Catalog\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    private $categorySetupFactory;

    private $eavSetupFactory;

    public function __construct(CategorySetupFactory $categorySetupFactory,EavSetupFactory $eavSetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup,ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '0.1.10', '<')) 
        {
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

            $categorySetup->addAttribute('catalog_category','visual_search',
                array(
                    'type' => 'int',
                    'label' => 'Visual Search Category',
                    'input' => 'select',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'sort_order' => 1,
                    'required' => false,
                    'default' => '0',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General',
                ));

            $categorySetup->addAttribute('catalog_category','sub_category_id',
                array(
                    'type' => 'varchar',
                    'label' => 'Sub Category ID',
                    'input' => 'text',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'sort_order' => 2,
                    'required' => false,
                    'default' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General',
                ));

            $categorySetup->addAttribute('catalog_category','category_header',
                array(
                    'type' => 'varchar',
                    'label' => 'Category Header',
                    'input' => 'text',
                    'sort_order' => 3,
                    'required' => false,
                    'default' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General',
                )); 

            $categorySetup->addAttribute('catalog_category','complete_decks',
                array(
                    'type' => 'int',
                    'label' => 'Complete Decks Category',
                    'input' => 'select',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'sort_order' => 4,
                    'required' => false,
                    'default' => '0',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General',
                ));

            $categorySetup->addAttribute('catalog_category','move_to_other_category',
                array(
                    'type' => 'int',
                    'label' => 'Show in Other Category instead of individual',
                    'input' => 'select',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'sort_order' => 5,
                    'required' => false,
                    'default' => '0',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General',
                ));

        }
        if (version_compare($context->getVersion(), '0.1.30', '<')) 
        {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'google_slide_link', [
                'type'                       => 'varchar',
                'label'                      => 'Google Slide Link',
                'input'                      => 'text',
                'visible'                    => true,
                'required'                   => false,
                'user_defined'               => false,
                'default'                    => '',
                'global'                     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable'                 => false,
                'filterable'                 => false,
                'comparable'                 => false,
                'visible_on_front'           => true,
                'used_in_product_listing'    => false,
                'unique'                     => false,
                'apply_to'                   => ''
            ]);
        }
        if (version_compare($context->getVersion(), '1.0.0', '<')) 
        {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'pricing_type', [
                'type'                       => 'int',
                'label'                      => 'Pricing Type',
                'input'                      => 'select',
                'visible'                    => true,
                'required'                   => true,
                'user_defined'               => true,
                'default'                    => '',
                'source'                     => 'Tatva\Catalog\Model\Product\Attribute\Source\PricingType',
                'global'                     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable'                 => false,
                'filterable'                 => false,
                'comparable'                 => false,
                'visible_on_front'           => true,
                'used_in_product_listing'    => false,
                'unique'                     => false,
                'apply_to'                   => 'virtual'
            ]);
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'pricing_product_type', [
                'type'                       => 'int',
                'label'                      => 'Pricing Product Type',
                'input'                      => 'select',
                'visible'                    => true,
                'required'                   => false,
                'user_defined'               => true,
                'default'                    => '',
                'source'                     => 'Tatva\Catalog\Model\Product\Attribute\Source\PricingProduct',
                'global'                     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable'                 => false,
                'filterable'                 => false,
                'comparable'                 => false,
                'visible_on_front'           => true,
                'used_in_product_listing'    => false,
                'unique'                     => false,
                'apply_to'                   => 'virtual'
            ]);

        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) 
        {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'is_new',[
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'New Image',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => true,
                    'default' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'group'=>"General",
                    'attribute_set' => 'Migration_Default',
                    'apply_to' => 'downloadable,simple'
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) 
        {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'banner_text', [
                'type'                       => 'text',
                'label'                      => 'Banner Text',
                'input'                      => 'text',
                'visible'                    => true,
                'required'                   => false,
                'user_defined'               => false,
                'default'                    => '',
                'global'                     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'searchable'                 => false,
                'filterable'                 => false,
                'comparable'                 => false,
                'visible_on_front'           => true,
                'used_in_product_listing'    => false,
                'unique'                     => false,
                'apply_to'                   => ''
            ]);
            $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'banner_url', [
                'type'                       => 'text',
                'label'                      => 'Banner URL',
                'input'                      => 'text',
                'visible'                    => true,
                'required'                   => false,
                'user_defined'               => false,
                'default'                    => '',
                'global'                     => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'searchable'                 => false,
                'filterable'                 => false,
                'comparable'                 => false,
                'visible_on_front'           => true,
                'used_in_product_listing'    => false,
                'unique'                     => false,
                'apply_to'                   => ''
            ]);
        }
        if (version_compare($context->getVersion(), '1.0.5', '<')) 
        {
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

            $categorySetup->addAttribute('catalog_category','show_in_frontend_listing',
                array(
                    'type' => 'int',
                    'label' => 'Show In Frontend Listing',
                    'input' => 'select',
                    'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                    'sort_order' => 6,
                    'required' => false,
                    'default' => '0',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General',
                ));
        }
        if (version_compare($context->getVersion(), '1.0.6', '<')) 
        {
            $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

            $categorySetup->addAttribute('catalog_category','seo_url',
                array(
                    'type' => 'varchar',
                    'label' => 'SEO URL',
                    'input' => 'text',
                    'source' => '',
                    'sort_order' => 7,
                    'required' => false,
                    'default' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'General',
                ));
        }
        $setup->endSetup();
    }   
}