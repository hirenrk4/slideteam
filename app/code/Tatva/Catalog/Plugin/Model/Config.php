<?php

namespace Tatva\Catalog\Plugin\Model;

class Config
{
    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        unset($options['name']);
        unset($options['price']);
        unset($options['free']);
        unset($options['number_of_downloads']);

        $options['position'] = __('Default');
        $newOption['newest'] = __('Newest');

        $options = array_merge($options,$newOption);

        return $options;
    }
}