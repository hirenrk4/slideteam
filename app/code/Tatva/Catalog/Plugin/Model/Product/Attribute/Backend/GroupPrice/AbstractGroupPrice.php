<?php

namespace Tatva\Catalog\Plugin\Model\Product\Attribute\Backend\GroupPrice;

class AbstractGroupPrice
{
    public function aroundAfterLoad(\Magento\Catalog\Model\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice $subject, callable $proceed, $object)
    {
        $result = $object;
        return $result;
    }
}