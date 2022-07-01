<?php
namespace Tatva\Metadescription\Model;


class Status extends \Magento\Framework\DataObject
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;
    public function __construct(
        array $data = []
    ) {
        parent::__construct(
            $data
        );
    }


    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => __('Enabled'),
            self::STATUS_DISABLED   => __('Disabled')
        );
    }
}