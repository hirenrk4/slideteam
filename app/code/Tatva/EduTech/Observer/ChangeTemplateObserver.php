<?php
/**
 * Copyright © 2017 BORN . All rights reserved.
 */
namespace Tatva\EduTech\Observer;

use \Magento\Framework\Event\ObserverInterface;

class ChangeTemplateObserver implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getBlock()->setTemplate('Tatva_EduTech::helper/gallery.phtml');
    }
}