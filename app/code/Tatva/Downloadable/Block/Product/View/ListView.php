<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Downloadable\Block\Product\View;

/**
 * Detailed Product Reviews
 *
 * @api
 * @since 100.0.2
 */
class ListView extends \Magento\Review\Block\Product\View
{
   /**
    * Unused class property
    * @var false
    */
   protected $_forceHasOptions = false;

   /**
    * Get product id
    *
    * @return int|null
    */
   public function getProductId()
   {
      $product = $this->_coreRegistry->registry('product');
      return $product ? $product->getId() : null;
   }

   /**
    * Prepare product review list toolbar
    *
    * @return $this
    */
   protected function _prepareLayout()
   {
      parent::_prepareLayout();

      $toolbar = $this->getLayout()->getBlock('product_review_list.toolbar');
      if ($toolbar) {
         $toolbar->setCollection($this->getReviewsCollection());
         $this->setChild('toolbar', $toolbar);
      }

      return $this;
   }

   /**
    * Add rate votes
    *
    * @return $this
    */
   protected function _beforeToHtml()
   {
      $this->getReviewsCollection()->load()->addRateVotes();
      return parent::_beforeToHtml();
   }

   /**
    * Return review url
    *
    * @param int $id
    * @return string
    */
   public function getReviewUrl($id)
   {
      return $this->getUrl('*/*/view', ['id' => $id]);
   }

   public function getProgessBarData()
   {
      $i =0;
      $bardata = array(20 => 0, 40 => 0, 60 => 0, 80 => 0, 100 => 0);
      $_items = $this->getReviewsCollection()->getItems();
      foreach ($_items as $_review) {
         foreach ($_review->getRatingVotes() as $_vote) {
            $bardata[$_vote->getPercent()]++;
            $i++;
         }
      }
      $ratingdata = array("progressbardata"=>$bardata,"total"=>$i);
      return $ratingdata;
   }
}
