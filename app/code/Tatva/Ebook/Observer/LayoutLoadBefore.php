<?php

namespace Tatva\Ebook\Observer;

class LayoutLoadBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    private $_resourceConnection;

    public function __construct(
       \Magento\Framework\Registry $registry,
       \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->_resourceConnection = $resourceConnection;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $this->registry->registry('current_product');

        if (!$product) {
          return $this;
        }
        if ($product->getIsEbook() == 1) {
           $layout = $observer->getLayout();
           $layout->getUpdate()->removeHandle('catalog_product_view');
           $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
           $layout->getUpdate()->removeHandle('catalog_product_view_type_onepage');
           $layout->getUpdate()->removeHandle('catalog_product_view_type_documentreport');
           $layout->getUpdate()->removeHandle('catalog_product_view_type_letterhead');
           $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
           $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
           $layout->getUpdate()->removeHandle('catalog_product_view_type_edutech');
           $layout->getUpdate()->removeHandle('catalog_product_view_type_ukraincrisis');
           $layout->getUpdate()->addHandle('catalog_product_view_type_ebook');
        }

        $categoryIds = $product->getCategoryIds();
        $onepageIds = $this->registry->registry("onepage_cat_ids");
        $documentreportIds = $this->registry->registry("documentreport_cat_ids");
        $letterheadIds = $this->registry->registry("letterhead_cat_ids");
        $edutechIds = $this->registry->registry("edutech_cat_ids");
        $ukraincrisisIds = $this->registry->registry("ukraincrisis_cat_ids");

        /*print_r($edutechIds);
        exit();*/
        $categoryId = $this->scopeConfig->getValue('resume/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        if(in_array($categoryId,$categoryIds))
        {
          $layout = $observer->getLayout();
          $layout->getUpdate()->removeHandle('catalog_product_view');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_onepage');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_documentreport');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_letterhead');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_edutech');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_ukraincrisis');
          $layout->getUpdate()->addHandle('catalog_product_view_type_resume');
        }  

        $categoryId = $this->scopeConfig->getValue('brochure/general/categoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if(in_array($categoryId,$categoryIds))
        {
          $layout = $observer->getLayout();
          $layout->getUpdate()->removeHandle('catalog_product_view');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_onepage');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_documentreport');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_letterhead');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_edutech');
          $layout->getUpdate()->removeHandle('catalog_product_view_type_ukraincrisis');
          $layout->getUpdate()->addHandle('catalog_product_view_type_brochure');
        }   

        $categoryId = $this->scopeConfig->getValue('resume/general/onepagecategoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);        

        if(empty($onepageIds))
        {
          $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryId;
          $connection = $this->_resourceConnection->getConnection();
          $results = $connection->fetchAll($sql);
          if(!empty($results))
          {
            $onepageIds = explode(',',$results[0]['category_list']);
            $this->registry->register("onepage_cat_ids",$onepageIds);
            if(array_intersect($categoryIds,$onepageIds))
            {
              $layout = $observer->getLayout();
              $layout->getUpdate()->removeHandle('catalog_product_view');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_documentreport');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_letterhead');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_edutech');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_ukraincrisis');
              $layout->getUpdate()->addHandle('catalog_product_view_type_onepage');
            }
          }
        }
        else
        {
          if(array_intersect($categoryIds,$onepageIds))
          {
            $layout = $observer->getLayout();
            $layout->getUpdate()->removeHandle('catalog_product_view');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_documentreport');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_letterhead');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_edutech');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_ukraincrisis');
            $layout->getUpdate()->addHandle('catalog_product_view_type_onepage');
          }  
        }

        $categoryId = $this->scopeConfig->getValue('resume/general/document_report_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);        

        if(empty($documentreportIds))
        {
          $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryId;
          $connection = $this->_resourceConnection->getConnection();
          $results = $connection->fetchAll($sql);
          if(!empty($results))
          {
            $documentreportIds = explode(',',$results[0]['category_list']);
            $this->registry->register("documentreport_cat_ids",$documentreportIds);
            if(array_intersect($categoryIds,$documentreportIds))
            {
              $layout = $observer->getLayout();
              $layout->getUpdate()->removeHandle('catalog_product_view');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_onepage');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_letterhead');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_edutech');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_ukraincrisis');
              $layout->getUpdate()->addHandle('catalog_product_view_type_documentreport');
            }
          }
        }
        else
        {
          if(array_intersect($categoryIds,$documentreportIds))
          {
            $layout = $observer->getLayout();
            $layout->getUpdate()->removeHandle('catalog_product_view');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_onepage');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_letterhead');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_edutech');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_ukraincrisis');
            $layout->getUpdate()->addHandle('catalog_product_view_type_documentreport');

          }  
        }

        $categoryId = $this->scopeConfig->getValue('resume/general/ukrain_crisis_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);        

        if(empty($ukraincrisisIds))
        {
          $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryId;
          $connection = $this->_resourceConnection->getConnection();
          $results = $connection->fetchAll($sql);
          if(!empty($results))
          {
            $ukraincrisisIds = explode(',',$results[0]['category_list']);
            $this->registry->register("ukraincrisis_cat_ids",$ukraincrisisIds);
            if(array_intersect($categoryIds,$ukraincrisisIds))
            {
              $layout = $observer->getLayout();
              $layout->getUpdate()->removeHandle('catalog_product_view');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_onepage');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_letterhead');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_edutech');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_documentreport');
              $layout->getUpdate()->addHandle('catalog_product_view_type_ukraincrisis');              
            }
          }
        }
        else
        {
          if(array_intersect($categoryIds,$ukraincrisisIds))
          {
            $layout = $observer->getLayout();
            $layout->getUpdate()->removeHandle('catalog_product_view');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_onepage');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_letterhead');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_edutech');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_documentreport');
            $layout->getUpdate()->addHandle('catalog_product_view_type_ukraincrisis');
          }  
        }

        $categoryId = $this->scopeConfig->getValue('resume/general/letterhead_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);        

        if(empty($letterheadIds))
        {
          $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryId;
          $connection = $this->_resourceConnection->getConnection();
          $results = $connection->fetchAll($sql);
          if(!empty($results))
          {
            $letterheadIds = explode(',',$results[0]['category_list']);
            $this->registry->register("letterhead_cat_ids",$letterheadIds);
            if(array_intersect($categoryIds,$letterheadIds))
            {
              $layout = $observer->getLayout();
              $layout->getUpdate()->removeHandle('catalog_product_view');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_onepage');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_documentreport');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_edutech');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_ukraincrisis');
              $layout->getUpdate()->addHandle('catalog_product_view_type_letterhead');
            }
          }
        }
        else
        {
          if(array_intersect($categoryIds,$letterheadIds))
          {
            $layout = $observer->getLayout();
            $layout->getUpdate()->removeHandle('catalog_product_view');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_onepage');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_documentreport');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_edutech');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_ukraincrisis');
            $layout->getUpdate()->addHandle('catalog_product_view_type_letterhead');
          }  
        }

        $categoryId = $this->scopeConfig->getValue('resume/general/edutech_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);        

        if(empty($edutechIds))
        {
          $sql = "SELECT category_list FROM tatva_custom_category_manage where type=".$categoryId;
          $connection = $this->_resourceConnection->getConnection();
          $results = $connection->fetchAll($sql);
          if(!empty($results))
          {
            $edutechIds = explode(',',$results[0]['category_list']);
            $this->registry->register("edutech_cat_ids",$edutechIds);
            if(array_intersect($categoryIds,$edutechIds))
            {
              $layout = $observer->getLayout();
              $layout->getUpdate()->removeHandle('catalog_product_view');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_onepage');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_documentreport');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_letterhead');
              $layout->getUpdate()->removeHandle('catalog_product_view_type_ukraincrisis');
              $layout->getUpdate()->addHandle('catalog_product_view_type_edutech');
            }
          }
        }
        else
        {
          if(array_intersect($categoryIds,$edutechIds))
          {
            $layout = $observer->getLayout();
            $layout->getUpdate()->removeHandle('catalog_product_view');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_downloadable');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_ebook');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_brochure');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_resume');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_onepage');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_documentreport');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_letterhead');
            $layout->getUpdate()->removeHandle('catalog_product_view_type_ukraincrisis');
            $layout->getUpdate()->addHandle('catalog_product_view_type_edutech');
          }  
        }

        return $this;
    }
}