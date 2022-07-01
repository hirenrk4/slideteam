<?php
namespace Tatva\Emarsys\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Catalog\Model\ProductFactory as ProductModelFactory;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Emarsys\Emarsys\Helper\Logs as EmarsysHelperLogs;
use Emarsys\Emarsys\Model\ResourceModel\Customer as EmarsysResourceModelCustomer;
use Emarsys\Emarsys\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Model\Config as EavConfig;
use Emarsys\Emarsys\Helper\Data as EmarsysDataHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv;
use Magento\Store\Model\ScopeInterface;

use Emarsys\Emarsys\Model\Emarsysproductexport as ProductExportModel;
use Emarsys\Emarsys\Model\ResourceModel\Emarsysproductexport as ProductExportResourceModel;

use Magento\Framework\Model\AbstractModel;

class Product extends \Emarsys\Emarsys\Model\Product
{
    protected $productCollection;
    protected $imageHelper;
    protected $appEmulation;
    protected $resourceConnection;
    protected $configWriter;


    public function __construct(
            \Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\Message\ManagerInterface $messageManager,
            \Magento\Catalog\Model\ProductFactory $productCollectionFactory,
            \Magento\Catalog\Model\Product $productModel,
            \Magento\Framework\Stdlib\DateTime\DateTime $date,
            \Emarsys\Emarsys\Helper\Logs $logsHelper, 
            \Emarsys\Emarsys\Model\ResourceModel\Customer $customerResourceModel, 
            \Emarsys\Emarsys\Model\ResourceModel\Product $productResourceModel,
            \Emarsys\Emarsys\Model\Emarsysproductexport $productExportModel,
            \Emarsys\Emarsys\Model\ResourceModel\Emarsysproductexport $productExportResourceModel, 
            \Magento\Catalog\Model\CategoryFactory $categoryFactory,
            \Magento\Store\Model\StoreManagerInterface $storeManager, 
            \Magento\Eav\Model\Config $eavConfig,
            \Emarsys\Emarsys\Helper\Data $emarsysHelper, 
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
            \Magento\Framework\File\Csv $csvWriter, 
            \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
            \Emarsys\Emarsys\Model\ApiExport $apiExport,
            \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
            \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, 
            \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productDataCollectionFactory,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
            \Magento\Catalog\Helper\Image $imageHelper, 
            \Magento\Store\Model\App\Emulation $appEmulation, 
            \Magento\Framework\App\ResourceConnection $resourceConnection,
            \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
            \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
            array $data = array()) {
        $this->productCollection = $productCollection;
        $this->productDataCollectionFactory = $productDataCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->categoryCollection = $categoryCollection; 
        $this->imageHelper = $imageHelper;
        $this->appEmulation = $appEmulation;
        $this->resourceConnection = $resourceConnection;
        $this->_registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;

        parent::__construct(
                $context, 
                $registry, 
                $messageManager, 
                $productCollectionFactory,
                $productModel,
                $date, 
                $logsHelper,
                $customerResourceModel, 
                $productResourceModel, 
                $productExportModel,
                $productExportResourceModel,
                $categoryFactory,
                $storeManager,
                $eavConfig,
                $emarsysHelper,
                $scopeConfig, 
                $csvWriter, 
                $directoryList,
                $apiExport, 
                $resource,
                $resourceCollection, 
                $data);
    }
    
    
    public function consolidatedCatalogExport($mode = EmarsysDataHelper::ENTITY_EXPORT_MODE_AUTOMATIC, $includeBundle = null, $excludedCategories = null)
    {
        set_time_limit(0);

        $logsArray['job_code'] = 'product';
        $logsArray['status'] = 'started';
        $logsArray['messages'] = __('Bulk product export started');
        $logsArray['created_at'] = $this->date->date('Y-m-d H:i:s', time());
        $logsArray['run_mode'] = $mode;
        $logsArray['auto_log'] = 'Complete';
        $logsArray['executed_at'] = $this->date->date('Y-m-d H:i:s', time());
        $logId = $this->logsHelper->manualLogs($logsArray, 1);
        $logsArray['id'] = $logId;
        $logsArray['log_action'] = 'sync';
        $logsArray['action'] = 'synced to emarsys';

        try {
            $this->_errorCount = false;
            $this->_mode = $mode;

            $allStores = $this->storeManager->getStores();

            /** @var \Magento\Store\Model\Store $store */
            foreach ($allStores as $store) {
                $this->setCredentials($store, $logId);
            }
            
            foreach ($this->getCredentials() as $websiteId => $website) {
                $emarsysFieldNames = array();
                $magentoAttributeNames = array();

                foreach ($website as $storeId => $store) {
                    foreach ($store['mapped_attributes_names'] as $mapAttribute) {
                        $emarsysFieldId = $mapAttribute['emarsys_attr_code'];
                        $emarsysFieldNames[$storeId][] = $this->productResourceModel->getEmarsysFieldName($storeId, $emarsysFieldId);
                        $magentoAttributeNames[$storeId][] = $mapAttribute['magento_attr_code'];
                    }
                }
                
                $this->productExportResourceModel->truncateTable();

                $defaultStoreID = false;
                
                
                foreach ($website as $storeId => $store) {
                    $currencyStoreCode = $store['store']->getDefaultCurrencyCode();
                    if (!$defaultStoreID) {
                        $defaultStoreID = $store['store']->getWebsite()->getDefaultStore()->getId();
                    }
                    $currentPageNumber = 1;
                    $collection = $this->productExportModel->getCatalogExportProductCollectionCount(
                        $storeId,
                        $currentPageNumber,
                        $magentoAttributeNames[$storeId],
                        $includeBundle,
                        $excludedCategories
                    );

                    $totalCount = $collection->getSize();
                    $batch = \Emarsys\Emarsys\Model\Emarsysproductexport::BATCH_SIZE;
                    $loop = ceil($totalCount/$batch);
                    $feed_product_count = 0;
                    
                    $header = $emarsysFieldNames[$storeId];
                    $removeAttributes = array('price');                    
                    $header = array_diff($header, $removeAttributes);
                    
                    /*$allstoreExistProduct = $this->resourceConnection->getConnection()->fetchAll('SELECT `entity_id` FROM `catalog_product_entity_media_gallery_value`  group by `entity_id` HAVING count(DISTINCT `store_id`) >1');
                    foreach ($allstoreExistProduct as $key => $value) {
                        $allstoreExistProductIDs[]=$value['entity_id'];
                    }*/
                    $catCollection = $this->categoryCollection->create()->addAttributeToSelect('*');
                    $categoryArr = array();
                    foreach($catCollection as $category)
                    {                           
                        $categoryArr[$category->getId()] =  $category->getName();
                    }   
                    
                    
                    for($i = 1; $i <= $loop; $i++)
                    {
                        $currentPageNumber = $i;

                        $collection = $this->productExportModel->getCatalogExportProductCollection(
                            $storeId,
                            $currentPageNumber,
                            $magentoAttributeNames[$storeId],
                            $includeBundle,
                            $excludedCategories
                        );
                        
                        

                        $products = array();

                        foreach ($collection as $product) {

                            $feed_product_count++;

                            $products[$product->getId()] = array(
                                   'entity_id' => $product->getId(),
                                   'params' => serialize(array(
                                       'default_store' => ($storeId == $defaultStoreID) ? $storeId : 0,
                                       'store' => $store['store']->getCode(),
                                       'store_id' => $store['store']->getId(),
                                       'data' => $this->_prepareCsvData($magentoAttributeNames[$storeId], $product,$categoryArr),
                                       'header' => $header,
                                       'currency_code' => $currencyStoreCode,
                                   ))
                            );
                        }
                        
                        $this->configWriter->save('feed_count/emarsys_feed/today_count', $feed_product_count);

                        //$logger->info($products);
                        if (!empty($products)) {                            
                            $this->productExportResourceModel->saveBulkProducts($products);
                        }                       
                    }
                }

                $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
                $this->cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);


                $today = $this->resourceConnection->getConnection()->fetchAll('SELECT `value` FROM core_config_data where `path` LIKE "feed_count/emarsys_feed/today_count"');
                $today_count = $today[0]['value'];

                $yesterday = $this->resourceConnection->getConnection()->fetchAll('SELECT `value` FROM core_config_data where `path` LIKE "feed_count/emarsys_feed/yesterday_count"');
                $yesterday_count = $yesterday[0]['value'];

                $diff = $yesterday_count - $today_count;
                
                if ($diff > 5)
                {
                    $mail = new \Zend_Mail();
                    $message = "Please find below count of Emarsys Feed Genration";
                    $message .= "<br/><br/>Yesterday Count :: ".$yesterday_count;
                    $message .= "<br/><br/>Today Count :: ".$today_count;
                    $message .= "<br/><br/>Difference :: ".$diff;
                    $mail->setFrom("support@slideteam.net",'SlideTeam Support');
                    $mail->setSubject('Today Emarsys Feed Generate Count Less Then Yesterday');
                    $mail->setBodyHtml($message);

                    $to_array = $this->scopeConfig->getValue('feed_count/feed_email/to_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $to_email = explode(",",$to_array);
                    $cc_array = $this->scopeConfig->getValue('feed_count/feed_email/cc_email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $cc_email = explode(",",$cc_array);

                    $send = 0;
                        if(!empty($to_email))
                        {
                            $mail->addTo($to_email);
                            $send = 1;
                        }
                        if(!empty($cc_email))
                        {
                            $mail->addCc($cc_email);
                        }
                        
                        try
                        {
                            if($send) :
                                $mail->send();
                            endif;
                        }catch(Exception $e)
                        {
                            echo $e->getMessage();
                        }
                }

                if (!empty($store)) {
                    list($csvFilePath, $outputFile) = $this->productExportModel->saveToCsv($websiteId);
                    $bulkDir = $this->customerResourceModel->getDataFromCoreConfig(
                        EmarsysDataHelper::XPATH_EMARSYS_FTP_BULK_EXPORT_DIR, 
                        ScopeInterface::SCOPE_WEBSITES, 
                        $websiteId
                    );
                    $outputFile = $bulkDir . $outputFile;
                    $this->moveFile($store['store'], $outputFile, $csvFilePath, $logId, $mode);
                }
            }
            
            $logsArray['id'] = $logId;
            if ($this->_errorCount) {
                $logsArray['status'] = 'error';
                $logsArray['messages'] = __('Product export have an error. Please check.');
            } else {
                $logsArray['status'] = 'success';
                $logsArray['messages'] = __('Product export completed');
            }
            $logsArray['finished_at'] = $this->date->date('Y-m-d H:i:s', time());
            $this->logsHelper->manualLogsUpdate($logsArray);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $logsArray['id'] = $logId;
            $logsArray['emarsys_info'] = __('consolidatedCatalogExport Exception');
            $logsArray['description'] = __("Exception " . $msg);
            $logsArray['message_type'] = 'Error';
            $this->logsHelper->logs($logsArray);
            if ($mode == EmarsysDataHelper::ENTITY_EXPORT_MODE_MANUAL) {
                $this->messageManager->addErrorMessage(
                    __("Exception " . $msg)
                );
            }
        }
    }   
    
    public function _prepareCsvData($magentoAttributeNames,$productData,$categoryArr)
    {       
        $productId = $productData->getEntityId();
        
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $url = $productData->getProductUrl();
        $path = explode("/", rtrim($url,"/"));
        $last = end($path); 
        $productUrl = $baseUrl.''.$last;
        $productUrl = str_replace($baseUrl, "https://www.slideteam.net/", $productUrl);

        $storeId = $this->storeManager->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        $category_small_image = $this->imageHelper->init($productData, 'category_page_grid')->setImageFile($productData->getImage())->getUrl();
        $category_medium_image = $this->imageHelper->init($productData, 'product_page_image_medium')->setImageFile($productData->getImage())->getUrl();
        $this->appEmulation->stopEnvironmentEmulation();
        
        /* getProuctAllStoreData Fucntion Response Code */
        $imageCount = $imageData =[];
        $result = [];
        $imagefind = 0;
        $imagesCountValue = 0;
        
        
        if(!empty($productData->getGallaryDataDefault()))
        {
            $gallaryData = explode(" && ",$productData->getGallaryDataDefault());
            $imagesCountValue = $gallaryData[1];
            $imagesData = explode(",",$gallaryData[2]);
        }
        else
        {
            $imagesData = array();
            $imagesCountValue = 0;
        }
        
        foreach($imagesData as $value)
        {
            if (!empty($value)) {
                $imagefind = 1;
            }
            if(empty($value))
                continue;
                
            $imageData[$productId][] = $value;
        }
        if (!$imagefind) {
            
            for($i=0;$i<6;$i++)
               {
                   $imageData[$productId][$i] = 'NA';
               }
               $imageCount[$productId]['count'] = 0;
            
        } else {
            $count = count($imageData[$productId]);
            $imageCount[$productId]['count'] = $count;
        }
        
        //$logger->info($imageData);
        /* getProuctAllStoreData Fucntion Response Code */
        
        $countVar = $imageCount[$productData->getEntityId()]['count'];
        $small_image_biggersize1 = $small_image_biggersize2 = $small_image_biggersize3 = $small_image_biggersize4 = $small_image_biggersize5 = $small_image_biggersize6 = "NA";
        $remaining = $countVar - 3;
        
        if($countVar > 0)
        {
            if($remaining <= 3) {
                $small_image_biggersize1 = $this->imageHelper->init($productData,'product_page_image_medium')->setImageFile($imageData[$productId][0])->getUrl();
            }else {
                $small_image_biggersize1 = $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][0])->getUrl();
            }
        }

        //$this->_registry->register('current_product',$productData);

        $hero_img_str = $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][0])->getUrl();
        
        $categoryIds = $productData->getCategoryIds();

        $resumecategoryId = $this->scopeConfig->getValue('resume/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $brochurecategoryId = $this->scopeConfig->getValue('brochure/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $spl_thumbimg = NULL;

        if(in_array($resumecategoryId,$categoryIds) || in_array($brochurecategoryId,$categoryIds))
        {                    
            $spl_thumbimg = $this->imageHelper->init($productData,'resume_page_list')->setImageFile($productData->getImage())->getUrl();
        }

        $customCategories = $this->scopeConfig->getValue("resume/general/custom_category_manager",\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $customCategories = explode(',', $customCategories);
        foreach ($customCategories as $categoryid) {
            $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryid;
            $results = $this->resourceConnection->getConnection()->fetchAll($sql);  
            if(!empty($results))
            {
                $customcatids = explode(',',$results[0]['category_list']);   
                if(array_intersect($categoryIds,$customcatids))
                {
                    $spl_thumbimg = $this->imageHelper->init($productData,'resume_page_list')->setImageFile($productData->getImage())->getUrl();    
                }
            }     
        }

        if(!empty($spl_thumbimg))
        {
            $spl_thumbimg = str_replace($baseUrl."media/catalog/product/cache/default_misc_dir", 'https://www.slideteam.net/media/catalog/product/cache/298x427', $spl_thumbimg); 
        }

        $hero_img_name = substr($hero_img_str,(int)strrpos($hero_img_str,'/'));
        $small_img_name = substr($category_small_image,(int)strrpos($category_small_image,'/')); 
        
        
        if($hero_img_name != $small_img_name){
            $is_hero_img_different = 1;
            if($remaining > 3) {
                $small_image_biggersize1 = $category_small_image;
            }else {
                $small_image_biggersize1 = $category_medium_image;
            }
            if($countVar > 1){
                $small_image_biggersize2 = $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][0])->getUrl();
            }
            if($countVar > 2){
                $flag = strpos($this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][1])->getUrl(), $small_img_name);
                if($flag !== false){
                $small_image_biggersize3 =  $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][2])->getUrl();    
                }
                else{
                    $small_image_biggersize3 =  $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][1])->getUrl();
                }
            }
            if($countVar > 3){
                $small_image_biggersize4 =  $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][3])->getUrl();
            }
            if($countVar > 4){
                $small_image_biggersize5 =  $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][4])->getUrl();
            }
            if($countVar > 5){
                $small_image_biggersize6 =  $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][5])->getUrl();
            }
        }
        else{
            if($countVar > 1){
                $small_image_biggersize2 = $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][1])->getUrl();
            }
            if($countVar > 2){
                $small_image_biggersize3 =  $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][2])->getUrl();
            }
            if($countVar > 3){
                $small_image_biggersize4 =  $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][3])->getUrl();
            }
            if($countVar > 4){
                $small_image_biggersize5 =  $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][4])->getUrl();
            }
            if($countVar > 5){
                $small_image_biggersize6 =  $this->imageHelper->init($productData,'category_page_grid')->setImageFile($imageData[$productId][5])->getUrl();
            }
        }
        
        $nodes = explode(",",$productData->getCategoryName());
        
        $attributeData = [];
       
        foreach ($magentoAttributeNames as $attributeName) {
            $attributeOption = $productData->getData($attributeName);
            if (!is_array($attributeOption)) {
                $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeName);
                if ($attribute->getFrontendInput() == 'boolean' || $attribute->getFrontendInput() == 'select'  || $attribute->getFrontendInput() == 'multiselect' ) {
                    $attributeOption = $productData->getAttributeText($attributeName);
                }
            }
            
            if (isset($attributeOption) && $attributeOption != '') {
                
                if ($attributeName == 'description') {
                    $attributeData[] = 'This image is for preview only. The actual downloaded product will be in very high resolution, and will open in Powerpoint itself. Please click on the "More details" button below to download this product';
                }
                elseif($attributeName == 'short_description')
                {
                    $p_short_description = $productData->getShortDescription();
                    $attributeData[] = trim(preg_replace('/\r|\n/', ' ', $p_short_description)); 
                }
                elseif ($attributeName == 'status') {

                    $proname = $productData->getName();
                    $pname = substr($proname, -3);
                    $lastchar = strtolower($pname);

                    if ($productData->getStatus() == 1 && $productData->getTypeId() == 'downloadable' && $lastchar != 'cpb')
                    {
                        $attributeData[] = 'TRUE';
                    } else {
                        $attributeData[] = 'FALSE';
                    }
                } elseif ($attributeName == 'category_ids') {
                    
                    $catepath = $productData->getCategoryPath();
                    
                    if($productData->getTypeId() == 'downloadable')
                    {
                        if(!empty($catepath))
                        {
                            $categoryNames = array();
                            $catpathArr = explode(",",$catepath);
                            
                            foreach($catpathArr as $catpathval)
                            {
                                $categoryPathIds = explode('/', $catpathval);
                                if (count($categoryPathIds) > 3) {
                                    $childCats = [];
                                    $pathIndex = 0;
                                    foreach ($categoryPathIds as $categoryPathId) {
                                        if ($pathIndex <= 1) {
                                            $pathIndex++;
                                            continue;
                                        }
                                        $childCats[] = $categoryArr[$categoryPathId];
                                    }
                                    $categoryNames[] = implode(" > ", $childCats);
                                }
                            }
                            
                            if(!empty($categoryNames))
                                $attributeData[] = implode('|', $categoryNames);
                            else
                                $attributeData[] = "";                      
                            
                        }else{
                            $attributeData[] = "Diagrams > Business";
                        }    
                    }else{
                        $attributeData[] = "Subscription";
                    }
                } elseif (is_array($attributeOption)) {
                    $attributeData[] = implode(',', $attributeOption);
                } elseif ($attributeName == 'image') {
                    $zoom_image = $this->imageHelper->init($productData,'product_page_image_large')->setImageFile($attributeOption)->getUrl();
                    $attributeData[] = str_replace($baseUrl, 'https://www.slideteam.net/', $zoom_image);
                } elseif ($attributeName == 'url_key') {
                    $attributeData[] = $productUrl;
                } elseif ($attributeName == 'small_image') {
                    $imageFile = $productData->getImage();
                    $pos = strrpos($imageFile, '.');
                    $pathEnding = substr($imageFile, $pos + 1);
                    if($pathEnding == 'gif' || $pathEnding == 'GIF') {
                        $product_small_image_for_gif = $this->imageHelper->init($productData, 'category_page_grid')->setImageFile($productData->getSmallImage())->getUrl();
                        $attributeData[] = str_replace($baseUrl, 'https://www.slideteam.net/', $product_small_image_for_gif);
                    }
                    else{
                        $attributeData[] = str_replace($baseUrl, 'https://www.slideteam.net/', $category_small_image);
                    }
                } elseif( $attributeName == 'sku') {
                    $attributeData[] = $productData->getEntityId();
                } else {
                    $attributeData[] = $attributeOption;
                }
            }
            else {
                
                if ($attributeName == 'description') {
                    $attributeData[] = 'This image is for preview only. The actual downloaded product will be in very high resolution, and will open in Powerpoint itself. Please click on the "More details" button below to download this product';
                }
                elseif($attributeName == 'short_description')
                {
                    $p_short_description = $productData->getShortDescription();
                    $attributeData[] = trim(preg_replace('/\r|\n/', ' ', $p_short_description)); 
                }
                elseif ($attributeName == 'url_key') {
                    $attributeData[] = $productUrl;
                } elseif ($attributeName == 'image') {
                    $zoom_image = $this->imageHelper->init($productData,'product_page_image_large')->setImageFile($attributeOption)->getUrl();
                    $attributeData[] = str_replace($baseUrl, 'https://www.slideteam.net/', $zoom_image);
                } elseif ($attributeName == 'c_date_added') {
                    $attributeData[] = $productData->getCreatedAt();
                } elseif ($attributeName == 'category_ids') {
                    
                    $catepath = $productData->getCategoryPath();
                    
                    if($productData->getTypeId() == 'downloadable')
                    {
                        if(!empty($catepath))
                        {
                            $categoryNames = array();
                            $catpathArr = explode(",",$catepath);
                            foreach($catpathArr as $catpathval)
                            {
                                $categoryPathIds = explode('/', $catpathval);
                                
                                if (count($categoryPathIds) > 3) {
                                    $childCats = [];
                                    $pathIndex = 0;
                                    foreach ($categoryPathIds as $categoryPathId) {
                                        if ($pathIndex <= 1) {
                                            $pathIndex++;
                                            continue;
                                        }
                                        $childCats[] = $categoryArr[$categoryPathId];
                                    }
                                    $categoryNames[] = implode(" > ", $childCats);
                                }                           
                            }
                            if(!empty($categoryNames))
                                $attributeData[] = implode('|', $categoryNames);
                            else
                                $attributeData[] = "";
                        } else {
                            $attributeData[] = "";
                        }
                    }else{
                        $attributeData[] = "Subscription";
                    }
                                        
                } elseif($attributeName == 'c_number_of_times_downloaded') {
                    $downcount = $productData->getDownloadCount();
                    if(empty($downcount))
                        $downcount = 0;
                    $attributeData[] = $downcount;
                } elseif ($attributeName == 'c_number_of_slides') {
                    $attributeData[] = $imagesCountValue;
                } elseif($attributeName == 'c_thumbnail_1') {
                    $attributeData[] = str_replace($baseUrl, 'https://www.slideteam.net/', $small_image_biggersize1);                   
                } elseif($attributeName == 'c_thumbnail_2') {
                    $attributeData[] = str_replace($baseUrl, 'https://www.slideteam.net/', $small_image_biggersize2);
                } elseif($attributeName == 'c_thumbnail_3') {
                    $attributeData[] = str_replace($baseUrl, 'https://www.slideteam.net/', $small_image_biggersize3);
                } elseif($attributeName == 'c_thumbnail_4') {
                    $attributeData[] = str_replace($baseUrl, 'https://www.slideteam.net/', $small_image_biggersize4);
                } elseif($attributeName == 'c_thumbnail_5') {
                    $attributeData[] = str_replace($baseUrl, 'https://www.slideteam.net/', $small_image_biggersize5);
                } elseif ($attributeName == 'c_thumbnail_6') {
                    $attributeData[] = str_replace($baseUrl, 'https://www.slideteam.net/', $small_image_biggersize6);
                } elseif ($attributeName == 'c_spl_thumbnail') {
                    if(!empty($spl_thumbimg))
                    {
                        $attributeData[] = $spl_thumbimg;
                    }
                    else
                    {
                        $attributeData[] = "N/A";
                    }
                } elseif ($attributeName == 'c_nodes') {
                    $nodename = "NA";
                    foreach($nodes as $node)
                    {
                        if(!empty($node))
                        {
                            $nodename = $node;
                        }
                    }
                    $attributeData[] = $nodename;                   
                }
                else {
                    $attributeData[] = '';
                }
            }
        }
        
        return $attributeData;
    }
    
    public function getProuctAllStoreData($productId,$productData) {        
        $imageCount = $imageData =[];
        $result = [];
        
        $allstoreExistProduct = $this->resourceConnection->getConnection()->fetchAll('SELECT `entity_id` FROM `catalog_product_entity_media_gallery_value`  group by `entity_id`   HAVING count(DISTINCT `store_id`) >1');

        foreach ($allstoreExistProduct as $key => $value) {
            $allstoreExistProductIDs[]=$value['entity_id'];
        }
        
        if(in_array($productId, $allstoreExistProductIDs))
        {
            $allstoreimagesCollection = $this->resourceConnection->getConnection()->fetchAll('SELECT cg.value,cv.entity_id FROM `catalog_product_entity_media_gallery_value` as cv
            LEFT JOIN catalog_product_entity_media_gallery as cg ON cg.value_id =cv.value_id WHERE cv.entity_id ='.$productId.' AND store_id=0');
            
            if(!empty($allstoreimagesCollection)) {
                foreach ($allstoreimagesCollection as $key => $value) {
                    $imageData[$value['entity_id']][] = $value['value'];
                    foreach ($imageData as $k => $imageValue) {
                        $count = count($imageValue);
                    }
                    $imageCount[$value['entity_id']]['count'] = $count;
                }
            } else{
                for($i=0;$i<6;$i++)
                {
                    $imageData[$productId][$i] = 'NA';
                }
                $imageCount[$productId]['count'] = 0;
            }
        }
        else{
            $imagesCollection = $this->resourceConnection->getConnection()->fetchAll('SELECT cg.value,cv.entity_id FROM `catalog_product_entity_media_gallery_value` as cv
            LEFT JOIN catalog_product_entity_media_gallery as cg ON cg.value_id =cv.value_id WHERE cv.entity_id ='.$productId.' ');
            
            if(!empty($imagesCollection)) {
                foreach ($imagesCollection as $key => $value) {
                    $imageData[$value['entity_id']][] = $value['value'];
                    foreach ($imageData as $k => $imageValue) {
                        $count = count($imageValue);
                    }
                    $imageCount[$value['entity_id']]['count'] = $count;
                }
            }else{
                for($i=0;$i<6;$i++)
                {
                    $imageData[$productId][$i] = 'NA';
                }
                $imageCount[$productId]['count'] = 0;
            }
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

    public function moveFile($store, $outputFile, $csvFilePath, $logId, $mode)
    {
        $apiExportEnabled = $store->getConfig(EmarsysDataHelper::XPATH_PREDICT_API_ENABLED);

        if ($apiExportEnabled) {
            $merchantId = $store->getConfig(EmarsysDataHelper::XPATH_PREDICT_MERCHANT_ID);
            //get token from admin configuration
            $token = $store->getConfig(EmarsysDataHelper::XPATH_PREDICT_TOKEN);

            //Assign API Credentials
            $this->apiExport->assignApiCredentials($merchantId, $token);

            //Get catalog API Url
            $apiUrl = $this->apiExport->getApiUrl(\Magento\Catalog\Model\Product::ENTITY);

            //Export CSV to API
            $apiExportResult = $this->apiExport->apiExport($apiUrl, $csvFilePath);

            if ($apiExportResult['result'] == 1) {
                //successfully uploaded file on Emarsys
                $logsArray['id'] = $logId;
                $logsArray['job_code'] = 'product';
                $logsArray['emarsys_info'] = __('File uploaded to Emarsys');
                $logsArray['description'] = __('File uploaded to Emarsys. File Name: %1. API Export result: %2', $csvFilePath, $apiExportResult['resultBody']);
                $logsArray['message_type'] = 'Success';
                $this->logsHelper->logs($logsArray);
                $this->_errorCount = false;
                if ($mode == EmarsysDataHelper::ENTITY_EXPORT_MODE_MANUAL) {
                    $this->messageManager->addSuccessMessage(
                        __("File uploaded to Emarsys successfully !!!")
                    );
                }
            } else {
                //Failed to export file on Emarsys
                $this->_errorCount = true;
                $msg = $apiExportResult['resultBody'];
                $logsArray['id'] = $logId;
                $logsArray['job_code'] = 'product';
                $logsArray['emarsys_info'] = __('Failed to upload file on Emarsys');
                $logsArray['description'] = __('Failed to upload file on Emarsys. %1' , $msg);
                $logsArray['message_type'] = 'Error';
                $this->logsHelper->logs($logsArray);
                if ($mode == EmarsysDataHelper::ENTITY_EXPORT_MODE_MANUAL) {
                    $this->messageManager->addErrorMessage(
                        __("Failed to upload file on Emarsys !!! " . $msg)
                    );
                }
            }
        } else {
            $fileContent = file_get_contents($outputFile);
            $fh = fopen($csvFilePath, 'r+');
            //fputs($fh,$fileContent);
            fclose($fh);
        }
        //unlink($csvFilePath);
    }
    
}