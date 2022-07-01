<?php 
namespace Tatva\Popup\Model\ResourceModel;

class Popup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
	public function _construct(){
		$this->_init("tatva_popup","popup_id");
	}
}

?>