<?php
namespace Tatva\ProductImport\Ui\Component\Listing\Column;

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
    	'1' => 'Success',
    	'0' => 'Failure'
    	];
    }

}
?>