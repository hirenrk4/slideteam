<?php
namespace Tatva\Theme\Block\Html;

use \Magento\Catalog\Model\Product\Visibility;

class Head extends \Magento\Framework\View\Element\Template
{
	protected $request;

	protected $_urlInterface;

	protected $_storeManager;

	protected $_scopeConfig;

	protected $catalogCategoryFactory;

	protected $cmspage;

	protected $cmsblock;

    protected $fishpigCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request, 
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Cms\Model\Page $cmspage,
        \Tatva\Portfolio\Block\Navigation $cmsblock,
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $fishpigCollection, 
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = array()
    ){
    	$this->request = $request;
    	$this->_urlInterface = $urlInterface;
    	$this->_storeManager = $storeManager;
    	$this->_scopeConfig = $scopeConfig;
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->cmspage = $cmspage;
        $this->cmsblock = $cmsblock;
        $this->fishpigCollection = $fishpigCollection;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    public function addCanonical()
    {
    	$pageType = $this->getRequest()->getModuleName();
    	$pageIdentifier = $this->request->getFullActionName();
        $pdp = array("catalog_product_view");
    	$page_array = array("catalog_category_view","catalog_product_view","wordpress_index_index");
    	$pageType_array = array("free-business-powerpoint-templates","share-and-download-products","professional-powerpoint-templates","new-powerpoint-templates");
    	$currentUrl = $this->_urlInterface->getCurrentUrl();	
    	$baseurl = $this->_storeManager->getStore()->getBaseUrl();
    	$check_string = $this->_storeManager->getStore(2)->getCode();
    	$canonical_link = "";

    	if ($pageType == "cms")
    	{
    		$store_id = $this->_storeManager->getStore()->getId();
    		if($store_id == 2)
    		{
    			$canonical_link = $currentUrl;	
    		} else {
    			if(strpos($currentUrl, $check_string) !== false) {
    				$canonical_link = str_replace("/".$check_string."/", "", $currentUrl);
    			} else {
    				$canonical_link = $currentUrl;
    			}
    		}
    	} 
    	elseif(in_array($pageType, $pageType_array)) 
    	{
    		if(strpos($currentUrl, $check_string) !== false) {
    			$canonical_link = str_replace("/".$check_string."/", "", $currentUrl);
    		} else {
    			$canonical_link = $currentUrl;
    		}
    	}
    	elseif(!in_array($pageIdentifier, $page_array))
    	{
    		if(strpos($currentUrl, $check_string) !== false) {
    			$canonical_link = str_replace("/".$check_string."/", "", $currentUrl);
    		} else {
    			$canonical_link = $currentUrl;
    		}
    	}
        elseif(in_array($pageIdentifier, $pdp))
        {
            $currentProduct = $this->registry->registry('current_product');
            $productId = $currentProduct->getId();
            $store_id = $this->_storeManager->getStore()->getId();

            if(strpos($currentUrl, $check_string) !== false) {
                $product = $this->productRepository->getById($productId, false, 0);
                $productUrl1 = $product->getData('url_key');

                $product = $this->productRepository->getById($productId, false, 2);
                $productUrl2 = $product->getData('url_key');

                if($productUrl1 == $productUrl2)
                {
                    $base = str_replace("business_powerpoint_diagrams/","",$baseurl);
                    $canonical_link = $base.$productUrl1.'.html';      
                }
                else{
                    $canonical_link = $baseurl.$productUrl2.'.html'; 
                }

            }
            else{
                $product = $this->productRepository->getById($productId, false, $store_id);
                $productUrl = $product->getData('url_key');
                $canonical_link = $baseurl.$productUrl.'.html';
            }

        }

        $this->_eventManager->dispatch(
            'custom_head_event',
            ['Current Url' => $currentUrl]
        );
    	return trim($canonical_link,'/');
    }

    /**
    * Action for create previous nd next relation url
    * @return previous and next url
    */
    public function PrevNext()
    {
    	$pageType = $this->getRequest()->getModuleName();
    	$pageIdentifier = $this->request->getFullActionName();
    	$page_array = array("catalog_category_view","catalog_product_view");
        $pageType_array = array("free-business-powerpoint-templates","share-and-download-products","professional-powerpoint-templates","new-powerpoint-templates");
        $currentUrl = $this->_urlInterface->getCurrentUrl();	
    	$baseurl = $this->_storeManager->getStore()->getBaseUrl();
    	$check_string = $this->_storeManager->getStore(2)->getCode();
    	$prevUrl = "";
    	$nextUrl = "";

    	if ($pageType == "cms")
    	{
    		$identifier = $this->cmspage->getIdentifier();
    		if(strpos($identifier, "portfolio/") !== false)
    		{
    			$testprev = $this->cmsblock->previousPage();
    			$testnext = $this->cmsblock->NextPage();
    			if($testprev != "true") {
    				$prevUrl = $testprev;
    			}
    			if($testnext != "true") {
    				$nextUrl = $testnext;
    			}
    		}
    	}
    	elseif(in_array($pageType,$pageType_array))
    	{
    		$allowedValues = $this->_scopeConfig->getValue('catalog/frontend/grid_per_page');
    		if($pageType == "free-business-powerpoint-templates"){
                $prodCol = $this->getLayout()->createBlock('Tatva\Freesamples\Block\ListProduct')->getLoadedProductCollection();
            }
            elseif($pageType == "share-and-download-products"){
                $prodCol = $this->getLayout()->createBlock('Tatva\Shareanddownloads\Block\ListProduct')->getLoadedProductCollection();
            }
            elseif($pageType == "professional-powerpoint-templates"){
            	$prodCol = $this->getLayout()->createBlock('Tatva\Bestsellers\Block\ListProduct')->getLoadedProductCollection();
            }
            elseif($pageType == "new-powerpoint-templates"){
            	$prodCol = $this->getLayout()->createBlock('Tatva\NewProduct\Block\ListProduct')->getLoadedProductCollection();
            }
            $tool = $this->getLayout()->createBlock('Tatva\Downloadscount\Block\Pager')->setLimit($this->getLayout()->createBlock('Magento\Catalog\Block\Product\ProductList\Toolbar')->getLimit())->setCollection($prodCol);
            $linkPrev = false;
            $linkNext = false;
            if ($tool->getCollection()->getSelectCountSql()) {
            	if ($tool->getLastPageNum() > 1) {
            		if (!$tool->isFirstPage()) {
            			$linkPrev = true;
            			$prevUrl = $tool->getPreviousPageUrl();
            			
            			if(strpos($prevUrl, '&amp;') !== false){
                            $prevUrl = str_replace("&amp;","&",$prevUrl);
                        }

            			if(strpos($prevUrl, "/".$check_string) !== false){
                            $prevUrl = str_replace("/".$check_string,"",$prevUrl);
                        }
            		}
            		if (!$tool->isLastPage()) {
            			$linkNext = true;
                        $nextUrl = $tool->getNextPageUrl();

                        if(strpos($nextUrl, '&amp;') !== false){
                            $nextUrl = str_replace("&amp;","&",$nextUrl);
                        }

                        if(strpos($nextUrl, "/".$check_string) !== false){
                            $nextUrl = str_replace("/".$check_string,"",$nextUrl);
                        }
            		}
            	}
            }
    	}
        elseif($pageType == "wordpress" && $pageIdentifier !== "wordpress_post_view")
        {
            $prodCol = $this->fishpigCollection->create();
            $prodCol->addPostTypeFilter('post');
            $prodCol->addIsViewableFilter();
            $tool = $this->getLayout()->createBlock('FishPig\WordPress\Block\Post\PostList\Pager')->setCollection($prodCol);
            $linkPrev = false;
            $linkNext = false;

            if ($tool->getCollection()->getSelectCountSql()) {
                if ($tool->getLastPageNum() > 1) {
                    if (!$tool->isFirstPage()) {
                        $linkPrev = true;
                        $prevUrl = $tool->getPreviousPageUrl();
                    
                    }
                    if (!$tool->isLastPage()) {
                        $linkNext = true;
                        $nextUrl = $tool->getNextPageUrl();
                    
                    }
                }
            } 

        }
    	elseif($pageIdentifier == 'catalog_category_view')
    	{
    		$allowedValues = $this->_scopeConfig->getValue('catalog/frontend/grid_per_page');
    		$prodCol = $this->getLayout()->createBlock('Tatva\Catalog\Block\Product\ListProduct')->getLoadedProductCollection();
    		
    		$tool = $this->getLayout()->createBlock('Tatva\Downloadscount\Block\Pager')->setLimit($this->getLayout()->createBlock('Magento\Catalog\Block\Product\ProductList\Toolbar')->getLimit())->setCollection($prodCol);
    		$linkPrev = false;
            $linkNext = false;
            if ($tool->getCollection()->getSelectCountSql()) {
            	if ($tool->getLastPageNum() > 1) {
            		if (!$tool->isFirstPage()) {
            			$linkPrev = true;
            			$prevUrl = $tool->getPreviousPageUrl();
            			
            			if(strpos($prevUrl, '&amp;') !== false){
                            $prevUrl = str_replace("&amp;","&",$prevUrl);
                        }

            			if(strpos($prevUrl, "/".$check_string) !== false){
                            $prevUrl = str_replace("/".$check_string,"",$prevUrl);
                        }
            		}
            		if (!$tool->isLastPage()) {
            			$linkNext = true;
                        $nextUrl = $tool->getNextPageUrl();

                        if(strpos($nextUrl, '&amp;') !== false){
                            $nextUrl = str_replace("&amp;","&",$nextUrl);
                        }

                        if(strpos($nextUrl, "/".$check_string) !== false){
                            $nextUrl = str_replace("/".$check_string,"",$nextUrl);
                        }
            		}
            	}
            }

    	}
    	return array("prev"=>$prevUrl,"next"=>$nextUrl);
    }
}