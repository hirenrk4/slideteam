<?php
namespace Tatva\Customer\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;


class AddCustomerAttribute implements DataPatchInterface, PatchRevertableInterface
{
   /** @var ModuleDataSetupInterface */
   private $moduleDataSetup;

   /** @var EavSetupFactory */
   private $customerSetupFactory;
   private $attributeSetFactory;
   /**
    * @param ModuleDataSetupInterface $moduleDataSetup
    * @param EavSetupFactory $eavSetupFactory
    */
   public function __construct(
       ModuleDataSetupInterface $moduleDataSetup,
       CustomerSetupFactory $customerSetupFactory,
       AttributeSetFactory $attributeSetFactory
   ) {
       $this->moduleDataSetup = $moduleDataSetup;
       $this->customerSetupFactory = $customerSetupFactory;
       $this->attributeSetFactory = $attributeSetFactory;
   }

   /**
    * {@inheritdoc}
    */
   public function apply()
   {
       $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'customer_plan_name',
            [
                'type' => 'varchar',
                'label' => 'Customer Plan Name',
                'input' => 'text',
                'source' => '',
                'required' => false,
                'visible' => true,
                'position' => 500,
                'system' => false,
                'backend' => ''
            ]
        );
         
        $attribute = $customerSetup->getEavConfig()->getAttribute('customer', 'customer_plan_name')->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'used_in_forms' => ['adminhtml_customer'],
            ]);
        $attribute->save();
 
        $this->moduleDataSetup->getConnection()->endSetup();
   }

   public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'customer_plan_name');
 
        $this->moduleDataSetup->getConnection()->endSetup();
    }

   /**
    * {@inheritdoc}
    */
   public static function getDependencies()
   {
       return [];
   }

   /**
    * {@inheritdoc}
    */
   public function getAliases()
   {
       return [];
   }

   /**
   * {@inheritdoc}
   */
   public static function getVersion()
   {
      return '1.0.4';
   }
}