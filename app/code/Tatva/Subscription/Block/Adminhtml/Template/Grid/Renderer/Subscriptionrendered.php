<?php
namespace Tatva\Subscription\Block\Adminhtml\Template\Grid\Renderer;

class Subscriptionrendered extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
     public function render(\Magento\Framework\DataObject $row)
    {
       $value = $row->getData('final_amount');
       if($value == NUll || $value == 0){
           $value = $row->getData('final_amount_paypal');
       }
       return $value;
    }
}
?>