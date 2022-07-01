<?php 
namespace Tatva\Customerdashboard\Model\ResourceModel;

class Customerdashboard extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
	public function _construct(){
		$this->_init("tatva_customer_dashboard","id");
	}
}

?>