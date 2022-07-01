<?php 
namespace Tatva\Customproductreview\Model\ResourceModel;

class Customproductreview extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
	public function _construct(){
		$this->_init("slideteam_product_review","review_id");
	}
}

?>