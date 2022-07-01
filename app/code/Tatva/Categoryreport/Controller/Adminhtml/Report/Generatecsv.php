<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Categoryreport\Controller\Adminhtml\Report;

class Generatecsv extends \Magento\Framework\App\Action\Action
{

    protected $_resultPageFactory;
    protected $_productCollectionFactory;
    protected $_categoryFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_productCollectionFactory = $productCollection;
        $this->_categoryFactory = $categoryFactory;
        $this->filesystem = $filesystem;  
        $this->directoryList = $directoryList;
        $this->csvProcessor = $csvProcessor;
        $this->fileFactory = $fileFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $category_ids = $this->getRequest()->getParam('category_products');
        
        $fileDirectoryPath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        if(!is_dir($fileDirectoryPath)) :
            mkdir($fileDirectoryPath, 0777, true);
        endif;
        $fileName = 'category_product_export.csv';
        $filePath =  $fileDirectoryPath . '/' . $fileName;
        //$data = [];
        $data[] = ["id"=>"Product Id","name"=>"Product Name","sku"=>"Sku","f_url"=>"Product Url","b_url"=>"Product Admin Url","c_date"=>"Created At"];
        
        foreach($category_ids as $catid)
        {
            $_category = $this->_categoryFactory->create();

            $category = $_category->load($catid);

            //Get category collection
            $catcollection = $category->getCollection()
                    ->addIsActiveFilter()
                    ->addOrderField('name')
                    ->addIdFilter($category->getChildren());            
            $childIds = array();
            foreach($catcollection as $childCategory)
            {
                $childIds[] = $childCategory->getId();
            }            
            $childIds[] = $catid;

            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');        
            $collection->addCategoriesFilter(['in' => $childIds]);

            foreach($collection as $key => $product)
            {
                $backendUrl = "https://scripts.slideteam.net/portexindia/catalog/product/edit/id/".$product->getId();
                
                $data[] = ["id"=>$product->getId(),"name"=>$product->getName(),"sku"=>$product->getSku(),"f_url"=>$product->getProductUrl(),"b_url"=>$backendUrl,"cdate"=>$product->getCreatedAt()];
                
            }           
        }
        $this->csvProcessor
            ->setEnclosure('"')
            ->setDelimiter(',')
            ->saveData($filePath, $data);
        
        return $this->fileFactory->create(
            $fileName,
            [
                'type' => "filename",
                'value' => $fileName,
                'rm' => true,
            ],
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream'
        );
        
    }

}
