<?php
namespace Tatva\Tag\Ui\Component\Listing\Column\Tatvataglisting;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class StatusRenderer extends \Magento\Ui\Component\Listing\Columns\Column
{

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
         $this->_coreSession = $coreSession;
        parent::__construct($context, $uiComponentFactory,$components, $data);
    }
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                $statusArray=$this->getOptions();
                $item['status'] = isset($statusArray[$item['status']])?$statusArray[$item['status']]:''; //Here you can do anything with actual data

            }          
        }

        return $dataSource;
    }
   public  function getOptions()
    {
         $options = array(
            -1=>'Disabled',
            0=>'Pending',
            1=>'Approved',
        );
        return $options;
        
    }
    
  
  }
