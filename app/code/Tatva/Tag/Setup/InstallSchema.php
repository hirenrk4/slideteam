<?php
namespace Tatva\Tag\Setup;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{

        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('tag')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('tag')
            )
                ->addColumn(
                    'tag_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                        'auto_increment' => true
                    ],
                    'Tag Id'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [
                    'nullable => true',
                    ],
                    'Name'
                )
                 ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    6,
                    [
                    'nullable => false',
                    'default'=>0
                    ],
                    'status'
                )
                  ->addColumn(
                    'first_customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    6,
                    [
                    'unsigned' => true,
                    ],
                    'First Customer Id'
                )
                   ->addColumn(
                    'first_store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    6,
                    [
                    'unsigned'  => true,
                    ],
                    'First Store Id'
                )
                    ->addColumn(
                    'description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [
                    'nullable => true',
                    ],
                    'description'
                )
                  
                ->addForeignKey(
                      $installer->getFkName('tag', 'first_store_id', 'store', 'store_id'),
                      'first_store_id',
                      $installer->getTable('store'),
                      'store_id',
                      \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                );
               

            $installer->getConnection()->createTable($table);
            }



       //tag-realtion table
       if (!$installer->tableExists('tag_relation')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('tag_relation')
            )
                ->addColumn(
                    'tag_relation_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                        'auto_increment' => true
                    ],
                    'Tag Relation Id'
                )
                ->addColumn(
                    'tag_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ],
                    'Tag id'
                )
                 ->addColumn(
                    'customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ],
                    'customer_id'
                )
                 ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                    'unsigned'  => true,
			        'nullable'  => false,
			        'default'   => '0',
                    ],
                    'product_id'
                )
                  ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    6,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '1',
                    ],
                    'store_id'
                )
                   ->addColumn(
                    'active',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    6,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '1',
                    ],
                    'Active'
                )
                    ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [
                    
                    ],
                    'Created At'
                )
                 ->addIndex(
                    $installer->getIdxName(
                         'tag_relation',
                        ['tag_id', 'customer_id', 'product_id', 'store_id'], 
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    array('tag_id', 'customer_id', 'product_id', 'store_id'),
                     ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE])

                ->addIndex(
                    $installer->getIdxName(
                        'tag_relation',
                        ['product_id']),
                    array('product_id'))
                ->addIndex(
                    $installer->getIdxName(
                        'tag_relation',
                        ['tag_id']),
                    array('tag_id'))
                ->addIndex(
                    $installer->getIdxName(
                        'tag_relation',
                         ['customer_id']),
                    array('customer_id'))
                ->addIndex(
                    $installer->getIdxName(
                        'tag_relation',
                        ['store_id']),
                    array('store_id'))
                // ->addForeignKey(
                //     $installer->getFkName('tag_relation', 'customer_id', 'customer_entity', 'entity_id'),
                //     'customer_id', 
                //     $installer->getTable('customer_entity'), 
                //     'entity_id',
                //      \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                //     )
            ->addForeignKey(
                $installer->getFkName('tag_relation', 'product_id', 'catalog_product_entity', 'entity_id'),
                'product_id',
                 $installer->getTable('catalog_product_entity'), 
                 'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('tag_relation', 'store_id', 'store', 'store_id'),
                'store_id',
                 $installer->getTable('store'), 
                 'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('tag_relation', 'tag_id', 'tag', 'tag_id'),
                'tag_id', 
                $installer->getTable('tag'), 
                'tag_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Tag Relation');

            $installer->getConnection()->createTable($table);
            }


        //tag-realtion table
       if (!$installer->tableExists('tag_summary')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('tag_summary')
            )
                ->addColumn(
                    'tag_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                        'auto_increment' => true
                    ],
                    'Tag Id'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    6,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'default'   => '0',
                    ],
                    'Store Id'
                )
                 ->addColumn(
                    'customers',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ],
                    'customers'
                )
                  ->addColumn(
                    'products',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ],
                    'products'
                )
                   ->addColumn(
                    'uses',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ],
                    'Uses'
                )
                    ->addColumn(
                    'historical_uses',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ],
                    'historical_uses'
                ) ->addColumn(
                    'popularity',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ],
                    'popularity'
                ) ->addColumn(
                    'base_popularity',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'default'   => '0',
                    ],
                    'Base Popularity'
                )->addIndex(
                    $installer->getIdxName(
                        'tag_summary',
                         ['store_id']),
                    array('store_id'))
                ->addIndex(
                    $installer->getIdxName(
                        'tag_summary',
                        ['tag_id']),
                    array('tag_id'))
                ->addForeignKey(
                    $installer->getFkName('tag_summary', 'store_id', 'store', 'store_id'),
                    'store_id', 
                    $installer->getTable('store'), 
                    'store_id',
                     \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
            ->addForeignKey(
                $installer->getFkName('tag_summary', 'tag_id', 'tag', 'tag_id'),
                'tag_id',
                 $installer->getTable('tag'), 
                 'tag_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Tag Summary');
               

            $installer->getConnection()->createTable($table);
            }



       //tag_properties
       if (!$installer->tableExists('tag_properties')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('tag_properties')
            )
                ->addColumn(
                    'tag_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                        'auto_increment' => true
                    ],
                    'Tag Id'
                )
                  ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    6,
                    [
                    'unsigned'  => true,
                    'nullable'  => false,
                    'primary'   => true,
                    'default'   => '0',
                    ],
                    'store_id'
                )
                   ->addColumn(
                    'base_popularity',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                      'unsigned'  => true,
                       'nullable'  => false,
                       'default'   => '0',
                    ],
                    'base_popularity'
                )
               ->addIndex(
                    $installer->getIdxName(
                        'tag_properties',
                         ['store_id']),
                    array('store_id'))
                ->addForeignKey(
                    $installer->getFkName('tag_properties', 'store_id', 'store', 'store_id'),
                    'store_id', $installer->getTable('store'), 'store_id',
                   \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)
                ->addForeignKey(
                    $installer->getFkName('tag_properties', 'tag_id', 'tag', 'tag_id'),
                    'tag_id', $installer->getTable('tag'), 'tag_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE)
                ->setComment('Tag Properties');
               

            $installer->getConnection()->createTable($table);
            }

       $installer->endSetup();

    }
}
