<?php
namespace Tatva\Sentence\Controller\Adminhtml\Imports;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Controller\ResultFactory;

class Imports extends \Magento\Framework\App\Action\Action
{

    protected $uploaderFactory;


    protected $_fileCsv;

    protected $allowedExtensions = ['text/csv', 'csv', 'application/download', 'text/comma-separated-values', 'application/ms-excel', 'application/octet-stream', 'application/vnd.ms-excel', 'text/x-comma-separated-values', '"text/x-comma-separated-values"'];

    protected $_messageManager;

    public function __construct(
       \Magento\Framework\App\Action\Context $context,
       \Magento\Framework\Filesystem $_filesystem,
       \Magento\Framework\File\Csv $fileCsv,
       UploaderFactory $uploaderFactory,
       \Magento\Framework\Message\ManagerInterface $messageManager 
       ) {
        $this->_filesystem = $_filesystem;
        $this->_fileCsv = $fileCsv;
        $this->uploaderFactory = $uploaderFactory;
        $this->_messageManager = $messageManager;
    parent::__construct($context); // If your class doesn't have a parent, you don't need to do this, of course.
}

public function execute()
{
// This is the directory where you put your CSV file.
    $path = $mediapath=$this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(). "sentence_import";
    ;
    $files=$this->getRequest()->getFiles('Sentence')['upload_file'];
    $fileId='Sentence[upload_file]';

    try
    {
        if (!isset($files['tmp_name'])) 
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));

        $uploader = $this->uploaderFactory->create(['fileId' => $fileId])
        ->setFilesDispersion(false)
        ->setAllowRenameFiles(false)
        ->setAllowedExtensions($this->allowedExtensions);

        if (!$result = $uploader->save($path,$files['name'])) {

            throw new \Magento\Framework\Exception\LocalizedException(__('File cannot be saved to path: $1',$path));
        }


    }

    catch (Exception $e)
    {
     $this->_messageManager->addErrorMessage(__('Only csv format is allowed to upload.'));
     $this->_redirect('*/*/');
     return;
 }

 return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
}

}