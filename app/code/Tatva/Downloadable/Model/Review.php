<?php

namespace Tatva\Downloadable\Model;

class Review extends \Magento\Review\Model\Review
{
   
   public function __construct(
      \Magento\Framework\Model\Context $context,
      \Magento\Framework\Registry $registry,
      \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $productFactory,
      \Magento\Review\Model\ResourceModel\Review\Status\CollectionFactory $statusFactory,
      \Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory $summaryFactory,
      \Magento\Review\Model\Review\SummaryFactory $summaryModFactory,
      \Magento\Review\Model\Review\Summary $reviewSummary,
      \Magento\Store\Model\StoreManagerInterface $storeManager,
      \Magento\Framework\UrlInterface $urlModel,
      \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
      \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
      array $data = []
   ) {
      parent::__construct($context, $registry, $productFactory, $statusFactory, $summaryFactory, $summaryModFactory, $reviewSummary, $storeManager,$urlModel,
      $resource, $resourceCollection, $data);
   }

   

   /**
    * Validate review summary fields
    *
    * @return bool|string[]
    */
   public function validate()
   {
      $errors = [];

      if (!\Zend_Validate::is($this->getDetail(), 'NotEmpty')) {
         $errors[] = __('Please enter a review.');
      }

      if (empty($errors)) {
         return true;
      }
      return $errors;
   }

  
}
