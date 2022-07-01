<?php
namespace Tatva\Customproductreview\Controller\Adminhtml\Imports;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Tatva\Customproductreview\Model\Customproductreview;
use Tatva\Customproductreview\Model\ResourceModel\CustomproductreviewFactory;

class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;

    protected $customproductreviewFactory;

    protected $customproductreview;

    protected $allowedExtensions = ['csv','application/octet-stream'];

    protected $_messageManager;

    protected $csv;

    public function __construct(
     \Magento\Backend\App\Action\Context $context,
     CustomproductreviewFactory $customproductreviewFactory,
     \Magento\Framework\Filesystem $_filesystem,
     Customproductreview $customproductreview,
     \Magento\Framework\File\Csv $csv,
     \Magento\Framework\Message\ManagerInterface $messageManager,
     DataPersistorInterface $dataPersistor
     ) {
        $this->customproductreviewFactory = $customproductreviewFactory;
        $this->customproductreview = $customproductreview;
        $this->_messageManager = $messageManager;
        $this->_filesystem = $_filesystem;
        $this->dataPersistor = $dataPersistor;
        $this->csv = $csv;
    parent::__construct($context); // If your class doesn't have a parent, you don't need to do this, of course.
}

public function execute()
{

 $data = $this->getRequest()->getPostValue();
 $file=$data['Customproductreview']['upload_file'];


 try
 {


  $ext = explode(".",$file['0']['name']);
  $count = count($ext);
  $extension = $ext[$count - 1];
  $model = $this->customproductreview->getCollection();

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
                $descriptions = array();
                $getduplicatedescriptions='';

                foreach($model as $collection){
                    $descriptions[] = $collection->getReviewDetail();
                }

                foreach ($importRawData as $data){
                    $getimports = $data[0];

                    if ($counter == 1)
                    {
                        $counter++;
                        continue;
                    }

                    if(!in_array($getimports,$descriptions)){
                        $customproductreviewModel = $this->customproductreview;

                        $importSQL = $customproductreviewModel->importCsv($data);
                        //echo $importSQL;
                        if ($importSQL != '1')
                            $inValidData++;
                        else
                            $num_products++;

                        $counter++; 
                    }
                    else
                    {
                        $getduplicatedescriptions .= ucfirst($getimports).",";
                    }
                    $string = rtrim($getduplicatedescriptions ,',');

                }
            }
        //} 
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
     $this->_messageManager->addSuccessMessage(__('Product Review imported successfully..'));
     $this->_redirect('customproductreview/index/index');
     return;
 }

}
