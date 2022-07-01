<?php
namespace Tatva\Emarsys\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
   /**
     * @var Context
     */
    protected $context;
    protected $_productCollectionFactory;


   public function __construct(
      \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,   
      Context $context
    ) {
      $this->_productCollectionFactory = $productCollectionFactory; 
      $this->context = $context;
      parent::__construct($context);
    }
    public function getProductCollection($filterWords)
    {
      $collection = $this->_productCollectionFactory->create();
      $collection->addAttributeToSelect('*');
      $collection->addAttributeToFilter(array(array('attribute' => 'name', 'like' => '%'.$filterWords.'%')));
      $collection->load();
      return  $collection->count();
    }
}