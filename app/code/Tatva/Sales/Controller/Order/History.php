<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Sales\Controller\Order;

use Magento\Sales\Controller\OrderInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class History extends \Magento\Sales\Controller\Order\History 
{

    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $this->_forward('defaultNoRoute'); //For Stop access history url
    }
}
