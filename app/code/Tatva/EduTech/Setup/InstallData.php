<?php

namespace Tatva\EduTech\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Eav\Setup\EavSetupFactory;

class InstallData implements InstallDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory) {

        $this->eavSetupFactory = $eavSetupFactory;

    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context){

        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        // Complete Curriculum
		$eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'complete_curriculum' ,
             [
                'group' => 'Edu Tech',
                'type' => 'text',
                'label' => 'Complete Curriculum',
                'input' => 'textarea',
                'sort_order' => 10,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable' => true,
                'comparable' => true,
                'required' => false,
                'visible' => false,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );
        // Sample Instructore Notes
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'sample_instructor_notes' ,
             [
                'group' => 'Edu Tech',
                'type' => 'text',
                'label' => 'Sample Instructore Notes',
                'input' => 'textarea',
                'sort_order' => 11,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable' => true,
                'comparable' => true,
                'required' => false,
                'visible' => false,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );
        // FAQs
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'product_faq' ,
             [
                'group' => 'Edu Tech',
                'type' => 'text',
                'label' => 'FAQs',
                'input' => 'textarea',
                'sort_order' => 12,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable' => true,
                'comparable' => true,
                'required' => false,
                'visible' => false,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

        // What is it
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'edu_top1' ,
             [
                'group' => 'Edu Tech',
                'type' => 'text',
                'label' => 'What is it',
                'input' => 'textarea',
                'sort_order' => 13,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable' => true,
                'comparable' => true,
                'required' => false,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

        // Who is it for 
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'edu_top2' ,
             [
                'group' => 'Edu Tech',
                'type' => 'text',
                'label' => 'Who is it for',
                'input' => 'textarea',
                'sort_order' => 14,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable' => true,
                'comparable' => true,
                'required' => false,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

        // Why EduDecks
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'edu_top3' ,
             [
                'group' => 'Edu Tech',
                'type' => 'text',
                'label' => 'Why EduDecks',
                'input' => 'textarea',
                'sort_order' => 15,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable' => true,
                'comparable' => true,
                'required' => false,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

        // Trainers
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'trainers_tab' ,
             [
                'group' => 'Edu Tech',
                'type' => 'text',
                'label' => 'Trainers Tab',
                'input' => 'textarea',
                'sort_order' => 15,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable' => true,
                'comparable' => true,
                'required' => false,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

        // Teachers
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'teachers_tab' ,
             [
                'group' => 'Edu Tech',
                'type' => 'text',
                'label' => 'Teachers Tab',
                'input' => 'textarea',
                'sort_order' => 15,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable' => true,
                'comparable' => true,
                'required' => false,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

        // trainers-teachers-list
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'trainers_teachers_list' ,
             [
                'group' => 'Edu Tech',
                'type' => 'text',
                'label' => 'Trainers Teachers List',
                'input' => 'textarea',
                'sort_order' => 15,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'searchable' => true,
                'comparable' => true,
                'required' => false,
                'wysiwyg_enabled' => true,
                'is_html_allowed_on_front' => true,
                'visible_in_advanced_search' => true,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
            ]
        );

            
    }
}
?>