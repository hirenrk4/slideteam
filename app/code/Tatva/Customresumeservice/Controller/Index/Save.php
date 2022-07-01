<?php
namespace Tatva\Customresumeservice\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends \Magento\Framework\App\Action\Action
{
	protected $_filesystem;
	protected $_redirect;

	public function __construct
	(
		Context $context,
		\Magento\Framework\App\Response\RedirectInterface $redirectInterface,
		\Magento\Framework\Filesystem $filesystem,
        UploaderFactory $uploaderFactory
	)
	{
		$this->_filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
		$this->_redirect = $redirectInterface;		
		parent::__construct($context);
	}

	public function execute()
	{
		$this->createDir();
		$files = $this->getRequest()->getFiles('myfile');
		$attachment_dir = 'resume_service_files/';
		$path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . $attachment_dir;

        if (isset($files))
        {
            $error = $files["error"];
            if (!is_array($files['name']))
            {
                $fileName = $files["name"];
                $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
                $uniqueFileNameWithoutExt = time() . "_" . $fileNameWithoutExt;
                $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                $uniqueFileNameWithoutExt = str_replace(" ", "_", $uniqueFileNameWithoutExt);
                $finalFileName = $uniqueFileNameWithoutExt . '.' . $fileExt;
                $files['name'] = $finalFileName;
                $uploader = $this->uploaderFactory->create(['fileId' => $files]);
                $uploader->setAllowedExtensions(['pdf','doc','docx','ods','txt']);		      
                $uploader->setAllowRenameFiles(true);		      
                $uploader->setFilesDispersion(false);  
                $uploader->save($path);
            }
            $a = array();
            $a['original'] = $fileName;
            $a['modified'] = $finalFileName;
            echo json_encode($a);
        }
	}

	private function createDir()
	{
		$path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'resume_service_files/';

		if(!file_exists($path)) 
		{
			mkdir($path,'0777');
		}
	}
}