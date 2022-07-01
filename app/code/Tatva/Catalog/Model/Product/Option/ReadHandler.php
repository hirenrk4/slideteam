<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Catalog\Model\Product\Option;

use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var ProductCustomOptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var Request
     */
    protected $request;
    /**
     * @param ProductCustomOptionRepositoryInterface $optionRepository
     */
    public function __construct(
        ProductCustomOptionRepositoryInterface $optionRepository,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->optionRepository = $optionRepository;
        $this->_request = $request;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return \Magento\Catalog\Api\Data\ProductInterface|object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($this->_request->getFullActionName() == 'catalog_product_view') {
            return $entity;
        }    
        $options = [];
        /** @var $entity \Magento\Catalog\Api\Data\ProductInterface */
        foreach ($this->optionRepository->getProductOptions($entity) as $option) {
            $option->setProduct($entity);
            $options[] = $option;
        }
        $entity->setOptions($options);
        return $entity;
    }
}
