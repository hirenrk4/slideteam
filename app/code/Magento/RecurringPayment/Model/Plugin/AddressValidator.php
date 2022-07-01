<?php
namespace Magento\RecurringPayment\Model\Plugin;

class AddressValidator 
{

    /**
     * While validatign address it throws warning which converts in exceptions. As we does not need billing as well as shipping address. We can *    skip this validation for all time.
     * @param  \Magento\Sales\Model\Order\Address\Validator $subject
     * @param  array $warnings returned by \Magento\Sales\Model\Order\Address\Validator
     * @return array of warning excluded the required warning
     */
    public function afterValidate(\Magento\Sales\Model\Order\Address\Validator $subject,$warnings)
    { 
        foreach ($warnings as $key => $warning) {   
            $pos = strpos($warning, "is a required field");
            if($pos !== false){
                unset($warnings[$key]);
            }
        }
        return $warnings;
    }
}