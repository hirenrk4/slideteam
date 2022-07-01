<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\EduTech\Block\Adminhtml\Product\Edit\Button;

use \Magento\Framework\UrlInterface;
use \Magento\Framework\View\Element\UiComponent\Context;
use \Magento\Catalog\Model\ProductRepository;

class ProductPreview extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{

    protected $context;
    protected $frontendUrlBuilder;
    protected $productRepository;
    public function __construct(
        Context $context,
        UrlInterface $frontendUrlBuilder,
        ProductRepository $productRepository
    ) {
        $this->context = $context;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->productRepository = $productRepository;
    }
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if ($this->context->getRequestParam('id')) {
            return [
                'label' => __('Preview'),
                'class' => 'action-secondary',
                'on_click' => sprintf('window.open(\'' . $this->getPreviewUrl() . '\',\'_blank\')',),
                'sort_order' => 20,
            ];
        }
        return '';
    }
    private function getPreviewUrl()
    {
        $productId = $this->context->getRequestParam('id');
        $product = $this->productRepository->getById($productId);
        $product->setStoreId(1);
        return $product->getProductUrl();
    }
}
