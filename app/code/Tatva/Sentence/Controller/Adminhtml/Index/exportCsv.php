<?php
namespace Tatva\Loginpopup\Controller\Adminhtml;


class Customeradditionaldata extends \Magento\Backend\App\Action
{
 /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Model\Session $backendSession
    ) {
        $this->registry = $registry;
        $this->backendSession = $backendSession;
    }
       protected function _isAllowed()
		{
		//return Mage::getSingleton('admin/session')->isAllowed('loginpopup/customeradditionaldata');
			return true;
		}

		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("loginpopup/customeradditionaldata")->_addBreadcrumb(__("Customeradditionaldata  Manager"),__("Customeradditionaldata Manager"));
				return $this;
		}
		public function execute() 
		{
			$fileName   = 'customeradditionaldata.csv';
            $grid       = $this->getLayout()->createBlock('loginpopup/adminhtml_customeradditionaldata_grid');
            $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		}
		
	}