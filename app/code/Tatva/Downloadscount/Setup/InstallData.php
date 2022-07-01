<?php
namespace Tatva\Downloadscount\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Customer\Model\Customer;
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
    /**
     * InstallData constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }
    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        
        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $attributesToAdd = [];

        /********* BEGIN: Add attribute ********* */
        
        $attrCode = 'deactivate_captcha';
        $customerSetup->addAttribute(Customer::ENTITY, $attrCode, [
            'type' => 'int',
            'label' => 'Deactivate Captcha',
            'input' => 'select',  
            'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean', 
            'default' => '0',
            'required' => false,
            'visible' => true,
            'system' => false,
            'position' => 120,
            'sort_order' => 9,
            'filterable' => true,
            'comparable' => true,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => true,
        ]);
        $attributesToAdd[] = $attrCode;

        /********* END ********* */

        /** Add the attributes to the attribute set and the common forms */
        foreach ($attributesToAdd as $code) 
        {
            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $code);
            $attribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],
            ]);
            $attribute->save();
        }
        $setup->startSetup();
    }
}