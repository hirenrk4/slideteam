<?php
namespace Tatva\Customer\Model\Plugin;

class EmailNotification
{
    public function aroundNewAccount(\Magento\Customer\Model\EmailNotification $subject, \Closure $proceed)
    {
        return $subject;
    }
}

?>