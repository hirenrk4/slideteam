<?php
namespace Tatva\Customer\Controller\Adminhtml\Export;

use Magento\Framework\App\Filesystem\DirectoryList;

class Index extends \Magento\Backend\App\Action
{


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ProductFactory $productCollection,
        \Tatva\Catalog\Model\ResourceModel\Productdownloadhistorylog\CollectionAdminReport $adminCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
        ) {
            $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $this->_fileFactory = $fileFactory;
            $this->productCollection = $productCollection;
            $this->adminCollection = $adminCollection;
            $this->storeManager = $storeManager;
            parent::__construct($context);
    }

    public function execute()
    {

        $adminCollection = $this->adminCollection;
        /*Filter Data Product Id*/
        $filters = $this->getRequest()->getParam('filters');
        if(isset($filters['product_id']))
        {
            $from = $filters['product_id']['from'];
            if($from){
                $adminCollection->getSelect()->where('main_table.product_id >= '.$from);
            }

            $to = $filters['product_id']['to'];
            if($to){
                $adminCollection->getSelect()->where('main_table.product_id <= '.$to);
            }
        }
        /*Filter Data Date wise*/
        if(isset($filters['recent_download']))
        {
            if(isset($filters['recent_download']['from'])){
                $from = $filters['recent_download']['from'];
                $adminCollection->getSelect()->where("main_table.download_date >= '".date("Y-m-d", strtotime($from))."'");
            }

            if(isset($filters['recent_download']['to'])){
                $to = $filters['recent_download']['to'];
                $adminCollection->getSelect()->where("main_table.download_date <= '".date("Y-m-d", strtotime($to. "+1 days"))."'");
            }
        }

        /*Filter Data Sku wise*/
        if(isset($filters['sku']))
        {
            $sku = $filters['sku'];
            $adminCollection->getSelect()->where("catalog_product.sku LIKE '%".$sku."%' ");
        }
        
        
        $filepath = 'export/export-'.md5(microtime()).'.csv';
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $columns = ['product_id','product_name','product_url','no_of_download','recent_download'];
        foreach ($columns as $column) {
            $header[] = $column;
        }
        $stream->writeCsv($header);

        foreach ($adminCollection->getData() as $item) {
            $itemData = [];

            $itemData[] = $item['product_id'];
            $productData = $this->productCollection->create()->load($item['product_id']);
            if(!empty($productData->getId()))
            {   
                $itemData[] = $productData->getName();
                //Product Url
                $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
                $url = $productData->getProductUrl();
                $path = explode("/", rtrim($url,"/"));
                $last = end($path); 
                $productUrl = $baseUrl.''.$last;
                $productUrl = str_replace($baseUrl, "https://www.slideteam.net/", $productUrl);
                
                $itemData[] = $productUrl;
            }
            $itemData[] = $item['no_of_download'];
            $itemData[] = $item['recent_download'];
            $stream->writeCsv($itemData);
        }
        $stream->unlock();
        $stream->close();
        return $this->_fileFactory->create('export.csv', [
            'type' => 'filename',
            'value' => $filepath,
            'rm' => true  // can delete file after use
        ], 'var');
        

    }
}