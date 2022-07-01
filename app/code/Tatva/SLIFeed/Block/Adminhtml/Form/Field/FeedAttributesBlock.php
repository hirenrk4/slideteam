<?php

namespace Tatva\SLIFeed\Block\Adminhtml\Form\Field;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class FeedAttributesBlock extends \SLI\Feed\Block\Adminhtml\Form\Field\FeedAttributesBlock
{
    protected $nonEavAttributes = [
        'related_products',
        'upsell_products',
        'crosssell_products',
        'reviews_count',
        'rating_summary',
        'c_number_of_slides',
        'availability'
    ];
}