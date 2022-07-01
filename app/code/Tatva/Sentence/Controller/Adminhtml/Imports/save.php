<?php
namespace Tatva\Sentence\Controller\Adminhtml\Imports;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Tatva\Sentence\Model\Sentence;
use Tatva\Sentence\Model\ResourceModel\SentenceFactory;

class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;

    protected $sentenceFactory;

    protected $sentence;

    protected $allowedExtensions = ['text/csv', 'csv', 'application/download', 'text/comma-separated-values', 'application/ms-excel', 'application/octet-stream', 'application/vnd.ms-excel', 'text/x-comma-separated-values', '"text/x-comma-separated-values"'];

    protected $_messageManager;

    protected $csv;

    public function __construct(
       \Magento\Backend\App\Action\Context $context,
       SentenceFactory $sentenceFactory,
       \Magento\Framework\Filesystem $_filesystem,
       Sentence $sentence,
       \Magento\Framework\File\Csv $csv,
       \Magento\Framework\Message\ManagerInterface $messageManager,
       DataPersistorInterface $dataPersistor
       ) {
        $this->sentenceFactory = $sentenceFactory;
        $this->sentence = $sentence;
        $this->_messageManager = $messageManager;
        $this->_filesystem = $_filesystem;
        $this->dataPersistor = $dataPersistor;
        $this->csv = $csv;
    parent::__construct($context); // If your class doesn't have a parent, you don't need to do this, of course.
}

public function execute()
{

   $data = $this->getRequest()->getPostValue();
   $file=$data['Sentence']['upload_file'];


   try
   {


      $ext = explode(".",$file['0']['name']);
      $count = count($ext);
      $extension = $ext[$count - 1];

      if ($extension == 'csv')
      {
        if(isset($file['0'])) {
            $data['upload_file'] = $file['0']['name'];
        }
        else {
            $data['upload_file'] = null;
        }

        if(isset($data['upload_file']) && !is_null($data['upload_file']))
        {
          $mediaPath = $file['0']['path'].'/'. $data['upload_file'];

          $importRawData = $this->csv->getData($mediaPath);
      }


                $counter = 1; // to omission first row if it is table headers
                $inValidData = 0;
                $num_products = 0;
                foreach ($importRawData as $data){
                    {

                        $importSQL = $this->sentence->importCsv($data);
                        if ($importSQL != '1')
                            $inValidData++;
                        else
                            $num_products++;

                        $counter++;

                    }
                }
            } 
        }
        catch (Exception $e)
        {
         $this->_messageManager->addErrorMessage(__('Only csv format is allowed to upload.'));
         $this->_redirect('*/*/');
         return;
     }
     if(!empty($getduplicatedescriptions))
     {
       $this->_messageManager->addNoticeMessage(__($string . " Are Already Added.."));
   }
   $this->_messageManager->addSuccessMessage(__('Sentences imported successfully..'));
   $this->_redirect('sentence/index/index');
   return;
}
public function generateDescriptionAction()
{
    $this->sentenceSentenceFactory->create()->generateDescription();
    $this->_messageManager->addSuccessMessage(__('Descriptions are successfully generated.'));
    $this->_redirect('/adminhtml_sentence/');
    return;
}
public function updateProductSentencesAction()
{
    $this->sentenceSentenceFactory->create()->updateProductSentences();
    $this->_messageManager->addSuccessMessage(__('Products Sentences are successfully saved.'));
    $this->_redirect('/adminhtml_sentence/');
    return;
}

}
