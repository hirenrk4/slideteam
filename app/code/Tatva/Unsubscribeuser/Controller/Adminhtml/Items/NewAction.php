<?php


namespace Tatva\Unsubscribeuser\Controller\Adminhtml\Items;

class NewAction extends \Tatva\Unsubscribeuser\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
