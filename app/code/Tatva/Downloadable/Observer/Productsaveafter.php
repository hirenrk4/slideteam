<?php
namespace Tatva\Downloadable\Observer;
use Magento\Framework\Event\ObserverInterface;

class Productsaveafter implements ObserverInterface
{
	protected $_driveFile;
	protected $_directory;
	protected $_tagModel;

	public function __construct(
		\Tatva\Tag\Model\Tag $tagModel,
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Framework\Filesystem\DirectoryList $directory,
		\Magento\Framework\Filesystem\Driver\File $driveFile
	) 
	{
		$this->_tagModel =$tagModel;
		$this->_request = $request;
		$this->_directory = $directory;
		$this->_driveFile = $driveFile;
	}
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$postData = $this->_request->getPostValue();
		//$productId = $postData['product']['current_product_id'];
		$_product = $observer->getProduct();
		$productId = $_product->getId();
		
		if(isset($postData['product']['product_tags']))
		{
			$productTags = $postData['product']['product_tags'];
            if ($productTags != "")
	        {
				if( strpos($productTags, ';') !== false ) {
					
	     			$item = explode(";", $productTags);
					$productTags = implode(",", $item);
		        }
				$this->_tagModel->saveTags($productTags, $productId, $only_inserts = false);
		    }
		}

		if(isset($postData['product']['media_gallery']))
		{
			$galleryData = $postData['product']['media_gallery'];

	        $removeImages = array();
	        foreach($galleryData['images'] as $key => $imageData)
	        {
	            if(isset($imageData['removed']) && $imageData['removed'] == 1)
	            {
	                $removeImages[] = str_replace("//", "/", $imageData['file']);
	            }
	        }

	        $rootpath = $this->_directory->getRoot();

	        // Get product media gallery images
	        if(!empty($removeImages))
	        {
	            foreach($removeImages as $key => $imagename)
	            {                                         
		            $image1 = str_replace("//", "/", $rootpath."/pub/media/catalog/product/cache/");
		            $files = $this->_driveFile->readDirectory($image1);
		            
		            foreach($files as $key2 => $foldercachesize)
		            {
		                $sizecacheImage = str_replace("//", "/", $foldercachesize."/".$imagename);

		                if ($this->_driveFile->isExists($sizecacheImage))  {
		                    $this->_driveFile->deleteFile($sizecacheImage);
		                }
		            }

		            $imagePath = str_replace("//", "/", $rootpath."/pub/media/catalog/product/".$imagename);

		            if ($this->_driveFile->isExists($imagePath))  {
		                $this->_driveFile->deleteFile($imagePath);
		            }
	            }
	        }	
		}
		
	}
}