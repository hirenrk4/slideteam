<?php

namespace Tatva\Catalog\Block\Category;

use Magento\Framework\View\Element\Template;

/**
 * Class View
 * @api
 * @package Magento\Catalog\Block\Category
 * @since 100.0.2
 */
use Magento\Catalog\Model\Category;

class Title extends Template {

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    public $catalogOutputHelper;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Output $catalogOutputHelper,
        Category $model,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->catalogOutputHelper = $catalogOutputHelper;
        $this->model = $model;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current category model object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->_coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }

    public function getCategoryCollectionById($catId) {
        $categoryCollection = $this->model->getCollection()->addAttributeToSelect('category_header')->addFieldToFilter('category_header', array('neq' => ''))->addFieldToFilter('entity_id', $catId);
        return $categoryCollection;
    }

    public function getCategoryTitle()
    {
        $category = $this->getCurrentCategory(); //get current category
        $current_category_name = $this->catalogOutputHelper->categoryAttribute($category, $category->getName(), 'name');
        $parent_category_name = $this->catalogOutputHelper->categoryAttribute($category, $category->getParentCategory()->getName(), 'name');

        $catID_t = $category->getId();

        $category_t = $this->getCategoryCollectionById($catID_t);

        $data_t = $category_t->getData();
        $title = '';
        if ($data_t) {
            $categoryHeader_t = $data_t[0]['category_header'];
            if ($categoryHeader_t == null || $categoryHeader_t == '') {
                if ($category->getLevel() > 2) {
                    if ($parent_category_name == "Data Driven" || $parent_category_name == "Hand Drawn" || $parent_category_name == "Essentials")
                        $title =  "All $current_category_name PowerPoint Templates and Slides";
                    else if ($parent_category_name == "Themes")
                        $title =  "All $current_category_name PowerPoint $parent_category_name, Templates and Slides";
                    else if ($parent_category_name == "Medical")
                        $title =  "All $current_category_name PowerPoint Images, Illustrations and Diagram slides";
                    else if ($parent_category_name == "Technology")
                        $title =  "All $current_category_name PowerPoint $parent_category_name Templates, Presentations and Slides";
                    else if ($parent_category_name == "Flat Designs" && $current_category_name == "Bullet and Text Slides")
                        $title =  "$current_category_name PowerPoint presentation slide templates";
                    else if ($parent_category_name == "Excel Linked" && $current_category_name == "Bubble Chart Graph")
                        $title =  "All $current_category_name PowerPoint Presentation Templates and Slides";
                    else
                        $title =  "All $current_category_name PowerPoint $parent_category_name and Slides";
                }else {
                    if ($current_category_name == "Themes")
                        $title =  "All PowerPoint $current_category_name and Templates";
                    else if ($current_category_name == "Medical")
                        $title =  "All $current_category_name PowerPoint Images, Illustrations and Diagram slides";
                    else
                        $title =  "All PowerPoint $current_category_name";
                }
            }else {
                $title =  $categoryHeader_t;
            }
        }
        return $title;
    }
}
