<?php


namespace Tatva\ProductImport\Model;

use Magento\Framework\Model\AbstractModel;

class ProductImport extends AbstractModel
{   

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
		('Tatva\ProductImport\Model\ResourceModel\ProductImport');
	}
	
}