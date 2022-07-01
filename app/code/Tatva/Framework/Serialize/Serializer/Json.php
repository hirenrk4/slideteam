<?php
namespace Tatva\Framework\Serialize\Serializer;



class Json extends \Magento\Framework\Serialize\Serializer\Json
{


	/**
	 * {@inheritDoc}
	 * @since 100.2.0
	 */
	public function unserialize($string)
	{
		if($this->isSerialized($string)){
			$result = unserialize($string);
			return $result;
		}
		$result = json_decode($string, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			throw new \InvalidArgumentException("Unable to unserialize value. Error: " . json_last_error_msg());

		}
		return $result;
	}

	/**
     * Check if value is a serialized string
     *
     * @param string $value
     * @return boolean
     */
    private function isSerialized($value)
    {
        return (boolean) preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }

}