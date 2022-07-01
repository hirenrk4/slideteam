<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Customer\Model;

class Customer extends \Magento\Framework\Model\AbstractModel
{
    const CACHE_TAG = 'customer_entity';

    protected $_cacheTag = 'customer_entity';
    protected $_eventPrefix = 'customer_entity';

    protected function _construct()
    {
      $this->_init('Tatva\Customer\Model\ResourceModel\Customer');
    }


   }
