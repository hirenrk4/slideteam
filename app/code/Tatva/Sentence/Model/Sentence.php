<?php


namespace Tatva\Sentence\Model;

use Magento\Framework\Model\AbstractModel;

class Sentence extends AbstractModel
{   
	const CACHE_TAG = 'sentence';
	
	protected $_eavAttribute;

	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Tatva\Sentence\Model\ResourceModel\Sentence\CollectionFactory  $sentenceCollectionFactory,
		\Magento\Framework\App\ResourceConnection $resourceConnection,
		\Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
		\Magento\Catalog\Model\Product\Action $catalogProductAction,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
		) {
		$this->sentenceCollectionFactory = $sentenceCollectionFactory;
		$this->resourceConnection = $resourceConnection;
		$this->catalogProductAction = $catalogProductAction;
		$this->productCollectionFactory = $productCollectionFactory;
		$this->_storeManager = $storeManager;
		$this->_eavAttribute = $eavAttribute;
		parent::__construct(
			$context,
			$registry,
			$resource,
			$resourceCollection,
			$data
			);
	}
	protected function _construct()
	{
		$this->_init
		('Tatva\Sentence\Model\ResourceModel\Sentence');
	}


	public function updateProductSentences()
	{
		$eavAttribute = $this->_eavAttribute;
		$this->sentence=$this->sentenceCollectionFactory->create();
		$this->productCollection=$this->productCollectionFactory->create();

		$code1 = $eavAttribute->getIdByCode('catalog_product', 'sentence1');
		$code2 = $eavAttribute->getIdByCode('catalog_product', 'sentence2');
		$code = array($code1, $code2);
		$adminStore = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
		$product_collection = $this->productCollection
		->addAttributeToSelect('sentence1')->addAttributeToSelect('sentence2')
		->addAttributeToSelect('name')
		->joinAttribute('sentence1', 'catalog_product/sentence1', 'entity_id', null, 'left', $adminStore)
		->joinAttribute('sentence2', 'catalog_product/sentence2', 'entity_id', null, 'left', $adminStore);
		$product_collection->getSelect()->where("`at_sentence1`.`value` is null AND `at_sentence2`.`value` is null")
		->limit(100);
		$production_data = array();
		$number_of_usage = 0;
		$connection = $this->resourceConnection->getConnection('core_write');
		foreach ($product_collection as $product)
		{


			$sentence_id = array();
			$sentence_name_before = array();
			$sentence_collection_less = '';
			$product_name = $product['name'];
			$entity_id = $product['entity_id'];

			$sentence_collection_count = $this->getCollection();
            // Get Maximum Number of Count from column 'number_of_usage_product'
			$sentence_collection_count->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('MAX(number_of_usage_product) as number_of_usage_product');

			foreach ($sentence_collection_count as $c)
			{
				$max_count = $c['number_of_usage_product'];
			}
			if ($max_count > 0)
			{
				$temp = $max_count - 1;
				$sentence_collection = $this->getCollection()->addFieldToFilter('number_of_usage_product', $temp)->setPageSize(2);
			}
			else
				$sentence_collection = $this->getCollection()->addFieldToFilter('number_of_usage_product', $max_count)->setPageSize(2);

            // Get Random Collection
			$sentence_collection->getSelect()->order(new \Zend_Db_Expr('RAND()'));

			if ($sentence_collection->count() < 2)
			{
				$less = 2 - $sentence_collection->count();
				$sentence_collection_less =$this->getCollection()->addFieldToFilter('number_of_usage_product', $max_count)->setPageSize($less);
				$sentence_collection_less->getSelect()->order(new \Zend_Db_Expr('RAND()'));
			}

			if (is_object($sentence_collection_less) && $sentence_collection_less->getSize() > 0)
				$sentence_name_before = array_merge_recursive($sentence_collection->getData(), $sentence_collection_less->getData());
			else
			{
				$sentence_name_before = $sentence_collection->getData();
			}


			for ($i = 0; $i < count($sentence_name_before); $i++)
				$sentence_id[] = $sentence_name_before[$i]['sentence_id'];

			try
			{

				$value_sentence1 = "Select value_id from catalog_product_entity_text where attribute_id = '" . $code1 . "' and entity_id = '" . $entity_id . "'";
				$value_sentence2 = "Select value_id from catalog_product_entity_text where attribute_id = '" . $code2 . "' and entity_id = '" . $entity_id . "'";

				$buffer_sentence1 = $connection->fetchAll($value_sentence1);

				$sentence = $sentence_name_before[0]['sentence'];
				$sentence = str_replace('PT&S', $product_name, $sentence);

				if (count($buffer_sentence1) > 0)
				{

					$sql = "UPDATE `catalog_product_entity_text` SET `value` = '" . addslashes($sentence) . "' WHERE entity_id = " . $entity_id . " and attribute_id = " . $code1 . ";";
					$connection->query($sql);
				}
				else
				{
					$sql = "INSERT INTO catalog_product_entity_text(entity_type_id ,attribute_id , store_id ,entity_id,value) VALUES ('4','" . $code1 . "','0','" . $entity_id . "','" . addslashes($sentence) . "');";
					$connection->query($sql);
				}

				$buffer_sentence2 = $connection->fetchAll($value_sentence2);

				$sentence = $sentence_name_before[1]['sentence'];
				$sentence = str_replace('PT&S', $product_name, $sentence);

				if (count($buffer_sentence2) > 0)
				{
					$sql = "UPDATE `catalog_product_entity_text` SET `value` = '" . addslashes($sentence) . "' WHERE entity_id = " . $entity_id . " and attribute_id = " . $code2 . ";";
					$connection->query($sql);
				}
				else
				{
					$sql = "INSERT INTO catalog_product_entity_text(entity_type_id ,attribute_id , store_id ,entity_id,value) VALUES ('4','" . $code2 . "','0','" . $entity_id . "','" . addslashes($sentence) . "');";
					$connection->query($sql);
				}
				$sql = "UPDATE `sentence` SET `number_of_usage_product`= `number_of_usage_product` +1  WHERE `sentence_id` in (" . implode(",", $sentence_id) . ");";
				$connection->query($sql);
			} catch (Exception $e)
			{
				continue;
			}
		}
	}

	public function generateDescription()
	{
		$tag_collection = Mage::getModel('tag/tag')->getCollection();
		$tag_collection->getSelect()->where("main_table.description is NULL or main_table.description = ''")
		->limit("200");

		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');

		$tag_data = array();
		if (is_object($tag_collection) && $tag_collection->getSize() > 0)
			$tag_data = $tag_collection->getData();

		foreach ($tag_data as $t)
		{

			if ($t['description'] == '' || $t['description'] === NULL)
			{
				$tag_name = $t['name'];
				$tag_id = $t['tag_id'];
				$sentence_name_before = array();
				$sentence_id = array();
				$final_description = '';
				$m = 0;
				$sentence_collection_less = '';
				$less = '';
				$sentence_collection_count = Mage::getModel('sentence/sentence')->getCollection();

                // Get Maximum Number of Count from column 'number_of_usage'
				$sentence_collection_count->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns('MAX(number_of_usage) as number_of_usage');

				foreach ($sentence_collection_count as $c)
				{
					$max_count = $c['number_of_usage'];
				}

				if ($max_count > 0)
				{
					$temp = $max_count - 1;
					$sentence_collection = Mage::getModel('sentence/sentence')->getCollection()->addFieldToFilter('number_of_usage', $temp)->setPageSize(5);
				}
				else
					$sentence_collection = Mage::getModel('sentence/sentence')->getCollection()->addFieldToFilter('number_of_usage', $max_count)->setPageSize(5);

                // Get Random Collection
				$sentence_collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
				if ($sentence_collection->count() < 5)
				{
					$less = 5 - $sentence_collection->count();
					$sentence_collection_less = Mage::getModel('sentence/sentence')->getCollection()->addFieldToFilter('number_of_usage', $max_count)->setPageSize($less);
					$sentence_collection_less->getSelect()->order(new Zend_Db_Expr('RAND()'));
				}

				if (is_object($sentence_collection_less) && $sentence_collection_less->getSize() > 0)
					$sentence_name_before = array_merge_recursive($sentence_collection->getData(), $sentence_collection_less->getData());
				else
					$sentence_name_before = $sentence_collection->getData();


				for ($i = 0; $i < count($sentence_name_before); $i++)
				{
					$sentence = '';
					$replace = '';

					if ($i == '0' || $i == '2')
					{
						$replace = $tag_name . " PowerPoint templates and " . $tag_name . " PPT Slides";
					}
					elseif ($i == '1')
					{
						$replace = $tag_name . " PowerPoint Presentation graphics and " . $tag_name . " PPT image backgrounds";
					}
					elseif ($i == '3')
					{
						$replace = $tag_name . " themes for presentations";
					}
					elseif ($i == '4')
					{
						$replace = $tag_name . " PowerPoint diagrams and PPT Clipart";
					}

					$sentence = $sentence_name_before[$i]['sentence'];
					$sentence_id[] = $sentence_name_before[$i]['sentence_id'];
					$sentence = str_replace('PT&S', $replace, $sentence);
					$final_description .= $sentence;
				}

				try
				{
					$sql = "UPDATE `tag` SET `description` = '" . addslashes($final_description) . "' WHERE tag_id = '" . $tag_id . "';";
					$update_sentence_count_sql = "UPDATE `sentence` SET `number_of_usage`= `number_of_usage` +1  WHERE `sentence_id` in (" . implode(",", $sentence_id) . ");";
					$connection->query($sql . $update_sentence_count_sql);
				} catch (Exception $e)
				{
					continue;
				}
			}
		}
	}

	public function importCsv($line)
	{
		
		$sentence_collection_count_product = $this->sentenceCollectionFactory->create();

        // Get Maximum Number of Count from column 'number_of_usage'

		if ($sentence_collection_count_product->getSize() > 0)
		{
			$sentence_collection_count_product->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('MAX(number_of_usage) as number_of_usage');
			foreach ($sentence_collection_count_product as $c)
			{
				$max_count = $c['number_of_usage'];
			}
		}
		else
		{
			$max_count = 0;
		}

		if ($sentence_collection_count_product->getSize() > 0)
		{
			$sentence_collection_count_product->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('MAX(number_of_usage_product) as number_of_usage_product');
			foreach ($sentence_collection_count_product as $c)
			{
				$max_count_product = $c['number_of_usage_product'];
			}
		}
		else
		{
			$max_count_product = 0;
		}

		$flag = 1;
		$count = 0;

		$line_value = $line[0];
		$line_normal = iconv("ASCII", "UTF-8//IGNORE", $line_value);
		$line_normal = trim($line_normal, ' ');

		$line_lower = strtolower($line_normal);




		$model = $this->sentenceCollectionFactory->create();
		foreach ($model as $m)
		{
			$sentence = $m->getSentence();
			$sentence = strtolower($sentence);

			if (strcmp($sentence, $line_lower) == '0')
			{
				$count++;
				break;
			}
		}echo $line_normal;
		if ($count == '0')
		{
			echo $line_normal;
			$this->setSentence($line_normal);

			if ($max_count > 0)
			{
				$temp = $max_count - 1;
				$this->setNumberOfUsage($temp);
			}
			else
			{
				$this->setNumberOfUsage($max_count);
			}


			if ($max_count_product > 0)
			{
				$temp_product = $max_count_product - 1;
				$this->setNumberOfUsageProduct($temp_product);
			}
			else
			{
				$this->setNumberOfUsageProduct($max_count_product);
			}


			$this->save();
			$this->unsetData();
			//return $flag;
		}
	}
}