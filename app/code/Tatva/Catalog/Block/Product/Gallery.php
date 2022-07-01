<?php

namespace Tatva\Catalog\Block\Product;

class Gallery extends \Magento\Catalog\Block\Product\Gallery {

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = array()) {
        return parent::__construct($context, $registry, $data);
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        
        $meta_title = $this->getTitle();
        $meta_description = $this->getDescription();
        
        $this->pageConfig->getTitle()->set($meta_title);
        $this->pageConfig->setDescription($meta_description);
        
        return $this;
    }

    public function getPageTitle_t() {
        $title = '';
        $image_label = $this->escapeHtml($this->getCurrentImage()->getLabel());
        if(!($image_label)) {
            $image_label = $this->getProduct()->getSku();
        }
        $search = '_';
        $replace = ' ';
        $image_label_without_uscore = str_replace($search, $replace, $image_label);
        $title = $image_label_without_uscore;
        return $title;
    }

    public function getTitle() {
        $temp_title = $this->getPageTitle_t();
        if ($temp_title != '') {
            $title = htmlspecialchars(html_entity_decode(trim($temp_title), ENT_QUOTES, 'UTF-8'));
        }
        return $title;
    }

    public function getDescription() {
        $product_title = $this->getTitle();
        $description = "Buy the highest quality predesigned $product_title PPT templates, ppt slide designs, and presentation graphics, images and themes.";
        return $description;
    }

}
