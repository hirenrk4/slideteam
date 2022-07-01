<?php
namespace Tatva\Categoryattribute\Setup;

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
			
				'catalog_category',
				 'seo_description', 
				[
				    'group'         => 'General Information',
				    'input'         => 'textarea',
				    'type'          => 'text',
				    'label'         => 'Seo Description',
				    'backend'       => '',
				    'visible'       => 1,
				    'required'      => 0,
				    'user_defined' => 1,
				    'backend_type'   => 'text',
				    'global'        => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
			   ],
			   'visual_search', 
			   [
	                'type'              => 'int',
	                'label'             => 'Visual Search Category',
	                'input'             => 'select',
	                'class'             => '',
	                'source'            => 'eav/entity_attribute_source_boolean',
	                'global'            => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
	                'visible'           => true,
	                'required'          => false,
	                'user_defined'      => false,
	                'default'           => '0',
	                'searchable'        => false,
	                'filterable'        => false,
	                'comparable'        => false,
	                'visible_on_front'  => true,
	                'unique'            => false
                ],
                'sub_category_id',
                [
                    'type'              => 'varchar',
                    'label'             => 'Sub Category ID',
                    'input'             => 'text',
                    'class'             => '',
                    'global'            => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible'           => true,
                    'required'          => false,
                    'user_defined'      => false,
                    'searchable'        => false,
                    'filterable'        => false,
                    'comparable'        => false,
                    'visible_on_front'  => true,
                    'unique'            => false,
            ]

		);
	}
}