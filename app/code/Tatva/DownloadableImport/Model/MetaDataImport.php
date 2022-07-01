<?php
namespace Tatva\DownloadableImport\Model;


class MetaDataImport extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Tatva\Metatitle\Model\Mysql4\Metatitle\CollectionFactory
     */

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $catalogProductAction;

   
 public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\Product\Action $catalogProductAction,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        \Tatva\Metatitle\Model\ResourceModel\Metatitle\CollectionFactory $metatitleModelFactory,
        \Tatva\Metadescription\Model\ResourceModel\Metadescription\CollectionFactory $metadescriptionModelFactory,
        \Tatva\Sentence\Model\ResourceModel\Sentence\CollectionFactory $sentenceModelFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
        ) {
        $this->resourceConnection = $resourceConnection;
        $this->catalogProductAction = $catalogProductAction;
         $this->productModel = $productModel;
        $this->storeResolver = $storeResolver;
        $this->_metatitleModelFactory = $metatitleModelFactory;
        $this->_metadescriptionModelFactory = $metadescriptionModelFactory;
        $this->_sentenceModelFactory = $sentenceModelFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
            );
    }

public function setProductData($attributeCodeID=1,$storeCode=0,$product_name=''){

         $connection = $this->resourceConnection->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            if($attributeCodeID==1)
            {
                $factory=$this->_metatitleModelFactory;
            }
            elseif($attributeCodeID==2){
                $factory=$this->_metadescriptionModelFactory;
            }
            elseif($attributeCodeID==3){
                $factory=$this->_sentenceModelFactory;
            }
         $store_id=$this->storeResolver->getStoreCodeToId($storeCode);
         $collectionCount = $factory->create();
         $attributeName1=$attributetitleNameBefore =$attbibuteMaxCountArray =$attbibuteLessCountArray=[];
        $collectionOfLessCount = $collectionOfMaxCount = $attbibuteTitleCount=$attbibutetitleId='';
     
        $collectionCount->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('MAX(number_of_usage_product) as number_of_usage_product');

        $numberOfUsageProducts=$collectionCount->getData();
        $maxCount=$numberOfUsageProducts[0]['number_of_usage_product'];

       $collectionOfMaxCount = $factory->create()->addFieldToFilter('number_of_usage_product', $maxCount);

       $attbibuteMaxCountArray = $collectionOfMaxCount->getData();

        if ($maxCount > 0)
        {
            $temp = $maxCount - 1;
            $collectionOfLessCount = $factory->create()->addFieldToFilter('number_of_usage_product', $temp);  
                              
        } 
        if(isset($collectionOfLessCount)) {
            if (is_object($collectionOfLessCount) && $collectionOfLessCount->getSize() > 0)
                $attbibuteLessCountArray = $collectionOfLessCount->getData();
        }
       if(!empty($attbibuteMaxCountArray))
        {
            $attributetitleName=array_merge($attbibuteLessCountArray,$attbibuteMaxCountArray);
        }
        else{

            $attributetitleName =$attbibuteMaxCountArray;
        }
       $attbibuteTitleCount=count($attributetitleName);
               
        $i=1;


                if($store_id==1)
                {
                    $store_id=0;
                }

                if($attributeCodeID==1)
                {
                    $attributeId1 = $attributetitleName[0]['metatitle_id'];
                    $attributetitle1 =$attributetitleName[0]['metatitle'];
                    $attributeName1='meta_title';
                     $tableName='metatitle';
                    $primaryID='metatitle_id';
                }
                else if($attributeCodeID==2)
                {
                     $attributeId1 = $attributetitleName[0]['metadescription_id'];
                    $attributetitle1 =$attributetitleName[0]['metadescription'];
                    $attributeName1='meta_description';
                     $tableName='metadescription';
                    $primaryID='metadescription_id';

                }
                else if($attributeCodeID==3)
                {
                    $attributeId1 = $attributetitleName[0]['sentence_id'];
                    $attributeId2 = $attributetitleName[1]['sentence_id'];
                    $attributetitle1 =$attributetitleName[0]['sentence'];
                    $attributetitle2 =$attributetitleName[1]['sentence'];
                    $attributeName1='sentence1';
                    $attributeName2='sentence2';
                    $tableName='sentence';
                    $primaryID='sentence_id';

                    if($attributeCodeID==3)
                    {
                        $attributetitle2 = str_replace('PT&S', $product_name, $attributetitle2); 
                        $sql = "UPDATE ". $tableName ." SET `number_of_usage_product`= `number_of_usage_product` +1  WHERE ". $primaryID ." =  ".$attributeId2;
                        $connection->query($sql);
                        $resultArray[$attributeName2]=$attributetitle2;
                    }

                }
                    $attributetitle1 = str_replace('PT&S', $product_name, $attributetitle1);

              $sql = "UPDATE ". $tableName ." SET `number_of_usage_product`= `number_of_usage_product` +1  WHERE ". $primaryID ." =  ".$attributeId1;
            $connection->query($sql);
            $resultArray[$attributeName1]=$attributetitle1;           
            return $resultArray;
         }
}

