<?php

namespace Tatva\DownloadableImport\Observer;

class Saveproductlog implements \Magento\Framework\Event\ObserverInterface
{
	protected $logger;

    protected $metatitleModel;
	
	public function __construct(
		\Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Tatva\DownloadableImport\Model\MetaDataImport $metaDataImport
	) {
		$this->_coreSession = $coreSession;
        $this->productModel = $productModel;
        $this->storeManager = $storeManager;
        $this->_metaDataImport = $metaDataImport;
	}

 
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		//$successdata=$errordata=[];
        
        $successdata = $this->_coreSession->getSuccessData();
        $errordata = $this->_coreSession->getErrorData();
		$importTimeData = $observer->getData('importData');
        $errorRowData = $importTimeData['errorData'];

        if ($products = $observer->getEvent()->getBunch()) {

           // $this->_metaDataImport->setProductData($products,$this->_metatitleModelFactory,1);
           // $this->_metaDataImport->setProductData($products,$this->_metadescriptionModelFactory,2);
           // $this->_metaDataImport->setProductData($products,$this->_sentenceModelFactory,3);

            foreach ($products as $product) {
                
                $product['sku']= substr($product['sku'], 0, 64);

                $productId = $this->productModel->getIdBySku($product['sku']);
                $storeCode = $product['store_view_code'];
                $storeId = $this->storeManager->getStore($storeCode)->getStoreId();
               
                $categories = $importTimeData['categories'][$product['sku']];

            	if(!in_array($product['sku'],$errorRowData))
            	{
            	    $successdata[$product['sku']]['name']= $product['name'];
            	    $successdata[$product['sku']]['startTime']=$importTimeData['importstarttime'][$product['sku']];
            	    $successdata[$product['sku']]['error']= "No";
                    $successdata[$product['sku']]['status']= 1;
                    $successdata[$product['sku']]['categories']= $categories;
                    if(isset($product['product_tags']) && $product['product_tags']!="")
                    {
                      $successdata[$product['sku']]['product_tags']= $product['product_tags'];   
                    }
                            	   
            	}
            	if(in_array($product['sku'],$errorRowData)){

            	    $errordata[$product['sku']]['name']= $product['name'];
            	    $errordata[$product['sku']]['startTime']=$importTimeData['importstarttime'][$product['sku']];
            	    $errordata[$product['sku']]['error']= $errorRowData[$product['sku']]['error'];
                    $errordata[$product['sku']]['status']= 0;
            	    $errordata[$product['sku']]['categories']= $categories;
                    if(isset($product['product_tags']) && $product['product_tags']!="")
                    {
                      $errordata[$product['sku']]['product_tags']= $product['product_tags'];   
                    }
            	}               
                
            	$this->_coreSession->setSuccessData($successdata);
	            $this->_coreSession->setErrorData($errordata);
            }
        }
        //$logger->info($this->_coreSession->getImageSku());

	}				
}
