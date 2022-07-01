<?php
namespace Tatva\Downloadable\Block;
class Reserch extends \Magento\Framework\View\Element\Template
{


  public function __construct
  (
    \Magento\Backend\Block\Template\Context $context,
    \Magento\Framework\Session\SessionManagerInterface $coreSession,
    array $data = [])
  {
  	$this->_coreSession = $coreSession;
    parent::__construct($context, $data);
  }
  function getSessionValue(){
  			
  	return $this->_coreSession;
  }


}
