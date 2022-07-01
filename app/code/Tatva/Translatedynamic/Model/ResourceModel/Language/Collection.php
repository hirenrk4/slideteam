<?php
namespace Tatva\Translatedynamic\Model\ResourceModel\Language;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init('Tatva\Translatedynamic\Model\Language','Tatva\Translatedynamic\Model\ResourceModel\Language');
    }
}