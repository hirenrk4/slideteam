<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Downloadable\Model\ResourceModel\Review;

use Magento\Framework\Model\AbstractModel;

/**
 * Review summary resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Summary extends \Magento\Review\Model\ResourceModel\Review\Summary
{
    public function appendSummaryFieldsToCollection(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        int $storeId,
        string $entityCode
    ) {
        if (!$productCollection->isLoaded()) {
            $summaryEntitySubSelect = $this->getConnection()->select();
            $summaryEntitySubSelect
                ->from(
                    ['review_entity' => $this->getTable('review_entity')],
                    ['entity_id']
                )->where(
                    'entity_code = ?',
                    $entityCode
                );
            /*$joinCond = new \Zend_Db_Expr(
                "e.entity_id = review_summary.entity_pk_value AND review_summary.store_id = {$storeId}"
                . " AND review_summary.entity_type = ({$summaryEntitySubSelect})"
            );
            $productCollection->getSelect()
                ->joinLeft(
                    ['review_summary' => $this->getMainTable()],
                    $joinCond,
                    [
                        'reviews_count' => new \Zend_Db_Expr("IFNULL(review_summary.reviews_count, 0)"),
                        'rating_summary' => new \Zend_Db_Expr("IFNULL(review_summary.rating_summary, 0)")
                    ]
                );*/
        }

        return $this;
    }
}
