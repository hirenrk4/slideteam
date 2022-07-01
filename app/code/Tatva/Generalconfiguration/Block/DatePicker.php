<?php

namespace Tatva\Generalconfiguration\Block;
 
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
 
class DatePicker extends \Magento\Config\Block\System\Config\Form\Field
{
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setDateFormat(\Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT);
        $element->setTimeFormat(null);
        return parent::render($element);
    }
}