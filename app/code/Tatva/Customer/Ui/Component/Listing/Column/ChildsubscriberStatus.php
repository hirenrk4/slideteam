<?php

namespace Tatva\Customer\Ui\Component\Listing\Column;

class ChildsubscriberStatus implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return 
		[
			['value' => 1, 'label' => __('Yes')], 
			['value' => NULL, 'label' => __('')]
		];
	}
}