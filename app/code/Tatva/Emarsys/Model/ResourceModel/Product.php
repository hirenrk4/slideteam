<?php

namespace Tatva\Emarsys\Model\ResourceModel;

class Product extends \Emarsys\Emarsys\Model\ResourceModel\Product
{
	public function getRequiredProductAttributesForExport($storeId)
	{
		$requiredMapping = [];
        $requiredMapping['sku'] = 'item'; // Mage_Attr_Code = Emarsys_Attr_Code
        $requiredMapping['name'] = 'title';
        /*$requiredMapping['quantity_and_stock_status'] = 'available';*/
        $requiredMapping['url_key'] = 'link';
        $requiredMapping['image'] = 'image';
        $requiredMapping['category_ids'] = 'category';
        $requiredMapping['price'] = 'price';
        $requiredMapping['status'] = 'available';
        $requiredMapping['c_thumbnail_1'] = 'c_thumbnail_1';
        $requiredMapping['c_thumbnail_2'] = 'c_thumbnail_2';
        $requiredMapping['c_thumbnail_3'] = 'c_thumbnail_3';
        $requiredMapping['c_thumbnail_4'] = 'c_thumbnail_4';
        $requiredMapping['c_thumbnail_5'] = 'c_thumbnail_5';
        $requiredMapping['c_thumbnail_6'] = 'c_thumbnail_6';
        $requiredMapping['c_date_added'] = 'c_date_added';
        $requiredMapping['c_number_of_slides'] = 'c_number_of_slides';
        $requiredMapping['c_number_of_times_downloaded'] = 'c_number_of_times_downloaded';
        $requiredMapping['c_nodes'] = 'c_nodes';
        $requiredMapping['c_spl_thumbnail'] = 'c_spl_thumbnail';
        
        $returnArray = [];
        foreach ($requiredMapping as $key => $value) {
            $attrData = [];
            $attrData['emarsys_contact_field'] = '';
            $attrData['magento_attr_code'] = $key;
            $attrData['emarsys_attr_code'] = $this->getEmarsysAttributeIdByCode($value, $storeId);
            $attrData['sync_direction'] = '';
            $attrData['store_id'] = $storeId;
            $returnArray[] = $attrData;
        }

        return $returnArray;
	}
}