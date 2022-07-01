<?php

namespace Vgroup65\Testimonial\Model;

class Testimonial extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface {

    const CACHE_TAG = 'vgroup_testimonial';
    protected $_cacheTag = 'vgroup_testimonial';
    protected $_eventPrefix = 'vgroup_testimonial';

    protected function _construct() {
        $this->_init('Vgroup65\Testimonial\Model\ResourceModel\Testimonial');
    }

    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues() {
        $values = [];
        return $values;
    }

}
