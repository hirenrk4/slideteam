<?php

namespace Tatva\SLIFeed\Model\Generators;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use SLI\Feed\Helper\XmlWriter;
use SLI\Feed\Helper\GeneratorHelper;
use Magento\Store\Model\ScopeInterface;
use Tatva\SLIFeed\Model\Resource\Productdelete\ProductCollection;
use Tatva\SLIFeed\Model\Resource\Productdelete\ProductCollectionFactory;
use Tatva\SLIFeed\Model\Productdelete;
use Magento\Framework\App\Config\ScopeConfigInterface;


class ProductGenerator extends \SLI\Feed\Model\Generators\ProductGenerator {

    protected $_productCollectionFactory;
    protected $connection;
    protected $_resourceCollection;
    protected $imageHelper;
    protected $storeManager;
    protected $appEmulation;
    protected $_scopeConfig;
    protected $helper;
    protected $dataobject;
    protected $productDeleteFactory;
    protected $configWriter;
    protected $productRepository;
    protected $categoryCollection; 
    

    public function __construct(\Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
            \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, 
            \Magento\Store\Model\StoreManagerInterface $storeManager, 
            \Magento\Catalog\Model\ResourceModel\Product $resource, 
            \Magento\Catalog\Model\ResourceModel\Product\Collection $resourceCollection, 
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $entityCollectionFactory,
            \SLI\Feed\Helper\GeneratorHelper $generatorHelper,
            \Magento\Framework\App\ResourceConnection $resourceData, 
            \Magento\Catalog\Helper\Image $imageHelper, 
            \Magento\Store\Model\App\Emulation $appEmulation, 
            \Magento\CatalogInventory\Model\ResourceModel\Stock\Status $status,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Tatva\SLIFeed\Helper\GeneratorHelper $helper,
            \Magento\Framework\DataObject $dataobject,
            \Tatva\SLIFeed\Model\Resource\Productdelete\ProductCollectionFactory $productDeleteFactory,
            \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
            \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    ) {
        $this->_resource = $resourceData;
        $this->imageHelper = $imageHelper;
        $this->_resourceCollection = $resourceCollection;
        $this->storeManager = $storeManager;
        $this->appEmulation = $appEmulation;
        $this->_scopeConfig = $scopeConfig;
        $this->dataobject = $dataobject;
        $this->productDeleteFactory = $productDeleteFactory;
        $this->helper = $helper;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->_productRepository = $productRepository;
        $this->categoryCollection = $categoryCollection; 
        parent::__construct(
                $context, $registry, $extensionFactory, $customAttributeFactory, $storeManager, $resource, $resourceCollection, $entityCollectionFactory, $generatorHelper, $status
        );
    }

    protected function getConnection() {
        if (!$this->connection) {
            $this->connection = $this->_resource->getConnection('core_write');
        }
        return $this->connection;
    }

    /**
     * {@inheritdoc}
     */
    public function generateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger) {

        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        // print_r($_product->_getResource()->getAttribute('gallery')->getValue($_product));
        $logger->debug(sprintf('[%s] Starting product XML generation', $storeId));

        $entityCollection = $this->entityCollectionFactory->create();
        $extraAttributes = $this->generatorHelper->getAttributes($storeId, $logger);
        $logger->debug(sprintf('[%s] Extra attributes: %s', $storeId, implode(', ', $extraAttributes)));
        /** @var Collection $entityCollection */

        //$resumecategoryId = $this->_scopeConfig->getValue('resume/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);    
        //$ebookcategoryId = $this->_scopeConfig->getValue('ebook/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $entityCollection
                ->setStoreId($storeId)              
                ->addAttributeToSelect($extraAttributes)// '*' for all, this also filters out all the bad values
                ->addAttributeToFilter('type_id',array('downloadable'))
                //->addAttributeToFilter('is_ebook',array(array('null'=>true),array('neq'=>1)),'left')
                ->addStoreFilter($storeId)// Need to add to reduce the products that can show for the store
                ->addCategoryIds()
                ->setOrder('entity_id', Select::SQL_ASC);

        //$catalog_ids = [$resumecategoryId,$ebookcategoryId];
        //$entityCollection->addCategoriesFilter(array('nin' => $catalog_ids));

        // Add the ratings to the collection if they are selected in the admin UI
        if (array_search('rating_summary', $extraAttributes) !== false) {
            $logger->debug(sprintf('[%s] Adding rating_summary to collection', $storeId));
            $entityCollection = $entityCollection->joinField(
                    'rating_summary', 'review_entity_summary', 'rating_summary', 'entity_pk_value=entity_id', ['entity_type' => 1, 'store_id' => $storeId], 'left');
        }

        // Need to find a way to add this into the above statement
        // Add the ratings counts to the collection if they are selected in the admin UI
        if (array_search('reviews_count', $extraAttributes) !== false) {
            $logger->debug(sprintf('[%s] Adding reviews_count to collection', $storeId));
            $entityCollection = $entityCollection->joinField(
                    'reviews_count', 'review_entity_summary', 'reviews_count', 'entity_pk_value=entity_id', ['entity_type' => 1, 'store_id' => $storeId], 'left');
        }

        
        $filterInStock = !$this->generatorHelper->isIncludeOutOfStock($storeId);
        $logger->debug(sprintf('[%s] Filter in stock products only = %s', $storeId, ($filterInStock ? 'true' : 'false')));
        $this->status->addStockDataToCollection($entityCollection, $filterInStock);

        $logger->debug(sprintf('[%s] Adding price data to collection', $storeId));

        // These need to be done later on as they actually call the SQL so it must go after the SQL
        $entityCollection = $entityCollection
//            ->addPriceData()
                ->addUrlRewrite();

        $page = 0;
        $processed = 0;
        $pageSize = 1000;
        $uniqueIdMap = [];
        $allstoreExistProduct='';
        $allstoreExistProductIDs=[];
        $feed_product_count = 0;

        $xmlWriter->startElement('products');

        $entityCollection->setPage(++$page, $pageSize);
        $logger->debug(sprintf("[%s] Product collection select: %s ", $storeId, $entityCollection->getSelectSql(true)));
        $removeAtributes = [];
        $removeAttributes = array('final_price', 'max_price', 'min_price', 'minimal_price', 'price', 'special_price', 'tier_price');
        

        $download_collection = $this->_resourceCollection;
        $download_collection->getSelect()->where("e.entity_id > '0'");
        $download_collection->getSelect()->joinLeft(array('productdownload_history_log'), "e.entity_id = product_id", array('COUNT(DISTINCT customer_id) AS download_count'));
        $download_collection->addAttributeToFilter('type_id', array('simple', 'downloadable'));
        $download_collection->getSelect()->group('e.entity_id');
        $downloadProduct = $download_collection->getData();
        foreach ($downloadProduct as $key => $downloadValue) {
            $downloadCount[$downloadValue['entity_id']]['downloadCount'] = $downloadValue['download_count'];
        }
        while ($items = $entityCollection->getItems()) {
        	$offset = (($page * $pageSize) - $pageSize);
        	$product_ids = $entityCollection->getAllIds($pageSize,$offset);
	        $product_id_data = implode(",", $product_ids);
	            
	        /*$result= $this->getProuctAllStoreData($product_ids);
	        $imageCount=$result['imageCount'];
	        $imageData=$result['imageData']; */
            /** @var Product $item */
            foreach ($items as $item) {

                $feed_product_count++;

                $removeAttributes = array('old_id', 'tax_class_id', 'links_purchased_separately', 'is_imported', 'free', 'subscription_period', 'download_limit',
                    'quantity_and_stock_status', 'price', 'meta_title', 'meta_description', 'small_image', 'thumbnail', 'gallery', 'url_path', 'options_container',
                    'gift_message_available', 'links_title', 'short_description', 'meta_keyword', 'product_tags', 'sentence1', 'sentence2', 'options', 'media_gallery',
                    'extension_attributes', 'downloadable_links', 'downloadable_samples', 'tier_price', 'tier_price_changed', 'category_ids', 'is_salable',
                    'media_gallery_images', 'child_ids', 'is_virtual', 'has_options', 'special_from_date', 'required_options', 'visibility', 'image_label', 'small_image_label',
                    'thumbnail_label', 'samples_title', 'special_price', 'special_to_date', 'links_exist', 'todays_number_of_downloads', 'cost', 'minimal_price',
                    'msrp', 'custom_design', 'page_layout', 'msrp_display_actual_price_type', 'news_from_date', 'news_to_date', 'custom_design_from',
                    'custom_design_to', 'custom_layout_update', '_cache_instance_products','_cache_instance_configurable_attributes','is_ebook');
                
                $result= $this->getProuctAllStoreData($item->getId());
                $imageCount=$result['imageCount'];
                $imageData=$result['imageData'];

                $storeId = $this->storeManager->getStore()->getId();
                $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
                $category_small_image = str_replace($baseUrl,'https://www.slideteam.net/',$this->imageHelper->init($item, 'category_page_grid')->setImageFile($item->getImage())->getUrl());
                $category_medium_image = str_replace($baseUrl,'https://www.slideteam.net/',$this->imageHelper->init($item, 'product_page_image_medium')->setImageFile($item->getImage())->getUrl());
               
                //Product Image
                if (array_search('image', $extraAttributes) !== false) {
                    $imageFile = $item->getImage();
                    $pos = strrpos($imageFile, '.');
                    $pathEnding = substr($imageFile, $pos + 1);
                    if($pathEnding == 'gif' || $pathEnding == 'GIF') {
                        $product_small_image_for_gif = str_replace($baseUrl,'https://www.slideteam.net/',$this->imageHelper->init($item, 'category_page_grid')->setImageFile($item->getSmallImage())->getUrl());
                        $item->setData('image', $product_small_image_for_gif);
                    }else{
                        $item->setData('image', $category_small_image);
                    }
                    
                }

                $dataImage = [];
                //Media Gallery Data
                if (array_key_exists($item->getId(), $imageData)) {
                    
                    $total_images = count($imageData[$item->getId()]);
                    $remaining = $total_images-3;

                    $id =0;
                    $galary_images = array();
                    $image_url='';                
                    $is_hero_img_different = 0;

                    foreach ($imageData[$item->getId()] as $key => $value) {
                        $galary_images[] = str_replace($baseUrl,'https://www.slideteam.net/',$this->imageHelper->init($item,'category_page_grid')->setImageFile($value)->getUrl());
                        $id++;
                        if($id >5){break;}
                    }

                    $count = count($galary_images);

                    $small_image_biggersize2="NA";
                    $small_image_biggersize3="NA";
                    $small_image_biggersize4="NA";
                    $small_image_biggersize5="NA";
                    $small_image_biggersize6="NA";

                    if($remaining > 3) {
                        $small_image_biggersize1 =  $galary_images[0];
                    }else {
                        $small_image_biggersize1 = str_replace($baseUrl,'https://www.slideteam.net/',$this->imageHelper->init($item, 'product_page_image_medium')->setImageFile($imageData[$item->getId()][0])->getUrl());
                    }

                    $hero_img_str = $galary_images[0];
                    $hero_img_name = substr($hero_img_str,(int)strrpos($hero_img_str,'/'));
                    $small_img_name = substr($category_small_image,(int)strrpos($category_small_image,'/'));  

                    if($hero_img_name != $small_img_name){
                        $is_hero_img_different = 1;
                        if($remaining > 3) {
                            $small_image_biggersize1 = $category_small_image;
                        }else {
                            $small_image_biggersize1 = $category_medium_image;
                        }
                        if($count > 1){
                            $small_image_biggersize2 = $galary_images[0];
                        }
                        if($count > 2){
                            $flag = strpos($galary_images[1], $small_img_name);
                            if($flag !== false){
                                $small_image_biggersize3 =  $galary_images[2];    
                            }
                            else{
                                $small_image_biggersize3 =  $galary_images[1];
                            }
                        }
                        if($count > 3){
                            $small_image_biggersize4 =  $galary_images[3];
                        }
                        if($count > 4){
                            $small_image_biggersize5 =  $galary_images[4];
                        }
                        if($count > 5){
                            $small_image_biggersize6 =  $galary_images[5];
                        }
                    }
                    else{
                        if($count > 1){
                            $small_image_biggersize2 = $galary_images[1];
                        }
                        if($count > 2){
                            $small_image_biggersize3 =  $galary_images[2];
                        }
                        if($count > 3){
                            $small_image_biggersize4 =  $galary_images[3];
                        }
                        if($count > 4){
                            $small_image_biggersize5 =  $galary_images[4];
                        }
                        if($count > 5){
                            $small_image_biggersize6 =  $galary_images[5];
                        }
                    }

                    $categoryIds = $item->getCategoryIds();
                    $A4ProductCategory = $this->_scopeConfig->getValue('button/A4_product/category_tree', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $A4ProductCategory = explode(',',$A4ProductCategory);

                    if(array_intersect($A4ProductCategory,$categoryIds)){
                        $small_image_biggersize1 = str_replace($baseUrl,'https://www.slideteam.net/',$this->imageHelper->init($item, 'product_page_image_medium_no_frame')->setImageFile($imageData[$item->getId()][0])->getUrl());

                        $item->setData('is_a4_product', TRUE);
                    }
                    else{
                        $item->setData('is_a4_product', FALSE);    
                    }

                    $item->setData('c_popup_1',$small_image_biggersize1);
                    $item->setData('c_popup_2',$small_image_biggersize2);
                    $item->setData('c_popup_3',$small_image_biggersize3);
                    $item->setData('c_popup_4',$small_image_biggersize4);
                    $item->setData('c_popup_5',$small_image_biggersize5);
                    $item->setData('c_popup_6',$small_image_biggersize6);

                    
                }
                if(array_key_exists($item->getId(), $imageCount)) {
                	$countVar = $imageCount[$item->getId()]['count'];
                }
                
                //No of product slides
                if (array_search('c_number_of_slides', $extraAttributes) !== false) {
                    $item->setData('c_number_of_slides', $countVar);
                }

                //Availability of product

                if (array_search('availability', $extraAttributes) !== false) {

                    $proname = $item->getName();
                    $pname = substr($proname, -3);
                    $lastchar = strtolower($pname);

                    $free = $item->getFree();

                    if ($item->getStatus() == 1 && $lastchar != 'cpb' && $free != 1) {
                        $item->setData('availability', TRUE);
                    } else {
                        $item->setData('availability', FALSE);

                    }
                }

                //Request Path
                /*$link = $baseUrl.''.$item->getUrlKey().'.html';*/
                $producturl = $item->getProductUrl();
                $path = explode("/", $producturl); 
                $last = end($path); 
                $link = $baseUrl.''.$last;
                $item->setData('request_path', str_replace($baseUrl, 'https://www.slideteam.net/', $link));


                //Number of Download Count 
                if (array_key_exists($item->getId(), $downloadCount)) {
                    $downloadVar = $downloadCount[$item->getId()]['downloadCount'];
                    $item->setData('number_of_downloads', $downloadVar);
                }

                //Node related data

                $nodes = $this->CategoryTreeCli($item->getId());
                $item->setData('nodes', $nodes);

                // depending on the requested data some products might be coming back more than once
                if (array_key_exists($item->getId(), $uniqueIdMap)) {
                    continue;
                }
                $uniqueIdMap[$item->getId()] = $item->getId();
                ++$processed;
                $this->writeProduct($xmlWriter, $item, array_diff(array_keys($item->_data), $removeAttributes), $extraAttributes);
            }

            $logger->debug(sprintf('[%s] Finished writing product page %s', $storeId, $page));

            $this->configWriter->save('feed_count/sli_feed/today_count', $feed_product_count);

            if (count($items) < $pageSize) {
                break;
            }

            $entityCollection->setPage(++$page, $pageSize);
            $entityCollection->clear();
        }

        // products
        $xmlWriter->endElement();

        $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        $this->cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
        
        $logger->debug(sprintf('[%s] Product generator: processed items: %s, pages: %s', $storeId, $processed, $page));

        return true;
    }

    public function getProuctAllStoreData($productId){

        $imageData=$imageCount=$product_ids=$allstoreimagesCollection=$imagestoreData=[];
        /*$product_ids=$productIds;
        $ProductIdsarray = implode(",",$product_ids); */
        $imagesCollection = $this->getConnection()->fetchAll('SELECT mgvte.value_id,mgvte.entity_id,mg.attribute_id,mg.value,mgv.store_id FROM catalog_product_entity_media_gallery_value_to_entity as mgvte LEFT JOIN catalog_product_entity_media_gallery as mg ON mg.value_id = mgvte.value_id LEFT JOIN catalog_product_entity_media_gallery_value as mgv ON mg.value_id = mgv.value_id WHERE mgv.disabled = 0 AND mgvte.entity_id = '.$productId);


     	foreach ($imagesCollection as $key => $value) {
     		/*if(!isset($imagestoreData[$value['entity_id']]['store_id']))
            {
               $imagestoreData[$value['entity_id']]['store_id'] = $value['store_id']; 
            }
            if($imagestoreData[$value['entity_id']]['store_id']==$value['store_id'])
            {
  
                $imageData[$value['entity_id']][] = $value['value'];
            }*/
            if(!in_array($value['value'], $allstoreimagesCollection)) {
                array_push($allstoreimagesCollection, $value['value']);
                $imageData[$value['entity_id']][] = $value['value'];
            }
            foreach ($imageData as $k => $imageValue) {
                $count = count($imageValue);
            }
            $imageCount[$value['entity_id']]['count'] = $count;       
        }
       	$result['imageCount']=$imageCount;
        $result['imageData'] =$imageData;
        return $result;
    }

    public function CategoryTreeCli($product_id) {
        $product = $this->_productRepository->getById($product_id);
        $cats = $product->getCategoryIds();
        
        if(!empty($cats)) {
            $catCollection = $this->categoryCollection->create();
            $catCollection->getSelect()->where('attribute_id = 33');
            $catCollection->getSelect()->joinLeft(array('catalog_category_entity_varchar'),'e.entity_id = catalog_category_entity_varchar.entity_id',array('catalog_category_entity_varchar.value as category_name','attribute_id'));
            $catCollection->addAttributeToFilter('entity_id',array('in' => array($cats)));
            $catCollection->addAttributeToFilter('level',array('gteq'=>4));
            $catCollection->getSelect()->group('e.entity_id');

            $category_data = $catCollection->getData();
           
            if($category_data) {    
                foreach ($category_data as $cat) {
                    if($cat['category_name']){
                        return $cat['category_name'];
                    }   
                }
            }else {
                return 'NA';
            }   
        }else {
            return 'NA';
        }

        
    }
    public function updateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger) {
        $logger->debug(sprintf('[%s] Starting product XML generation', $storeId));

        $entityCollection = $this->entityCollectionFactory->create();
        $extraAttributes = $this->generatorHelper->getAttributes($storeId, $logger);
        $logger->debug(sprintf('[%s] Extra attributes: %s', $storeId, implode(', ', $extraAttributes)));
        /** @var Collection $entityCollection */

        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $date = $this->_scopeConfig->getValue('sli_feed_generation/feed/date');

        $entityCollection
                ->setStoreId($storeId)
                ->addAttributeToSelect($extraAttributes)// '*' for all, this also filters out all the bad values
                ->addAttributeToFilter('updated_at', array('gteq' => $date))
                ->addAttributeToFilter('type_id',array('downloadable'))
                ->addAttributeToFilter('is_ebook',array(array('null'=>true),array('neq'=>1)),'left')
                ->addStoreFilter($storeId)// Need to add to reduce the products that can show for the store
                ->addCategoryIds()
                ->setOrder('entity_id', Select::SQL_ASC);
        
        // Add the ratings to the collection if they are selected in the admin UI
        if (array_search('rating_summary', $extraAttributes) !== false) {
            $logger->debug(sprintf('[%s] Adding rating_summary to collection', $storeId));
            $entityCollection = $entityCollection->joinField(
                    'rating_summary', 'review_entity_summary', 'rating_summary', 'entity_pk_value=entity_id', ['entity_type' => 1, 'store_id' => $storeId], 'left');
        }

        // Need to find a way to add this into the above statement
        // Add the ratings counts to the collection if they are selected in the admin UI
        if (array_search('reviews_count', $extraAttributes) !== false) {
            $logger->debug(sprintf('[%s] Adding reviews_count to collection', $storeId));
            $entityCollection = $entityCollection->joinField(
                    'reviews_count', 'review_entity_summary', 'reviews_count', 'entity_pk_value=entity_id', ['entity_type' => 1, 'store_id' => $storeId], 'left');
        }


        $filterInStock = !$this->generatorHelper->isIncludeOutOfStock($storeId);
        $logger->debug(sprintf('[%s] Filter in stock products only = %s', $storeId, ($filterInStock ? 'true' : 'false')));
        $this->status->addStockDataToCollection($entityCollection, $filterInStock);

        $logger->debug(sprintf('[%s] Adding price data to collection', $storeId));

        // These need to be done later on as they actually call the SQL so it must go after the SQL
        $entityCollection = $entityCollection
//            ->addPriceData()
                ->addUrlRewrite();

        $page = 0;
        $processed = 0;
        $pageSize = 1000;
        $uniqueIdMap = [];

        $xmlWriter->startElement('products');

        $entityCollection->setPage(++$page, $pageSize);
        $logger->debug(sprintf("[%s] Product collection select: %s ", $storeId, $entityCollection->getSelectSql(true)));
        $removeAtributes = [];
        $removeAttributes = array('final_price', 'max_price', 'min_price', 'minimal_price', 'price', 'special_price', 'tier_price');

        //Delete Catalog Feed Generation

        $productDeleteCollection = $this->productDeleteFactory->create();
        $productDeleteCollection->addFieldToFilter('sendCatalogtoSLi',0)
        ->addFieldToFilter('updated_at', array('gteq' => $date));


        if(count($productDeleteCollection) > 0)
        {
            $new_date = date("Ymd",strtotime($date));
            $time = date('Hi',time());
            $filename = sprintf($this->helper->getFeedDeleteFileTemplate(),$new_date,$time); 

            $xml = new XmlWriter($filename);

            while($products = $productDeleteCollection->getItems())
            {   
                
                foreach ($products as $product) {
                    $removeAttributes = array('sendCatalogtoSLi','delete_id','url_key','created_at','updated_at');

                    $baseUrl = $this->generatorHelper->getBaseUrl($storeId);
                    $url_key = rtrim($product->getData("url_key"));

                    //Url key
                    $link = $baseUrl.''.$url_key.'.html';
                    $product->setData('url', $link);

                    //Delete status
                    $product->setData('delete','yes');

                    
                    $this->writeData($xml, $product, array_diff(array_keys($product->_data), $removeAttributes), $extraAttributes);
                    $product->setData('sendCatalogtoSLi',1);


                }
                if(count($products) < $pageSize) {
                    break;
                }

            }
            $xml->closeFeed();
            $productDeleteCollection->save();
        }

        if(count($entityCollection) > 0) {

            $download_collection = $this->_resourceCollection;
            $download_collection->getSelect()->where("e.entity_id > '0'");
            $download_collection->getSelect()->joinLeft(array('productdownload_history_log'), "e.entity_id = product_id", array('COUNT(DISTINCT customer_id) AS download_count'));
            $download_collection->addAttributeToFilter('type_id', array('simple', 'downloadable'));
            $download_collection->getSelect()->group('e.entity_id');
            $downloadProduct = $download_collection->getData();
            foreach ($downloadProduct as $key => $downloadValue) {
                $downloadCount[$downloadValue['entity_id']]['downloadCount'] = $downloadValue['download_count'];
            }

            while ($items = $entityCollection->getItems()) {
            	$offset = (($page * $pageSize) - $pageSize);
	        	$product_ids = $entityCollection->getAllIds($pageSize,$offset);
		        $product_id_data = implode(",", $product_ids);
		            
		        /*$result= $this->getProuctAllStoreData($product_ids);
		        $imageCount=$result['imageCount'];
		        $imageData=$result['imageData'];*/

                /** @var Product $item */
                foreach ($items as $item) {
                    $removeAttributes = array('old_id', 'tax_class_id', 'links_purchased_separately', 'is_imported', 'free', 'subscription_period', 'download_limit',
                        'quantity_and_stock_status', 'price', 'meta_title', 'meta_description', 'small_image', 'thumbnail', 'gallery', 'url_path', 'options_container',
                        'gift_message_available', 'links_title', 'short_description', 'meta_keyword', 'product_tags', 'sentence1', 'sentence2', 'options', 'media_gallery',
                        'extension_attributes', 'downloadable_links', 'downloadable_samples', 'tier_price', 'tier_price_changed', 'category_ids', 'is_salable',
                        'media_gallery_images', 'child_ids', 'is_virtual', 'has_options', 'special_from_date', 'required_options', 'visibility', 'image_label', 'small_image_label',
                        'thumbnail_label', 'samples_title', 'special_price', 'special_to_date', 'links_exist', 'todays_number_of_downloads', 'cost', 'minimal_price',
                        'msrp', 'custom_design', 'page_layout', 'msrp_display_actual_price_type', 'news_from_date', 'news_to_date', 'custom_design_from',
                        'custom_design_to', 'custom_layout_update','is_ebook');
                    
                    $result= $this->getProuctAllStoreData($item->getId());
                    $imageCount=$result['imageCount'];
                    $imageData=$result['imageData'];

                    $storeId = $this->storeManager->getStore()->getId();
                    $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
                    $category_small_image = str_replace($baseUrl,'https://www.slideteam.net/',$this->imageHelper->init($item, 'category_page_grid')->setImageFile($item->getImage())->getUrl());
                    $category_medium_image = str_replace($baseUrl,'https://www.slideteam.net/',$this->imageHelper->init($item, 'product_page_image_medium')->setImageFile($item->getImage())->getUrl());

                    $item->setData('image', $category_small_image);

                    if(!empty($imageData)) { 
                        $dataImage = [];

                        //Media Gallery Data
                        if (array_key_exists($item->getId(), $imageData)) {
                            
                            $total_images = count($imageData[$item->getId()]);
                            $remaining = $total_images-3;

                            $id =0;
                            $galary_images = array();
                            $image_url='';                
                            $is_hero_img_different = 0;

                            foreach ($imageData[$item->getId()] as $key => $value) {
                                $galary_images[] = str_replace($baseUrl,'https://www.slideteam.net/',$this->imageHelper->init($item,'category_page_grid')->setImageFile($value)->getUrl());
                                $id++;
                                if($id >5){break;}
                            }

                            $count = count($galary_images);

                            $small_image_biggersize2="NA";
                            $small_image_biggersize3="NA";
                            $small_image_biggersize4="NA";
                            $small_image_biggersize5="NA";
                            $small_image_biggersize6="NA";

                            if($remaining > 3) {
                                $small_image_biggersize1 =  $galary_images[0];
                            }else {
                                $small_image_biggersize1 = str_replace($baseUrl,'https://www.slideteam.net/',$this->imageHelper->init($item, 'product_page_image_medium')->setImageFile($imageData[$item->getId()][0])->getUrl());
                            }

                            $hero_img_str = $galary_images[0];
                            $hero_img_name = substr($hero_img_str,(int)strrpos($hero_img_str,'/'));
                            $small_img_name = substr($category_small_image,(int)strrpos($category_small_image,'/'));  

                            if($hero_img_name != $small_img_name){
                                $is_hero_img_different = 1;
                                if($remaining > 3) {
                                    $small_image_biggersize1 = $category_small_image;
                                }else {
                                    $small_image_biggersize1 = $category_medium_image;
                                }
                                if($count > 1){
                                    $small_image_biggersize2 = $galary_images[0];
                                }
                                if($count > 2){
                                    $flag = strpos($galary_images[1], $small_img_name);
                                    if($flag !== false){
                                        $small_image_biggersize3 =  $galary_images[2];    
                                    }
                                    else{
                                        $small_image_biggersize3 =  $galary_images[1];
                                    }
                                }
                                if($count > 3){
                                    $small_image_biggersize4 =  $galary_images[3];
                                }
                                if($count > 4){
                                    $small_image_biggersize5 =  $galary_images[4];
                                }
                                if($count > 5){
                                    $small_image_biggersize6 =  $galary_images[5];
                                }
                            }
                            else{
                                if($count > 1){
                                    $small_image_biggersize2 = $galary_images[1];
                                }
                                if($count > 2){
                                    $small_image_biggersize3 =  $galary_images[2];
                                }
                                if($count > 3){
                                    $small_image_biggersize4 =  $galary_images[3];
                                }
                                if($count > 4){
                                    $small_image_biggersize5 =  $galary_images[4];
                                }
                                if($count > 5){
                                    $small_image_biggersize6 =  $galary_images[5];
                                }
                            }

                            $item->setData('c_popup_1',$small_image_biggersize1);
                            $item->setData('c_popup_2',$small_image_biggersize2);
                            $item->setData('c_popup_3',$small_image_biggersize3);
                            $item->setData('c_popup_4',$small_image_biggersize4);
                            $item->setData('c_popup_5',$small_image_biggersize5);
                            $item->setData('c_popup_6',$small_image_biggersize6);

                            
                        }

                        if(array_key_exists($item->getId(), $imageCount)) {
                            $countVar = $imageCount[$item->getId()]['count'];
                        }
                        
                        //No of product slides
                        if (array_search('c_number_of_slides', $extraAttributes) !== false) {
                            $item->setData('c_number_of_slides', $countVar);
                        }
                    }else {
                        //No of product slides
                        if (array_search('c_number_of_slides', $extraAttributes) !== false) {
                            $item->setData('c_number_of_slides', 0);
                        }
                    }

                    //Availability of product

                    if (array_search('availability', $extraAttributes) !== false) {

                        $proname = $item->getName();
                        $pname = substr($proname, -3);
                        $lastchar = strtolower($pname);

                        $free = $item->getFree();

                        if ($item->getStatus() == 1 && $lastchar != 'cpb' && $free != 1) {
                            $item->setData('availability', TRUE);
                        } else {
                            $item->setData('availability', FALSE);

                        }
                    }

                    //Request Path
                    /*$link = $baseUrl.''.$item->getUrlKey().'.html';*/
                    $producturl = $item->getProductUrl();
                    $path = explode("/", $producturl); 
                    $last = end($path); 
                    $link = $baseUrl.''.$last;
                    $item->setData('request_path', str_replace($baseUrl, 'https://www.slideteam.net/', $link));


                    //Number of Download Count 
                    if (array_key_exists($item->getId(), $downloadCount)) {
                        $downloadVar = $downloadCount[$item->getId()]['downloadCount'];
                        $item->setData('number_of_downloads', $downloadVar);
                    }

                    //Node related data
                    $nodes = $this->CategoryTreeCli($item->getId());
                    $item->setData('nodes', $nodes);

                    // depending on the requested data some products might be coming back more than once
                    if (array_key_exists($item->getId(), $uniqueIdMap)) {
                        continue;
                    }
                    $uniqueIdMap[$item->getId()] = $item->getId();
                    ++$processed;
                    $this->writeProduct($xmlWriter, $item, array_diff(array_keys($item->_data), $removeAttributes), $extraAttributes);
                    $this->updateLastUpdatedAfter();
                }

                $logger->debug(sprintf('[%s] Finished writing product page %s', $storeId, $page));

                if (count($items) < $pageSize) {
                    break;
                }

                $entityCollection->setPage( ++$page, $pageSize);
                $entityCollection->clear();
            }
        }
        // products
        $xmlWriter->endElement();

        $logger->debug(sprintf('[%s] Product generator: processed items: %s, pages: %s', $storeId, $processed, $page));

        return true;
    }

    protected function writeData(XmlWriter $xmlWriter, Productdelete $product, array $attributes = [], array $extraAttributes = [])
    {

        // function to write a single (structured) value
        $writeValue = function ($value, $level, $writeValue) use ($xmlWriter) {
            if (is_array($value)) {
                foreach ($value as $v) {
                    $xmlWriter->startElement('value_' . $level);
                    $writeValue($v, $level + 1, $writeValue);
                    $xmlWriter->endElement();
                }
            } elseif (is_bool($value)) {
                $xmlWriter->text($value ? 'true' : 'false');
            } else {
                $xmlWriter->text($value);
            }
        };
        // function to add a single element with some support for types
        $writeElement = function ($name, $value) use ($xmlWriter, $writeValue) {
            $xmlWriter->startElement($name);
            $writeValue($value, 1, $writeValue);
            $xmlWriter->endElement();
        };

        $xmlWriter->startElement('product');
        $xmlWriter->writeAttribute('id', $product->getId());
        $xmlWriter->writeAttribute('sku', $product->getSku());

        // regular attributes
        foreach ($attributes as $attribute) {
            $writeElement($attribute, $product->getData($attribute));
        }
        
        // product
        $xmlWriter->endElement();
        $xmlWriter->flush();
    }
        
    public function updateLastUpdatedAfter(){
        $LastUpdatedAfter_old = $this->_scopeConfig->getValue('sli_feed_generation/feed/date');
        $date_obj = new \DateTime($LastUpdatedAfter_old); 
        $date_absolute = $date_obj->format("Y-m-d H:i:s");
        $offset = new \DateInterval('PT24H00M00S');
        $date_obj->add($offset);
        $date_absolute_2 = $date_obj->format("Y-m-d");
        $LastUpdatedAfter = $date_absolute_2;
        $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        $this->cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
        $this->configWriter->save('sli_feed_generation/feed/date', $LastUpdatedAfter, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
    }

}
