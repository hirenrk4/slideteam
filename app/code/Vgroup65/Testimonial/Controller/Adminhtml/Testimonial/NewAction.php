<?php
namespace Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;

use Vgroup65\Testimonial\Controller\Adminhtml\Testimonial;
class NewAction extends Testimonial {

    public function execute(){
        $this->_forward('edit');
    }
    
}
