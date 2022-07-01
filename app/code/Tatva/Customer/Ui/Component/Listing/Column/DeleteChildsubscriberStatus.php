<?php

namespace Tatva\Customer\Ui\Component\Listing\Column;

class DeleteChildsubscriberStatus implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return 
		[
			['value' => 0, 'label' => __('Deleted')], 
			['value' => -1, 'label' => __('Subscription')]
		];
	}
}