<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Downloadable\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * Review resource model
 *
 * @api
 * @since 100.0.2
 */
class Review extends \Magento\Review\Model\ResourceModel\Review
{
   public function __construct(
      \Magento\Framework\Model\ResourceModel\Db\Context $context,
      \Magento\Framework\Stdlib\DateTime\DateTime $date,
      \Magento\Store\Model\StoreManagerInterface $storeManager,
      \Magento\Review\Model\RatingFactory $ratingFactory,
      \Magento\Review\Model\ResourceModel\Rating\Option $ratingOptions,
      $connectionName = null
   ) {
      parent::__construct($context, $date, $storeManager, $ratingFactory, $ratingOptions, $connectionName);
   }

   protected function _beforeSave(AbstractModel $object)
   {
      if (!$object->getId()) {
         if (!$object->getCreatedAt()) {
            $object->setCreatedAt($this->_date->gmtDate());
         }
      }
      if ($object->hasData('stores') && is_array($object->getStores())) {
         $stores = $object->getStores();
         $stores[] = 0;
         $object->setStores($stores);
      } elseif ($object->hasData('stores')) {
         $object->setStores([$object->getStores(), 0]);
      }
      return $this;
   }

}