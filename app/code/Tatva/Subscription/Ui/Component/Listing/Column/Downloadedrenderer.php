<?php
namespace Tatva\Subscription\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class Downloadedrenderer extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @var \Tatva\Subscription\Helper\Data
     */
    protected $subscriptionHelper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = [],
        \Tatva\Subscription\Helper\Data $subscriptionHelper,
        \Magento\Framework\DataObject $row
        ) {
        $this->storeManager = $storeManager;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->row = $row;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if(isset($dataSource['data']['items'])) {
            foreach($dataSource['data']['items'] as & $item) {
                if($item) {
                     $downloaded = $this->subscriptionHelper->checkDownloadsForRenderer($item);
                   if($downloaded<0)
                       $item['downloaded'] =  __('Expired');
                   else if($downloaded!="")
                       $item['downloaded'] = (int)$downloaded;
                   else
                    $item['downloaded']=0;
            }
        }
    }

    return $dataSource;
}


}



?>