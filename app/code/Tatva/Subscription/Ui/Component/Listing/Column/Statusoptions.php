<?php

namespace Tatva\Subscription\Ui\Component\Listing\Column;

class Statusoptions implements \Magento\Framework\Option\ArrayInterface
{
    //Here you can __construct Model

	public function toOptionArray()
	{
		$result = [];

		foreach (self::getOptionArray() as $index => $value) {
			$result[] = ['value' => $index, 'label' => $value];
		}

		return $result;
	}

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray()
    {
    	return [
    	'Requested Unsubscription' => 'Requested Unsubscription',
    	'Paid' => 'Paid',
    	'Unsubscribed' => 'Unsubscribed',
        'Cancelled' => 'Cancelled',
        'Refunded' => 'Refunded',
    	];
    }

}
