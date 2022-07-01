<?php
namespace Tatva\Subscription\Controller\Adminhtml;
Class Index extends \Magento\Framework\App\Action\Action
{

	protected function _isAllowed()
	{
	 return $this->_authorization->isAllowed('Magento_Customer::manage');
	}

	public function execute()
	{
		echo "Need to work";
		//exit("<br/> in ".__FILE__." : ".__LINE__);
	}
}

?>