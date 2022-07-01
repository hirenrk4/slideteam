<?php
namespace Tatva\Customerdashboard\Model;

class Customerdashboard extends \Magento\Framework\Model\AbstractModel{

    public function _construct(){
        $this->_init("Tatva\Customerdashboard\Model\ResourceModel\Customerdashboard");
    }

}
?>