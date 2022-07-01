<?php

namespace Vgroup65\Testimonial\Model;

class Configuration extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

    const CACHE_TAG = 'testimonial_configuration';
    protected $_cacheTag = 'testimonial_configuration';
    protected $_eventPrefix = 'testimonial_configuration';

    protected function _construct() {
        $this->_init('Vgroup65\Testimonial\Model\ResourceModel\Configuration');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues() {
        $values = [];
        return $values;
    }

}
