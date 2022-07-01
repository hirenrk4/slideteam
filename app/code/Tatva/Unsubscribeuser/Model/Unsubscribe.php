<?php

namespace Tatva\Unsubscribeuser\Model;

use Magento\Framework\Model\AbstractModel;

class Unsubscribe extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Tatva\Unsubscribeuser\Model\ResourceModel\Unsubscribe');
    }
}