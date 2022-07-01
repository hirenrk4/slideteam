<?php
namespace Tatva\Customdesignservice\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\FileSystem\DirectoryList;

class save extends \Magento\Framework\App\Action\Action
{
	protected $_filesystem;
	protected $_redirect;

	public function __construct
	(
		Context $context,
		\Magento\Framework\App\Response\RedirectInterface $redirectInterface,
		\Magento\Framework\Filesystem $filesystem
	)
	{
		$this->_filesystem = $filesystem;
		$this->_redirect = $redirectInterface;
		parent::__construct($context);
	}

	public function execute()
	{
		$this->createDir();
		$files = $this->getRequest()->getFiles('myfile');
		$attachment_dir = 'design_services_files';
		$output_dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . $attachment_dir;
		if(isset($files))
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
			$a['original']= $fileName;
			$a['modified']=$finalFileName;
			echo json_encode($a);
		}
	}

	private function createDir()
	{
		$path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'design_services_files';

		if(!file_exists($path)) 
		{
			mkdir($path,'0777');
		}
	}

}