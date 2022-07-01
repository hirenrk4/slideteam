<?php

namespace Tatva\Questionnaire\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
		if (!$installer->tableExists('questionnaire'))
		{
			$table = $installer->getConnection()->newTable(
	            $installer->getTable('questionnaire')
	        )->addColumn('id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, 'auto_increment' => true], 'id')
			->addColumn('name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Name')
			->addColumn('phone', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, ['nullable' => false, 'default' => ''], 'Phone')
			->addColumn('call_flag', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 30, ['nullable' => false, 'default' => ''], 'Call Flag')
			->addColumn('email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Email')
			->addColumn('number_of_slides', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 255, ['nullable' => false, 'default' => '0'], 'No. of Slide')
			->addColumn('style_option', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Style Option')
			->addColumn('template_or_diagram_details', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Template or Diagram Details')
			->addColumn('description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Description')
			->addColumn('client_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11, ['nullable' => false, 'default' => '0'], 'Client Id')
			->addColumn('questionnaire_file', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Questionnaire File')
			->addIndex(
				$setup->getIdxName(
					$installer->getTable('questionnaire'),
					['name','phone','call_flag','email','style_option','template_or_diagram_details','description','questionnaire_file'],
					AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['name','phone','call_flag','email','style_option','template_or_diagram_details','description','questionnaire_file'], 
				['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
			)->setComment('Questionnaire Table');

            $installer->getConnection()->createTable($table);            
        }
        $installer->endSetup();
    }

}
