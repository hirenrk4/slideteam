<?php

namespace Tatva\Unsubscribeuser\Model;

use Magento\Framework\Model\AbstractModel;

class Unsubscribeplan extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Tatva\Unsubscribeuser\Model\ResourceModel\Unsubscribeplan');
    }
}