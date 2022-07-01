<?php
namespace Tatva\Metatitle\Controller\Adminhtml\Imports;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Tatva\Metatitle\Model\Metatitle;
use Tatva\Metatitle\Model\ResourceModel\MetatitleFactory;

class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;

    protected $metatitleFactory;

    protected $metatitle;

    protected $allowedExtensions = ['csv','application/octet-stream'];

    protected $_messageManager;

    protected $csv;

    public function __construct(
     \Magento\Backend\App\Action\Context $context,
     MetatitleFactory $metatitleFactory,
     \Magento\Framework\Filesystem $_filesystem,
     Metatitle $metatitle,
     \Magento\Framework\File\Csv $csv,
     \Magento\Framework\Message\ManagerInterface $messageManager,
     DataPersistorInterface $dataPersistor
     ) {
        $this->metatitleFactory = $metatitleFactory;
        $this->metatitle = $metatitle;
        $this->_messageManager = $messageManager;
        $this->_filesystem = $_filesystem;
        $this->dataPersistor = $dataPersistor;
        $this->csv = $csv;
    parent::__construct($context); // If your class doesn't have a parent, you don't need to do this, of course.
    }

    public function execute()
    {

        $data = $this->getRequest()->getPostValue();
        $file=$data['Metatitle']['upload_file'];


        try
        {

            $ext = explode(".",$file['0']['name']);
            $count = count($ext);
            $extension = $ext[$count - 1];
            $model = $this->metatitle->getCollection();

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
                $titles = array();
                $getduplicatetitles='';

                foreach($model as $collection){
                    $titles[] = $collection->getMetatitle();
                }

                foreach ($importRawData as $data){
                    $getimports = $data[0];

                    if ($counter == 1)
                    {
                        $counter++;
                        continue;
                    }

                    if(!in_array($getimports,$titles)){
                        $metatitleModel = $this->metatitle;

                        $importSQL = $metatitleModel->importCsv($data);
                            //echo $importSQL;
                        if ($importSQL != '1')
                            $inValidData++;
                        else
                            $num_products++;

                        $counter++; 
                    }
                    else
                    {
                        $getduplicatetitles .= ucfirst($getimports).",";
                    }
                    $string = rtrim($getduplicatetitles ,',');

                }
            }
            //} 
        }
        catch (Exception $e)
        {
            $this->_messageManager->addError(__('Only csv format is allowed to upload.'));
            $this->_redirect('*/*/');
            return;
        }
        if(!empty($getduplicatetitles))
        {
            $this->_messageManager->addNotice(__($string . " Are Already Added.."));
            $this->_redirect('*/*/');
            return;
        }
        $this->_messageManager->addSuccess(__('Metatitles imported successfully..'));
        $this->_redirect('metatitle/index/index');
        return;
    }

}
