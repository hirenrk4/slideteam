<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\DownloadableImport\Controller\Adminhtml\Import;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\ImportExport\Controller\Adminhtml\ImportResult as ImportResultController;
use Magento\Framework\Controller\ResultFactory;
use Magento\ImportExport\Model\Import;

class Start extends \Magento\ImportExport\Controller\Adminhtml\Import\Start
{
    /**
     * @var \Magento\ImportExport\Model\Import
     */
    protected $importModel;

    /**
     * @var \Magento\Framework\Message\ExceptionMessageFactoryInterface
     */
    private $exceptionMessageFactory;

    /**
     * @var Import\ImageDirectoryBaseProvider
     */
    private $imagesDirProvider;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\ImportExport\Model\Report\ReportProcessorInterface $reportProcessor
     * @param \Magento\ImportExport\Model\History $historyModel
     * @param \Magento\ImportExport\Helper\Report $reportHelper
     * @param Import $importModel
     * @param \Magento\Framework\Message\ExceptionMessageFactoryInterface $exceptionMessageFactory
     * @param Import\ImageDirectoryBaseProvider|null $imageDirectoryBaseProvider
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\ImportExport\Model\Report\ReportProcessorInterface $reportProcessor,
        \Magento\ImportExport\Model\History $historyModel,
        \Magento\ImportExport\Helper\Report $reportHelper,
        Import $importModel,
        \Magento\Framework\Message\ExceptionMessageFactoryInterface $exceptionMessageFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Tatva\ProductImport\Model\ProductImportFactory $productImportModel,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Tatva\Tag\Model\Tag $tagModel,
        \Magento\Framework\UrlInterface $frontUrlModel,
        ?Import\ImageDirectoryBaseProvider $imageDirectoryBaseProvider = null
    ) {
        parent::__construct($context, $reportProcessor, $historyModel, $reportHelper,$importModel,$exceptionMessageFactory);
        $this->importModel = $importModel;
        $this->exceptionMessageFactory = $exceptionMessageFactory;
        $this->_coreSession = $coreSession;
        $this->_productImportModel =$productImportModel;
        $this->_productRepository = $productRepository;
        $this->_tagModel =$tagModel;
        $this->_frontUrlModel =$frontUrlModel;
        $this->imagesDirProvider = $imageDirectoryBaseProvider
            ?? ObjectManager::getInstance()->get(Import\ImageDirectoryBaseProvider::class);
    }

    /**
     * Start import process action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            /** @var \Magento\Framework\View\Result\Layout $resultLayout */
            $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);

            /** @var $resultBlock \Magento\ImportExport\Block\Adminhtml\Import\Frame\Result */
            $resultBlock = $resultLayout->getLayout()->getBlock('import.frame.result');
            $resultBlock
                ->addAction('show', 'import_validation_container')
                ->addAction('innerHTML', 'import_validation_container_header', __('Status'))
                ->addAction('hide', ['edit_form', 'upload_button', 'messages']);

            $this->importModel->setData($data);
            //Images can be read only from given directory.
            $this->importModel->setData('images_base_directory', $this->imagesDirProvider->getDirectory());
            $errorAggregator = $this->importModel->getErrorAggregator();
            $errorAggregator->initValidationStrategy(
                $this->importModel->getData(Import::FIELD_NAME_VALIDATION_STRATEGY),
                $this->importModel->getData(Import::FIELD_NAME_ALLOWED_ERROR_COUNT)
            );

            try {
                $this->importModel->importSource();
            } catch (\Exception $e) {
                $resultMessageBlock = $resultLayout->getLayout()->getBlock('messages');
                $message = $this->exceptionMessageFactory->createMessage($e);
                $html = $resultMessageBlock->addMessage($message)->toHtml();
                $errorAggregator->addError(
                    \Magento\ImportExport\Model\Import\Entity\AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION,
                    \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::ERROR_LEVEL_CRITICAL,
                    null,
                    null,
                    null,
                    $html
                );
            }

            $successProducts =$this->_coreSession->getSuccessData();
            $errorProducts =$this->_coreSession->getErrorData();
            if(!empty($successProducts)){
               $this->saveProductLog($successProducts);
            }

            if(!empty($errorProducts)){
              $this->saveProductLog($errorProducts); 
            }

            if ($this->importModel->getErrorAggregator()->hasToBeTerminated()) {
                $resultBlock->addError(__('Maximum error count has been reached or system error is occurred!'));
                $this->addErrorMessages($resultBlock, $errorAggregator);
            } else {
                $this->importModel->invalidateIndex();

                $noticeHtml = $this->historyModel->getSummary();

                if ($this->historyModel->getErrorFile()) {
                    $noticeHtml .=  '<div class="import-error-wrapper">' . __('Only the first 100 errors are shown. ')
                                    . '<a href="'
                                    . $this->createDownloadUrlImportHistoryFile($this->historyModel->getErrorFile())
                                    . '">' . __('Download full report') . '</a></div>';
                }

                $resultBlock->addNotice(
                    $noticeHtml
                );

                $this->addErrorMessages($resultBlock, $errorAggregator);
                $resultBlock->addSuccess(__('Import successfully done'));
                //system('cd /home/cloudpanel/htdocs/www.slideteam.net/current  && php bin/magento cache:flush config layout block_html collections reflection db_ddl eav customer_notification config_integration config_integration_api translate config_webservice vertex');
                //system('cd /home/cloudpanel/htdocs/www.slideteam.net/current && php bin/magento cache:clean config layout block_html collections reflection db_ddl eav customer_notification config_integration config_integration_api translate config_webservice vertex');
            }
            //$this->_coreSession->unsImageSku();
            $this->_coreSession->unsSuccessData();
            $this->_coreSession->unsErrorData();
            return $resultLayout;
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('adminhtml/*/index');
        return $resultRedirect;
    }
    private function getProductUrl($product) {
        $routeParams = [ '_nosid' => true];

        $routeParams['id'] = $product->getId();
        $routeParams['s'] = $product->getUrlKey();
      return $this->_frontUrlModel->getUrl('catalog/product/view', $routeParams);
    }
    public function loadBySku($sku) {
    $matches = $this->_productImportModel->create()->getCollection()
        ->addFieldToFilter('productsku', $sku);

    foreach ($matches as $match) {
        return $match->getId();
    }
}
    public function saveProductLog($dataProduct){
        $productModel=$this->_productImportModel->create();
         foreach($dataProduct as $key=>$value){
                    $sku=$key;
                    $name=$value['name'];
                    $importSatrtTime=$value['startTime'];
                    $importEndTime=date("Y-m-d H:i:s");
                    $error=$value["error"];
                    $status=$value['status'];
                    $dataArray=['profiler_start_time' =>$importSatrtTime,
                                'productsku'=>$sku,
                                'productname'=>$name,
                                'import_start_time'=>$importSatrtTime,
                                'import_end_time'=>$importEndTime,
                                'status'=>$status,
                                'error'=>$error,
                                'profiler_end_time'=>$importEndTime,
                                'product_categories'=>$value['categories'],
                                'product_download'=>0,
                               ];
                               $productTags="";
                  if(isset($value['product_tags']) && $value['product_tags']!="") 
                   {
                     $productTags=$value['product_tags'];
                     $dataArray['product_tags']=$productTags;
                   }
                   $productObj=$this->_productRepository->get($sku);
                   $productId=$productObj->getId();
                   $url=$productObj->getUrlModel()->getUrl($productObj);
                   $dataArray['product_url']=$url;
                   $id=$this->loadBySku($sku);
                    		
                    try {

                        if($productId > 0)
                        {
                                if (trim($productTags) != "")
                                  $productModel->setProductTags(trim(trim($productTags), ","));
                                  if ($productTags != "")
                                      $this->_tagModel->saveTags($productTags, $productId, $only_inserts = false);

                       }

                        if($id > 0)
                        {
                            $productModel->load($id);
                            $productModel->setData($dataArray);
                            $productModel->setId($id);
                           
                        }
                        else{
                            $productModel->setData($dataArray);
                        }
                        $productModel->save();   
                  }
                   catch (Exception $e){
                            echo $e->getMessage(); 
                    }
                }
    }
}


