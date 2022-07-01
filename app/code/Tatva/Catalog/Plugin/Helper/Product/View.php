<?php

namespace Tatva\Catalog\Plugin\Helper\Product;

class View
{
    public function afterpreparePageMetadata(\Magento\Catalog\Helper\Product\View $catalogProduct,$product)
    {
        $pageConfig = $resultPage->getConfig();
            if(is_object($product))
            {
                if($product->hasData("meta_title"))
                {

        $title = $product->getMetaTitle();
        if ($title) {
            $pageConfig->getTitle()->set($title);
        }
                }       
                
                if($product->hasData("meta_keyword"))
                {               

                    $keyword = $product->getMetaKeyword();
                    $currentCategory = $this->_coreRegistry->registry('current_category');
                    if ($keyword) {
                        $pageConfig->setKeywords($keyword);
                    }
                    elseif ($currentCategory)
                     {
                       $headBlock->setKeywords($product->getName());
                     }
                 }
                 elseif ($currentCategory)
                 {
                    $pageConfig->setKeywords($product->getName());
                 }
            }
                if($product->hasData("meta_description"))
                {               

                    $description = $product->getMetaDescription();
                    if ($description) {
                        $pageConfig->setDescription($description);
                    } else {
                        $pageConfig->setDescription($this->string->substr($product->getDescription(), 0, 255));
                    }
                }
                else
                {
                   $pageConfig->setDescription($this->string->substr($product->getDescription(), 0, 255));
                }

        if ($this->_catalogProduct->canUseCanonicalTag()) {
            $pageConfig->addRemotePageAsset(
                $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }


        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($product->getName());
        }

        return $this;
    }
}