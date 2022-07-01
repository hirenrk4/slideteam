<?php
namespace Tatva\Catalog\Block\Product\View;

use Magento\Framework\Data\Collection;
use Magento\Framework\Json\EncoderInterface;
use Magento\Catalog\Helper\Image;
use Tatva\Generalconfiguration\Helper\Data as GeneralHelper;
use Magento\Framework\App\RequestInterface;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    /**
     * GeneralHelper
     *
     * @var generalHelper
     */
    public $generalHelper;

    /**
     * @var \Tatva\Translate\Model\Translatedata
     */
    public $traslatedata;
    protected $request;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        GeneralHelper $generalHelper,
        \Tatva\Translate\Model\Translatedata $traslatedata,
        RequestInterface $request,
        array $data = array()) {
        $this->generalHelper = $generalHelper;
        $this->traslatedata = $traslatedata;
        $this->request = $request;
        parent::__construct($context, $arrayUtils, $jsonEncoder, $data);
    }
    /**
     * Retrieve collection of gallery images
     *
     * @return Collection
     */
    public function getGalleryImages()
    {
        $product = $this->getProduct();
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                /* @var \Magento\Framework\DataObject $image */
                $image->setData(
                    'small_image_url',
                    $this->_imageHelper->init($product, 'category_page_grid')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_medium_no_frame')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_large_no_frame')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'custom_thumb',
                    $this->_imageHelper->init($product, 'custom_product_thumb_image')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }

        return $images;
    }

    public function getGalleryImagesJson()
    {
        $imagesItems = [];
        foreach ($this->getGalleryImages() as $image) {
            
            $imagesItems[] = [
                'thumb' => $image->getData('small_image_url'),
                'thumb_small' => $image->getData('custom_thumb'),
                'img' => $image->getData('medium_image_url'),
                'full' => $image->getData('large_image_url'),
                'caption' => ($image->getLabel() ?: $this->getProduct()->getName()),
                'position' => $image->getPosition(),
                'isMain' => $this->isMainImage($image),
                'type' => str_replace('external-', '', $image->getMediaType()),
                'videoUrl' => $image->getVideoUrl(),
            ];
        }
        if (empty($imagesItems)) {
            $imagesItems[] = [
                'thumb' => $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                'thumb_small' => $this->_imageHelper->getDefaultPlaceholderUrl('custom_thumb'),
                'img' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                'full' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                'caption' => '',
                'position' => '0',
                'isMain' => true,
                'type' => 'image',
                'videoUrl' => null,
            ];
        }
        return json_encode($imagesItems);
    }

    public function getCurrentlangdata()
    {
        $lang = $this->request->getParam('lang');
        $product_id = $this->getProduct()->getId();
        $translatedata = $this->traslatedata->getTraslatedata($product_id, $lang);
        //$this->_catalogSession->setlang($lang);
        $arrayvalue = array_column($translatedata, 'value', 'attribute_id');

        return $arrayvalue;
    }
    
}