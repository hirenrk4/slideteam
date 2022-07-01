<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RecurringPayment\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallData implements InstallDataInterface
{
    /**
     * Eav setup factory
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $this->installEntities($setup);
        $installer->endSetup();
    }

    /**
     * Default entites and attributes
     *
     * @param array|null $entities
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function installEntities($setup)
    {
        $attributes = [
            'is_recurring'       => [
                'type'                       => 'int',
                'label'                      => 'Enable Recurring Payment',
                'input'                      => 'select',
                'source'                     => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'required'                   => false,
                'note'                       =>
                    'Products with recurring payment participate in catalog as nominal items.',
                'sort_order'                 => 1,
                'apply_to'                   => 'simple,virtual',
                'is_configurable'            => false,
                'group'                      => 'Recurring Payment',
            ],
            'recurring_payment'  => [
                'type'                       => 'text',
                'label'                      => 'Recurring Payment',
                'input'                      => 'text',
                'backend'                    => 'Magento\RecurringPayment\Model\Product\Attribute\Backend\Recurring',
                'required'                   => false,
                'sort_order'                 => 2,
                'apply_to'                   => 'simple,virtual',
                'is_configurable'            => false,
                'group'                      => 'Recurring Payment'                
            ]
        ];
        
        $eavSetup = $this->eavSetupFactory->create();
        foreach ($attributes as $attrCode => $attr) {
            $eavSetup->addAttribute('catalog_product', $attrCode, $attr);
        }

        // From data-install starts --------------------------------------------------------------
        
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'Default');

        $groupName = 'Recurring Payment';
        $eavSetup->updateAttributeGroup($entityTypeId, $attributeSetId, $groupName, 'sort_order', 41);
        $eavSetup->updateAttributeGroup($entityTypeId, $attributeSetId, $groupName, 'attribute_group_code', 'recurring-payment');
        $eavSetup->updateAttributeGroup($entityTypeId, $attributeSetId, $groupName, 'tab_group_code', 'advanced');

        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'is_recurring');
        $eavSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, 'recurring_payment');

        $connection = $setup->getConnection();
        $adminRuleTable = $setup->getTable('authorization_rule');
        $connection->update(
            $adminRuleTable,
            array('resource_id' => 'Magento_RecurringPayment::recurring_payment'),
            array('resource_id = ?' => 'Magento_Sales::recurring_payment')
        );

        // From data-install ends --------------------------------------------------------------

    }
}
