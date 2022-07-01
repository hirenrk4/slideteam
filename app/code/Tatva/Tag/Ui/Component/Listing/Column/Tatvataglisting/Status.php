<?php

namespace Tatva\Tag\Ui\Component\Listing\Column\Tatvataglisting;

class Status implements \Magento\Framework\Option\ArrayInterface
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
         $options = array(
            -1=>'Disabled',
            0=>'Pending',
            1=>'Approved',
        );
        return $options;
    	
    }
   

}
