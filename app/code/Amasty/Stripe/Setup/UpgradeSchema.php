<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stripe
 */


namespace Amasty\Stripe\Setup;

use Amasty\Stripe\Setup\Operations\UpgradeTo200;
use Amasty\Stripe\Setup\Operations\UpgradeTo201;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var UpgradeTo200
     */
    private $upgradeTo200;
    private $upgradeTo201;

    public function __construct(UpgradeTo200 $upgradeTo200,UpgradeTo201 $upgradeTo201)
    {
        $this->upgradeTo200 = $upgradeTo200;
        $this->upgradeTo201 = $upgradeTo201;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->upgradeTo200->execute($setup);
        }

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->upgradeTo201->execute($setup);
        }

        $setup->endSetup();
    }
}
