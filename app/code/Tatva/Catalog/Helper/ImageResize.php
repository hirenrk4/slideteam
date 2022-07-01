<?php

namespace Tatva\Catalog\Helper;
use \Magento\Framework\App\Filesystem\DirectoryList;

class ImageResize extends \Magento\Framework\App\Helper\AbstractHelper
{
	public function __construct(
    	\Magento\Framework\Filesystem\Driver\File $fileDriver,
    	\Magento\Framework\App\Filesystem\DirectoryList $dir,
    	\Magento\Framework\Filesystem $fileSystem,
    	\Magento\Framework\Image\AdapterFactory $imageFactory,
    	\Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
    	$this->_fileDriver = $fileDriver;
    	$this->_dir = $dir;
    	$this->_fileSystem = $fileSystem;
    	$this->_imageFactory = $imageFactory;
    	$this->_storeManager = $storeManager;
    }

    public function ImageGenerate($file,$type,$isacpect=false)
    {
    	if($type == "small_image")
    	{
    		$width = "330";
    		$height = "186";
    		$combo = "330x186";
    	}
    	elseif($type == "medium_image")
    	{
    		$width = "560";
    		$height = "315";
    		$combo = "560x315";
    	}
    	elseif($type == "thumbnail")
    	{
    		$width = "80";
    		$height = "115";
    		$combo = "80x115";
    	}
    	elseif($type == "main_image")
    	{
    		$width = "1280";
    		$height = "720";
    		$combo = "1280x720";
    	}

    	$imageResized = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('catalog/product/cache/'.$combo.'/'.ltrim($file,'/'));

    	if (!$this->_fileDriver->isExists($imageResized)) {

    		$absolutePath = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('catalog/product/'.ltrim($file,'/'));
    		
    		$imageResize = $this->_imageFactory->create();
			$imageResize->open($absolutePath);
			$imageResize->constrainOnly(TRUE);        
			$imageResize->keepTransparency(TRUE);        
			$imageResize->keepFrame(FALSE);        
			$imageResize->keepAspectRatio($isacpect);
			$imageResize->resize($width,$height);  
			$destination = $imageResized;
			$imageResize->save($destination);

			$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product/cache/'.$combo.'/'.ltrim($file,'/');

			return $mediaUrl;
    	}
    	else
    	{
    		$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product/cache/'.$combo.'/'.ltrim($file,'/');

    		return $mediaUrl;
    	}

    }
}