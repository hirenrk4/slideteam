<?php
namespace Tatva\PaidCustomerPopup\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class InstallData implements InstallDataInterface
{
	private $customerSetupFactory;

    private $attributeSetFactory;

	public function __construct(
		CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
	){
		$this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
	}

	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$setup->startSetup();

        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(
        	\Magento\Customer\Model\Customer::ENTITY,
            'second_login',
            [
                'type'         => 'varchar',
				'label'        => 'Second Login Count',
				'input'        => 'text',
				'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'is_searchable_in_grid' => false,
				'required'     => false,
				'default'      => 0,
				'system'       => false,
            ]
        );
        $Attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'second_login')
        ->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer']
        ]);
        $Attribute->save();

        $setup->endSetup();
	}
}