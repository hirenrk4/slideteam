<?php
namespace Tatva\OrderReport\Controller\Adminhtml\OrderReport;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Magento\Framework\App\Action\Action
{
   
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Tatva\OrderReport\Model\GenerateCSV $generateCsv
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        
        $this->_fileFactory = $fileFactory;
        $this->_generateCsv = $generateCsv;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }   
 
    public function execute() {
        $post = (array) $this->getRequest()->getParams();
        
        $this->_generateCsv->CsvGenerate($post);
        
        $this->_redirect('orderreport/orderreport/index');
        return;
    }

    
}