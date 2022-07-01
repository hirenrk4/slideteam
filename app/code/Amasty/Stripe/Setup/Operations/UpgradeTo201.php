<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Setup\Operations;


use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo201
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createStripeOrderColumn($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function createStripeOrderColumn(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('sales_order');
        if ($setup->getConnection()->isTableExists($tableName) == true) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'stripe_checkout_message_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'=>255,
                    'nullable' => true,
                    'comment' => 'Stripe Subscription Id'
                ]
            );
        }
  
    }
}
