<?php
namespace Tatva\Freesamples\Controller\Index;

class Downloadsample extends \Tatva\Freesamples\Controller\Index
{

    /**
     * @var \Magento\Downloadable\Model\LinkFactory
     */
    protected $downloadableLinkFactory;

    /**
     * @var \Magento\Downloadable\Helper\File
     */
    protected $downloadableFileHelper;

    public function __construct(
        \Magento\Downloadable\Model\LinkFactory $downloadableLinkFactory,
        \Magento\Downloadable\Helper\File $downloadableFileHelper,
        \Magento\Framework\App\Filesystem\DirectoryList $_directorylist
    ) {
        $this->downloadableLinkFactory = $downloadableLinkFactory;
        $this->downloadableFileHelper = $downloadableFileHelper;
        $this->_directorylist=$_directorylist;
    }
    public function execute(){

        $file_path = $this->_directorylist->getPath('media/catalog/sample_product');
        //$filename = "sample.zip";
    	$filename = "Free_Sample_Download.pptx";
		$linkId = $this->getRequest()->getParam('link_id', 0);
    	$link = $this->downloadableLinkFactory->create()->load($linkId);
    	$resource = $this->downloadableFileHelper->getFilePath($file_path, $filename);
        $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
        $this->_processDownload($resource, $resourceType);
    	exit(0);
    }
}
