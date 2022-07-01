<?php
namespace Tatva\Downloadable\Cron;

class Download
{
    protected $_resourceCollection;

    protected $connection;

    protected $entityCollectionFactory;

    protected $productAction;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $resourceCollection,
        \Magento\Framework\App\ResourceConnection $resourceData,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $entityCollectionFactory,
        \Magento\Catalog\Model\Product\Action $productAction
    ) {
        $this->_resourceCollection = $resourceCollection;
        $this->_resource = $resourceData;
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->productAction = $productAction;
    }

    public function execute()
    {
        
        $downloadCount = array();

        $download_collection = $this->_resource->getConnection()->fetchAll("SELECT `e`.*, DATEDIFF(CURDATE(),`e`.created_at) as 'number_of_days', COUNT(DISTINCT customer_id) AS `download_count` FROM `catalog_product_entity` AS `e`
        LEFT JOIN `productdownload_history_log` ON `e`.`entity_id` = product_id WHERE (`e`.`entity_id` > '0') AND (`e`.`type_id` = 'downloadable') GROUP BY `e`.`entity_id`");

        if(count($download_collection) > 0)
        {
            foreach ($download_collection as $key => $downloadValue) {

                $id = $downloadValue['entity_id'];
                $sku = $downloadValue['sku'];

                $downloadValue['number_of_days'] = ($downloadValue['number_of_days'] == 0 ? 1 : $downloadValue['number_of_days']);

                $downloadRatio = (int)$downloadValue['download_count'] / (int)$downloadValue['number_of_days'];

                $productids = array($id);
                $attributesData = ['number_of_downloads' => $downloadRatio];
                $storeId = 0;

                try {
                    $this->productAction->updateAttributes($productids, $attributesData, $storeId);

                } catch (Exception $e) {
                    
                    continue; 
                }

            }

        }
    }
}