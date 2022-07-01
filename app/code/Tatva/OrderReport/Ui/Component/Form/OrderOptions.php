<?php

namespace Tatva\OrderReport\Ui\Component\Form;

class OrderOptions implements \Magento\Framework\Option\ArrayInterface
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
    	'new' => 'New Orders',
    	'recurring' => 'Recurring Orders',
    	];
    }

}
