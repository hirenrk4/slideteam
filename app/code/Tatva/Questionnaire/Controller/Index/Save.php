<?php

namespace Tatva\Questionnaire\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends \Magento\Framework\App\Action\Action
{

    protected $_resultPageFactory;
    protected $_filesystem;
    protected $uploaderFactory;
    protected $_file;    

    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory)
    {
        $this->_filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;	   
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->createDir();
        $files = $this->getRequest()->getFiles('myfile');
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'questionnaire_files/';		

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
                $uploader->setAllowedExtensions(['pdf','ppt','pptx','doc','docx','jpg','jpeg','png','gif','xlsx','xlsm','xlsb','xls','xltx','xltm','xlt','csv','xlam','xla','ods','zip','txt']);		      
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
        $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'questionnaire_files/';
        
        if(!file_exists($path)) 
        {
            mkdir($path,'0777');
        }
    }

}
