<?php 
namespace Tatva\Ebook\Model\ResourceModel;

class ProductDownloadEbooksHistoryLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
	public function _construct(){
		$this->_init("productdownload_ebooks_history_log","log_id ");
	}
}

?>