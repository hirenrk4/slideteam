<?php
namespace Tatva\EduTech\Plugin\Backend\Catalog\Grid;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProductActions
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var UrlInterface
     */
    protected $frontendUrlBuilder;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    public function __construct(
        ContextInterface $context,
        UrlInterface $frontendUrlBuilder,
        StoreManagerInterface $storeManager
    )
    {
        $this->context = $context;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->storeManager = $storeManager;
    }
    
    public function afterPrepareDataSource(
        \Magento\Catalog\Ui\Component\Listing\Columns\ProductActions $subject,
        array $dataSource
    ) {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');
            $this->frontendUrlBuilder->setScope($storeId);
            
            foreach ($dataSource['data']['items'] as $key => $item) {
                $href = '';
                if($item['type_id'] == 'downloadable') {
                    if(isset($item['url_key']))
                    {
                        $href = $this->frontendUrlBuilder->getBaseUrl().$item['url_key'].'.html';
                    }
                    else
                    {
                        $href = $this->frontendUrlBuilder->getUrl(
                        'catalog/product/view',
                        [
                            'id' => $item['entity_id'],
                            '_current' => false,
                            '_nosid' => true,
                            'admin_preview' => 1,
                        ]                        
                        );  
                    }
                }else {
                    $href = $this->frontendUrlBuilder->getUrl(
                        'catalog/product/view',
                        [
                            'id' => $item['entity_id'],
                            '_current' => false,
                            '_nosid' => true,
                            'admin_preview' => 1,
                        ]                        
                        );
                }
                $dataSource['data']['items'][$key][$subject->getData('name')]['preview'] = [
                    'href' => $href ,
                    'target' => '_blank',
                    'label' => __('Preview'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}