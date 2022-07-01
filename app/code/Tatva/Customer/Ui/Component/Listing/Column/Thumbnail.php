<?php
namespace Tatva\Customer\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{

	protected $productCollection;
	protected $imageHelper;

    public function __construct(
    	\Magento\Framework\View\Element\UiComponent\ContextInterface $context, 
    	\Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
    	\Magento\Store\Model\StoreManagerInterface $storeManager,
    	\Magento\Catalog\Model\Product $productCollection,
    	\Magento\Catalog\Helper\Image $imageHelper, 
    	array $components = array(), 
    	array $data = array()
    	) 
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_storeManager = $storeManager;
        $this->productCollection = $productCollection;
        $this->imageHelper = $imageHelper;

    }

    public function prepareDataSource(array $dataSource)
    {
    	if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
            	$product_id = $item['product_id'];
                $detail = $this->productCollection->load($product_id);

                $thumbnailImage = $this->imageHelper->init($detail,'thumbnail')->setImageFile($detail->getImage())->resize(97)->getUrl();
                $mainimage = $this->imageHelper->init($detail,'image')->setImageFile($detail->getImage())->getUrl();

                $item[$fieldName . '_src'] = $thumbnailImage;
                $item[$fieldName . '_alt'] = $detail->getName();
                $item[$fieldName . '_orig_src'] = $mainimage;
                $item[$fieldName . '_rel'] = "imgtip[$product_id]";
            }
        }
        return $dataSource;
    }

    protected function getAlt($row)
	{
		$altField = $this->getData('config/altField') ?: self::ALT_FIELD;
	   	return isset($row[$altField]) ? $row[$altField] : null;
	}
}