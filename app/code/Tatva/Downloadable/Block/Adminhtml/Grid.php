<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Adminhtml reviews grid
 *
 * @method int getProductId() getProductId()
 * @method \Magento\Review\Block\Adminhtml\Grid setProductId() setProductId(int $productId)
 * @method int getCustomerId() getCustomerId()
 * @method \Magento\Review\Block\Adminhtml\Grid setCustomerId() setCustomerId(int $customerId)
 * @method \Magento\Review\Block\Adminhtml\Grid setMassactionIdFieldOnlyIndexValue() setMassactionIdFieldOnlyIndexValue(bool $onlyIndex)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Tatva\Downloadable\Block\Adminhtml;

class Grid extends \Magento\Review\Block\Adminhtml\Grid
{
    /**
     * Prepare grid columns
     *
     * @return \Magento\Backend\Block\Widget\Grid
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        
        //return parent::_prepareColumns();

        $this->addColumn(
            'review_id',
            [
                'header' => __('ID'),
                'filter_index' => 'rt.review_id',
                'index' => 'review_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created'),
                'type' => 'datetime',
                'filter_index' => 'rt.created_at',
                'index' => 'review_created_at',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );

        if (!$this->_coreRegistry->registry('usePendingFilter')) {
            $this->addColumn(
                'status',
                [
                    'header' => __('Status'),
                    'type' => 'options',
                    'options' => $this->_reviewData->getReviewStatuses(),
                    'filter_index' => 'rt.status_id',
                    'index' => 'status_id'
                ]
            );
        }

        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'filter_index' => 'rdt.title',
                'index' => 'title',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true
            ]
        );

        $this->addColumn(
            'nickname',
            [
                'header' => __('Nickname'),
                'filter_index' => 'rdt.nickname',
                'index' => 'nickname',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true,
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'customer_email',
            [
                'header' => __('Customer Email'),
                'filter_index' => 'rdt.customer_email',
                'index' => 'customer_email',
                'type' => 'text',
                'truncate' => 50,
                'escape' => true,
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'detail',
            [
                'header' => __('Review'),
                'index' => 'detail',
                'filter_index' => 'rdt.detail',
                'type' => 'text',
                'truncate' => 50,
                'nl2br' => true,
                'escape' => true
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn(
                'visible_in',
                ['header' => __('Visibility'), 'index' => 'stores', 'type' => 'store', 'store_view' => true]
            );
        }

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'type' => 'select',
                'index' => 'type',
                'filter' => \Magento\Review\Block\Adminhtml\Grid\Filter\Type::class,
                'renderer' => \Magento\Review\Block\Adminhtml\Grid\Renderer\Type::class
            ]
        );

        $this->addColumn(
            'name',
            ['header' => __('Product'), 'type' => 'text', 'index' => 'name', 'escape' => true]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'type' => 'text',
                'index' => 'sku',
                'escape' => true
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getReviewId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => 'review/product/edit',
                            'params' => [
                                'productId' => $this->getProductId(),
                                'customerId' => $this->getCustomerId(),
                                'ret' => $this->_coreRegistry->registry('usePendingFilter') ? 'pending' : null,
                            ],
                        ],
                        'field' => 'id',
                    ],
                ],
                'filter' => false,
                'sortable' => false
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        
    }

   
}
