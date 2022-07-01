<?php
namespace Tatva\Customer\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $customerSetupFactory;

    private $attributeSetFactory;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        if (version_compare($context->getVersion(), '1.0.3', '<')) 
        {    
            $customerSetup->addAttribute(
                    \Magento\Customer\Model\Customer::ENTITY,
                    'contact_status',                    
                    [
                        'type' => 'varchar',
                        'label' => 'Control(1)/Variation(0)',
                        'input' => 'text',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => true,
                        'is_filterable_in_grid' => true,
                        'is_searchable_in_grid' => true,
                        'position' => 103,
                        'required' => false,
                        'system' => false
                    ]
                );
            $Attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'contact_status')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer','customer_account_edit','customer_account_create'],
            ]);
            $Attribute->save();
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) 
        {    
            $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY,'contact_status');
        }

        if (version_compare($context->getVersion(), '1.0.5', '<'))
        {
            $customerSetup->addAttribute(
                    \Magento\Customer\Model\Customer::ENTITY,
                    'customer_country_code',
                    [
                        'type' => 'varchar',
                        'label' => 'Customer Country Code',
                        'input' => 'hidden',
                        'is_used_in_grid' => false,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => false,
                        'position' => 103,
                        'required' => false,
                        'system' => false
                    ]
                );
            $Attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'customer_country_code')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId
            ]);
            $Attribute->save();
        }

        if (version_compare($context->getVersion(), '1.0.8', '<')) 
        {    
            $customerSetup->addAttribute(
                    \Magento\Customer\Model\Customer::ENTITY,
                    'enable_multilogin',                    
                    [
                        'type' => 'int',
                        'label' => 'Enable Multilogin',
                        'input' => 'boolean',
                        'is_used_in_grid' => true,
                        'position' => 103,
                        'required' => false,
                        'system' => false
                    ]
                );
            $Attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'enable_multilogin')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer','customer_account_edit','customer_account_create'],
            ]);
            $Attribute->save();
        }

        if (version_compare($context->getVersion(), '1.0.12', '<'))
        {
            $customerSetup->addAttribute(
                    \Magento\Customer\Model\Customer::ENTITY,
                    'ipstack_customer_country_code',
                    [
                        'type' => 'varchar',
                        'label' => 'Ip Stack Customer Country Code',
                        'input' => 'hidden',
                        'is_used_in_grid' => true,
                        'is_visible_in_grid' => false,
                        'is_filterable_in_grid' => false,
                        'is_searchable_in_grid' => false,
                        'position' => 104,
                        'required' => false,
                        'system' => false
                    ]
                );
            $Attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'ipstack_customer_country_code')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId
            ]);
            $Attribute->save();
        }

        $setup->endSetup();
    }
}