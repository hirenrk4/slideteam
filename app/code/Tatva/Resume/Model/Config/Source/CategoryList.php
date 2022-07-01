<?php 
namespace Tatva\Resume\Model\Config\Source;
use Magento\Store\Model\StoreManagerInterface;

class CategoryList implements \Magento\Framework\Option\ArrayInterface
{

	protected $storeManagerInterface;
    protected $_categoryCollection;

	public function __construct(
		StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
	){
    	$this->storeManagerInterface = $storeManagerInterface;
    	$this->_categoryCollection = $categoryCollection;
    }


	public function toOptionArray()
    {
        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value)
        {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
        return $ret;
    }

    public function toArray()
    {
       // $storeID = $this->storeManagerInterface->getStore()->getId();
        $categories = $this->_categoryCollection->create()                              
            ->addAttributeToSelect('*')
            ->setStore($this->storeManagerInterface->getStore());
        $catagoryList = array();
         foreach ($categories as $category){
             $category->getName();
             $catagoryList[$category->getEntityId()] = __($category->getName());
         }

        return $catagoryList;
    }
}