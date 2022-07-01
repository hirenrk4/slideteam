<?php

namespace Tatva\Customer\Ui\Component\Listing\Column;

class JoinForm implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return 
		[
			['value' => "Facebook", 'label' => __('Facebook')], 
			['value' => "Google", 'label' => __('Google')],
		];
	}
}