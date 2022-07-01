<?php

/**
 * Grid Grid Model.
 * @category  Webkul
 * @package   Webkul_Grid
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Tatva\OrderReport\Model;


class OrderReport extends \Magento\Framework\Model\AbstractModel
{
    
    protected function _construct()
    {
        $this->_init('Tatva\OrderReport\Model\ResourceModel\OrderReport');
    }
    
}