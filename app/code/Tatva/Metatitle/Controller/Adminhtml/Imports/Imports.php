<?php
namespace Tatva\Metatitle\Controller\Adminhtml\Imports;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Controller\ResultFactory;

class Imports extends \Magento\Backend\App\Action
{

    protected $uploaderFactory;


    protected $_fileCsv;

    protected $allowedExtensions = ['csv','application/octet-stream'];

    protected $_messageManager;

    public function __construct(
       \Magento\Backend\App\Action\Context $context,
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
        $path = $mediapath=$this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(). "metatitle_import";
        ;
        $files=$this->getRequest()->getFiles('Metatitle')['upload_file'];
        $fileId='Metatitle[upload_file]';

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