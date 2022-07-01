<?php
namespace Tatva\Customdesignsubcategories\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;

class save extends \Magento\Framework\App\Action\Action
{
	protected $_filesystem;
	protected $_redirect;
	protected $_CustomSesignDubCategoriesModel;

	public function __construct
	(
		Context $context,
		\Magento\Framework\App\Response\RedirectInterface $redirectInterface,
		\Tatva\Customdesignsubcategories\Model\Customdesignsubcategories $customDesignSubCategories,
		\Magento\Framework\Filesystem $filesystem,
        UploaderFactory $uploaderFactory
	)
	{
		$this->_filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
		$this->_redirect = $redirectInterface;
		$this->_CustomDesignSubCategoriesModel = $customDesignSubCategories;
		$page_type = (string)$this->pageType();
		$this->_CustomDesignSubCategoriesModel->saveDataAccPageType($page_type);		
		parent::__construct($context);
	}

	public function execute()
	{
		$this->createDir();
		$files = $this->getRequest()->getFiles('myfile');
		$attachment_dir = $this->_CustomDesignSubCategoriesModel->getAttachementSavingDir();
		//$attachment_dir = 'businessresearch_files/';
		$output_dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . $attachment_dir;

        if (isset($files))
        {
       
            $error = $files['error'];
			if(!is_array($files['name']))
			{
				$fileName = $files["name"];
				$fileNameWithoutExt = pathinfo($fileName,PATHINFO_FILENAME);
				$uniqueFileNameWithoutExt = time()."_".$fileNameWithoutExt;
				$fileExt = pathinfo($fileName,PATHINFO_EXTENSION);
				$uniqueFileNameWithoutExt = str_replace(" ","_",$uniqueFileNameWithoutExt);
				$finalFileName = $uniqueFileNameWithoutExt.'.'.$fileExt;
				move_uploaded_file($files["tmp_name"],$output_dir. '/' . $finalFileName);
			}
            
            $a = array();
            $a['original'] = $fileName;
            $a['modified'] = $finalFileName;
            echo json_encode($a);
        }
	}

	private function createDir()
	{
		$path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'businessresearch_files';

		if(!file_exists($path)) 
		{
			mkdir($path,'0777');
		}
	}

	public function pageType()
	{
		$url = $this->_redirect->getRefererUrl();
		$last_slash = strrpos($url,'/');
		$second_last_slash = strrpos($url,'/',-2);
		$this->page_type = str_replace('/','',substr($url,$second_last_slash));
		$this->page_type = str_replace('.html','',$this->page_type);
		return $this->page_type;
	}
}