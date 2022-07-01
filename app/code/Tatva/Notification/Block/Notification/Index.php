<?php
namespace Tatva\Notification\Block\Notification;
use Tatva\Notification\Helper\Data;
use Tatva\Generalconfiguration\Helper\Data as GeneralHelper;

class Index extends \Magento\Framework\View\Element\Template
{
    /**     
     * @var \Tatva\Notification\Model\NotificationFactory
     */
    protected $helper;
    public $generalHelper;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		Data $helper,
		GeneralHelper $generalHelper,
		array $data = []
	){
		$this->helper = $helper;
		$this->generalHelper = $generalHelper;
		parent::__construct($context, $data);
	}
	public function getPopupNotifications()
	{
		$notifications = $this->helper->getAllCollection();
		$notifications->getSelect()->limit($this->helper->getPopLimit());
		return $notifications;
	}		
}