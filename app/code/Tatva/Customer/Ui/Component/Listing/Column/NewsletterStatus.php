<?php

namespace Tatva\Customer\Ui\Component\Listing\Column;

class NewsletterStatus implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{
		return 
		[
			['value' => 1, 'label' => __('Subscribed')], 
			['value' => 2, 'label' => __('Not Activated')],
			['value' => 3, 'label' => __('Unsubscribed')],
			['value' => 4, 'label' => __('Unconfirmed')],
			['value' => NULL, 'label' => __('Unsubscribed')]
		];
	}
}