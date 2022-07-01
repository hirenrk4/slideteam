<?php

namespace Tatva\DownloadableImport\Plugin;

class StartPlugin{

	protected $productobj;
	protected $_coreSession;
    protected $_storeConfig;
    
    
    public function __construct(
    	\Magento\Catalog\Model\Product $productobj,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $storeConfig
    )
    {
        $this->productobj = $productobj;
        $this->_coreSession = $coreSession;
        $this->_storeConfig = $storeConfig;   
    }

    public function afterExecute(\Tatva\DownloadableImport\Controller\Adminhtml\Import\Start $start,$result)
    {
        $onepageFind = $this->_coreSession->getOnePageFind();
        $productids = $this->_coreSession->getProductIdsArray();
        $baseurl = $this->_storeConfig->getValue('web/unsecure/base_url');
        
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/import_image_cache.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Plugin");
        $logger->info($productids); 
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
		$fileDriver = $objectManager->create('\Magento\Framework\Filesystem\Driver\File');
		$dir = $objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList');
		$fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
		$imageFactory = $objectManager->create('\Magento\Framework\Image\AdapterFactory');
		
        if(!empty($productids))
        {
	        foreach($productids as $productid)
	        {
	            $product = $this->productobj->load($productid);
	            //file_get_contents($product->getProductUrl());
	            //$sku = $product->getSku();			

	            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
	            $nofound = 0;
	            foreach ($existingMediaGalleryEntries as $key => $entry) {
	            	
	                if(!empty($onepageFind))
	                {
						$filepath1 = "media/catalog/product/cache/298x427/".ltrim($entry->getFile(),'/');
				       
						$logger->info($filepath1);
				 		        	        
						if($fileDriver->isExists($filepath1))
						{
				 			$fileDriver->deleteFile($dir->getPath('media')."/catalog/product/cache/298x427/".ltrim($entry->getFile(),'/'));
						}

				 		$absolutePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/'.ltrim($entry->getFile(),'/'));

						$imageResized = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/cache/298x427/'.ltrim($entry->getFile(),'/'));
						//create image factory...
						$imageResize = $imageFactory->create();        
						$imageResize->open($absolutePath);
						$imageResize->constrainOnly(TRUE);        
						$imageResize->keepTransparency(TRUE);        
						$imageResize->keepFrame(FALSE);        
						$imageResize->keepAspectRatio(TRUE);        
						$imageResize->resize(298,427);
						//destination folder                
						$destination = $imageResized ;    
						//save image      
						$imageResize->save($destination);

						$filepath1 = "media/catalog/product/cache/80x115/".ltrim($entry->getFile(),'/');
				 		        	        
						if($fileDriver->isExists($filepath1))
						{
				 			$fileDriver->deleteFile($dir->getPath('media')."/catalog/product/cache/80x115/".ltrim($entry->getFile(),'/'));
						}

				 		$absolutePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/'.ltrim($entry->getFile(),'/'));

						$imageResized = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/cache/80x115/'.ltrim($entry->getFile(),'/'));
						//create image factory...
						$imageResize = $imageFactory->create();        
						$imageResize->open($absolutePath);
						$imageResize->constrainOnly(TRUE);        
						$imageResize->keepTransparency(TRUE);        
						$imageResize->keepFrame(FALSE);        
						$imageResize->keepAspectRatio(TRUE);        
						$imageResize->resize(80,115);
						//destination folder                
						$destination = $imageResized ;    
						//save image      
						$imageResize->save($destination);
						
						$filepath2 = "media/catalog/product/cache/330x186/".ltrim($entry->getFile(),'/');

						$logger->info($filepath2);
						
						if($fileDriver->isExists($filepath2))
						{
				 			$fileDriver->deleteFile($dir->getPath('media')."/catalog/product/cache/330x186/".ltrim($entry->getFile(),'/'));
						}
				 		$absolutePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/'.ltrim($entry->getFile(),'/'));

						$imageResized = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/cache/330x186/'.ltrim($entry->getFile(),'/'));
						//create image factory...
						$imageResize = $imageFactory->create();        
						$imageResize->open($absolutePath);
						$imageResize->constrainOnly(TRUE);        
						$imageResize->keepTransparency(TRUE);        
						$imageResize->keepFrame(FALSE);
						$imageResize->keepAspectRatio(TRUE);
						$imageResize->resize(330,186);  
						//destination folder                
						$destination = $imageResized ;    
						//save image      
						$imageResize->save($destination);	
	                }
	                else
	                { 
						$filepath2 = "media/catalog/product/cache/330x186/".ltrim($entry->getFile(),'/');
						
						//$logger->info($filepath);
						     
						if($fileDriver->isExists($filepath2))
						{
				 			$fileDriver->deleteFile($dir->getPath('media')."/catalog/product/cache/330x186/".ltrim($entry->getFile(),'/'));
						}

				 		$absolutePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/'.ltrim($entry->getFile(),'/'));

						$imageResized = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/cache/330x186/'.ltrim($entry->getFile(),'/'));
						//create image factory...
						$imageResize = $imageFactory->create();        
						$imageResize->open($absolutePath);
						$imageResize->constrainOnly(TRUE);        
						$imageResize->keepTransparency(TRUE);        
						$imageResize->keepFrame(FALSE);        
						$imageResize->keepAspectRatio(TRUE);
						$imageResize->resize(330,186);  
						//destination folder                
						$destination = $imageResized ;    
						//save image      
						$imageResize->save($destination);

						$filepath2 = "media/catalog/product/cache/560x315/".ltrim($entry->getFile(),'/');
						     
						if($fileDriver->isExists($filepath2))
						{
				 			$fileDriver->deleteFile($dir->getPath('media')."/catalog/product/cache/560x315/".ltrim($entry->getFile(),'/'));
						}

				 		$absolutePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/'.ltrim($entry->getFile(),'/'));

						$imageResized = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/cache/560x315/'.ltrim($entry->getFile(),'/'));
						//create image factory...
						$imageResize = $imageFactory->create();        
						$imageResize->open($absolutePath);
						$imageResize->constrainOnly(TRUE);        
						$imageResize->keepTransparency(TRUE);        
						$imageResize->keepFrame(FALSE);        
						$imageResize->keepAspectRatio(TRUE);
						$imageResize->resize(560,315);  
						//destination folder                
						$destination = $imageResized ;    
						//save image      
						$imageResize->save($destination);


						$filepath2 = "media/catalog/product/cache/80x115/".ltrim($entry->getFile(),'/');
						     
						if($fileDriver->isExists($filepath2))
						{
				 			$fileDriver->deleteFile($dir->getPath('media')."/catalog/product/cache/80x115/".ltrim($entry->getFile(),'/'));
						}

				 		$absolutePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/'.ltrim($entry->getFile(),'/'));

						$imageResized = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/cache/80x115/'.ltrim($entry->getFile(),'/'));
						//create image factory...
						$imageResize = $imageFactory->create();        
						$imageResize->open($absolutePath);
						$imageResize->constrainOnly(TRUE);        
						$imageResize->keepTransparency(TRUE);        
						$imageResize->keepFrame(FALSE);        
						$imageResize->keepAspectRatio(TRUE);
						$imageResize->resize(80,115);  
						//destination folder                
						$destination = $imageResized ;    
						//save image      
						$imageResize->save($destination);


						$filepath2 = "media/catalog/product/cache/1280x720/".ltrim($entry->getFile(),'/');
						     
						if($fileDriver->isExists($filepath2))
						{
				 			$fileDriver->deleteFile($dir->getPath('media')."/catalog/product/cache/1280x720/".ltrim($entry->getFile(),'/'));
						}

				 		$absolutePath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/'.ltrim($entry->getFile(),'/'));

						$imageResized = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath('catalog/product/cache/1280x720/'.ltrim($entry->getFile(),'/'));
						//create image factory...
						$imageResize = $imageFactory->create();        
						$imageResize->open($absolutePath);
						$imageResize->constrainOnly(TRUE);        
						$imageResize->keepTransparency(TRUE);        
						$imageResize->keepFrame(FALSE);        
						$imageResize->keepAspectRatio(TRUE);
						$imageResize->resize(1280,720);  
						//destination folder                
						$destination = $imageResized ;    
						//save image      
						$imageResize->save($destination);
	                }
	            }
	        }
        }
        $this->_coreSession->unsOnePageFind();
        $this->_coreSession->unsProductIdsArray();

        //system('cd /home/cloudpanel/htdocs/www.slideteam.net/current  && php bin/magento cache:flush config layout block_html collections reflection db_ddl eav customer_notification config_integration config_integration_api translate config_webservice vertex');
        //system('cd /home/cloudpanel/htdocs/www.slideteam.net/current && php bin/magento cache:clean config layout block_html collections reflection db_ddl eav customer_notification config_integration config_integration_api translate config_webservice vertex');

        return $result;
    }
}