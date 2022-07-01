<?php 
namespace Tatva\Ebook\Model\ResourceModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Tatva\Ebook\Model\ProductDownloadEbooksHistoryLog","Tatva\Ebook\Model\ResourceModel\ProductDownloadEbooksHistoryLog");
	}
}
 ?>