<?php

namespace Tatva\ProductImport\Model\ResourceModel;

class ProductImport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected $_idFieldName = 'profiler_id';
    protected $_date;

    public function __construct(\Magento\Framework\Model\ResourceModel\Db\Context $context, \Magento\Framework\Stdlib\DateTime\DateTime $date, $resourcePrefix = null)
    {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    protected function _construct()
    {
        $this->_init('product_import_details', 'profiler_id');
                
    }

}
