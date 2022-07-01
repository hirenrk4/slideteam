<?php 
namespace Tatva\Ebook\Model\Config\Source;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\Category;

class CategoryList implements \Magento\Framework\Option\ArrayInterface
{

	protected $storeManagerInterface;
	public function __construct(
		StoreManagerInterface $storeManagerInterface,
		Category $category
	){
    	$this->storeManagerInterface = $storeManagerInterface;
    	$this->category = $category;
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
       $rootCatId = $this->storeManagerInterface->getStore()->getRootCategoryId();
       $mainCategory = $this->category->load($rootCatId);
       $parent_category = $mainCategory->getChildrenCategories();

        $catagoryList = array();
        foreach ($parent_category as $category){
            $catagoryList[$category->getEntityId()] = __($category->getName());
        }

        return $catagoryList;
    }
}