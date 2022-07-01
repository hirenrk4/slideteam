<?php

namespace Tatva\EduTech\Helper;

class Product extends \Magento\Catalog\Helper\Product
{
	protected $_request;
    protected $_urlInterface;

    public function __construct(
    	\Magento\Framework\App\Helper\Context $context, 
    	\Magento\Store\Model\StoreManagerInterface $storeManager, 
    	\Magento\Catalog\Model\Session $catalogSession, 
    	\Magento\Framework\View\Asset\Repository $assetRepo, 
    	\Magento\Framework\Registry $coreRegistry, 
    	\Magento\Catalog\Model\Attribute\Config $attributeConfig, 
    	$reindexPriceIndexerData, 
    	$reindexProductCategoryIndexerData, 
    	\Magento\Catalog\Api\ProductRepositoryInterface $productRepository, 
    	\Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
    	\Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
    	$this->_request = $request;
        $this->_urlInterface = $urlInterface;
        parent::__construct($context, $storeManager, $catalogSession, $assetRepo, $coreRegistry, $attributeConfig, $reindexPriceIndexerData, $reindexProductCategoryIndexerData, $productRepository, $categoryRepository);
    }

    public function initProduct($productId, $controller, $params = null)
    {
        // Prepare data for routine
        if (!$params) {
            $params = new \Magento\Framework\DataObject();
        }

        // Init and load product
        $this->_eventManager->dispatch(
            'catalog_controller_product_init_before',
            ['controller_action' => $controller, 'params' => $params]
        );

        if (!$productId) {
            return false;
        }

        try {
            $product = $this->productRepository->getById($productId, false, $this->_storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }

        $previousPath = $this->_request->getServer('HTTP_REFERER');
        $currenturl = $this->_urlInterface->getCurrentUrl();
        if(!(strpos($previousPath,'/admin/') || strpos($previousPath,'/portexindia/'))){
            if (!$this->canShow($product) ) {

                if(!strpos($currenturl,'/catalog/product/gallery/'))
                {
                    return false;
                }
            }
        }

        // if (!$this->canShow($product) && !$this->_request->getParam('admin_preview')) {
        //     return false;
        // }

        if (!in_array($this->_storeManager->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
            return false;
        }

        // Load product current category
        $categoryId = $params->getCategoryId();
        if (!$categoryId && $categoryId !== false) {
            $lastId = $this->_catalogSession->getLastVisitedCategoryId();
            if ($product->canBeShowInCategory($lastId)) {
                $categoryId = $lastId;
            }
        } elseif (!$product->canBeShowInCategory($categoryId)) {
            $categoryId = null;
        }

        if ($categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
            } catch (NoSuchEntityException $e) {
                $category = null;
            }
            if ($category) {
                $product->setCategory($category);
                $this->_coreRegistry->register('current_category', $category);
            }
        }

        // Register current data and dispatch final events
        $this->_coreRegistry->register('current_product', $product);
        $this->_coreRegistry->register('product', $product);

        try {
            $this->_eventManager->dispatch(
                'catalog_controller_product_init_after',
                ['product' => $product, 'controller_action' => $controller]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_logger->critical($e);
            return false;
        }

        return $product;
    }
}