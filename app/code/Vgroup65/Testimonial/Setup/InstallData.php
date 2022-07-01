<?php
namespace Vgroup65\Testimonial\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
    class InstallData implements InstallDataInterface
    {

        public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
        {
            $setup->startSetup();

            $attribute_name= [
                [
                    'display_type' => 'list',
                    'no_of_testimonial' => '0',
                    'auto_rotate' => 1,
                    'top_menu_link' => 'Testimonial',
                    'display_top_menu' => 1
                ],

            ];
            $setup->getConnection()->insertMultiple($setup->getTable('testimonial_configuration'), $attribute_name);
            $setup->endSetup();
        }
    }