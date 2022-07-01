<?php

namespace Tatva\Sentence\Model\ResourceModel\Sentence;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

	protected $_idFieldName = 'sentence_id';

	protected function _construct()
	{
		$this->_init('Tatva\Sentence\Model\Sentence', 'Tatva\Sentence\Model\ResourceModel\Sentence');
	}

}
