<?php
namespace Tatva\Wishlist\CustomerData;

/**
 * Wishlist section
 */
class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist
{


    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $counter = $this->getCounter();
        return [
            'counter' => $counter,
            'added' => $this->getItemsAdded(),
            'items' => $counter ? $this->getItems() : [],           
        ];
    }

    public function getItemsAdded(){

        $collection = $this->wishlistHelper->getWishlistItemCollection();
        $collection->clear()->setInStockFilter(true)->setPageSize(false);
        $items      = array();

        foreach ($collection as $item){
            $items[$item->getProductId()] = json_decode($this->wishlistHelper->getRemoveParams($item, true));
        }
        return $items;
    }   
}