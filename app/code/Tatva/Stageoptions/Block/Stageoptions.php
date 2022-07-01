<?php

namespace Tatva\Stageoptions\Block;

class Stageoptions extends \Magento\Framework\View\Element\Template
{
    protected $_registry;
    protected $_storeManager;
    protected $_scopeConfig;
    protected $_categoryFactory;

    public function __construct
    (
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = [])
    {
        $this->_registry = $registry;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;

        parent::__construct($context, $data);
    }

    public function productHasStageOptions()
    {
        $flag = false;
        $current_category = $this->getCurrentCategory_t();
        $current_cat_id = 0;
        $current_category_level = 0;
        
        if($current_category)
        {
            $current_cat_id = $current_category->getId();
            $current_category_level = $current_category->getLevel();   
        }   

        if($current_category_level > '2')
        {
            $node_cat_id = $this->getNodeCategory() != null ? $this->getNodeCategory()->getId() : null;
            $parent_cat_id = $current_category->getParentCategory()->getId();
            $exception_categories = $this->getExceptionalCategories_t();

            if($exception_categories != null && $exception_categories[0] != '')
            {
                $is_exception_parent = in_array($parent_cat_id,$exception_categories);
                $is_exception_own = in_array($current_cat_id,$exception_categories);
                $is_exception_node = in_array($node_cat_id,$exception_categories);
                $has_not_child_categories = count($this->getChildCategories_t()) == 0 ? 1 : 0;
                $is_exception = $is_exception_own || $is_exception_parent || $has_not_child_categories || $is_exception_node;
                $flag = $is_exception ?  false : true;
            }
            else
            {
                $has_not_child_categories = count($this->getChildCategories_t()) == 0 ? 1 : 0;
                $flag = $has_not_child_categories ?  false : true;
            }
        }
        else if($current_category_level == '0')
        {
            $has_not_child_categories = count($this->getChildCategories_t()) == 0 ? 1 : 0;
            $flag = $has_not_child_categories ?  false : true;
        }

        return $flag;
    }

    public function getCurrentCategory_t()
    {
        $_category = null;
        $current_product = $this->_registry->registry('current_product');
        $category_id = $current_product->getCategoryId();

        if(empty($category_id))
        {
            $category_id = 2;
        }
        
        if($category_id != false)
        {
            $_category = $this->_categoryFactory->create()->load($category_id); //$this->_registry->registry('current_category');
            $cat_level = $_category->getLevel();
            /*if level == 4 then there is nodes then product for that we need to find main_cat  egory*/
            if($cat_level == '4')
            {
                $node_category = $_category;
                $_category = $_category->getParentCategory();
            }
        }  
        return $_category;                       
    }

    public function getNodeCategory()
    {
        $node_category = null;
        $current_product = $this->_registry->registry('current_product');
        $category_id = $current_product->getCategoryId() ;
        $_category = $this->_categoryFactory->create()->load($category_id); //$this->_registry->registry('current_category');
        $cat_level = $_category->getLevel();
        /*if level == 4 then there is nodes only*/
        if($cat_level == '4')
        {
            $node_category = $_category;
        }
        return $node_category;    
    }

    public function getExceptionalCategories_t()
    {
        $seprator = ',';
        $ex_categories_string = $this->_scopeConfig->getValue('button/stageoptions_config/stageoptions_config_field1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $ex_categories_array = explode($seprator,$ex_categories_string);

        return $ex_categories_array;
    }

    public function getChildCategories_t()
    {
        $child_categories = null;
        $_category = $this->getCategory_for_finding_children();
        if($_category != null)
        {
            $child_categories = $_category->getChildrenCategories();    
        }       
        return $child_categories;
    }

    public function getCategory_for_finding_children()
    {
        $category_t = $this->getCurrentCategory_t();
        $store_id = $this->_storeManager->getStore()->getId();
        $category_id = null;

        if($store_id != '2')
        {
            $current_product = $this->_registry->registry('current_product');
            $product_data = $current_product->getCategoryCollection()->addFieldToFilter("level",4);
            $categoryTreePath = '';

            foreach($product_data as $data)
            {
                $categoryTreePath = $data->getPath();
                $categoryIds = explode('/', $categoryTreePath);
                $category_id = isset($categoryIds[3]) == true ? $categoryIds[3] : null;
                $exception_categories = $this->getExceptionalCategories_t();

                if($exception_categories != null && $exception_categories[0] != '')
                {
                    $is_exception = in_array($category_id,$exception_categories);
                    if($is_exception)
                    {
                        continue;
                    }
                    else
                    {
                        break;
                    }
                }
                else
                {
                    break;
                }
            }
            if($category_id != null)
            {
                $category_t = $this->_categoryFactory->create()->load($category_id)->setStoreId($store_id); //$this->_registry->registry('current_category')->setStoreId($store_id);
            }
        }
        return $category_t;
    }

    public function getChildCategoriesSorted_t()
    {
        $child_categories = $this->getChildCategories_t();
        $child_categories_sorted = array();
        $store_id = $this->_storeManager->getStore()->getId();
        if($store_id == '2')
        {
            foreach ($child_categories as $key => $child_c)
            {
                $child_categories_sorted[$child_c->getName()] = $child_c->getUrl();
            }    
        }
        else if($store_id == '0' || $store_id == '1')
        {
            foreach ($child_categories as $key => $child_c)
            {
                $url = $child_c->getUrl();
                $temp_url = explode($this->_storeManager->getStore()->getBaseUrl(),$url);
                $temp_url[0] = $this->_storeManager->getStore(2)->getBaseUrl();
                $url = implode($temp_url); 
                $child_categories_sorted[$child_c->getName()] = $url;    
            }
        }

        ksort($child_categories_sorted);
        return $child_categories_sorted;
    }
}
