<?php

namespace Tatva\Customer\Plugin;

class PersonalInfo
{
	public function beforeToHtml(\Tatva\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo $block)
    {
    	
        $block->setTemplate('Tatva_Customer::customer/edit/personal_info.phtml');
    }
} 