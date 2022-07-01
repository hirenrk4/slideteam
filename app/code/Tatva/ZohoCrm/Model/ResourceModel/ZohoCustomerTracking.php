<?php 
namespace Tatva\ZohoCrm\Model\ResourceModel;

class ZohoCustomerTracking extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
	public function _construct(){
		$this->_init("zoho_customer_tracking","id");
	}
}

?>