<?php

namespace Tco\Checkout\Controller\Ins;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\RemoteServiceUnavailableException;
use Magento\Sales\Model\OrderFactory;

class Notification extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $_logger;

	/**
	 * @var \Tco\Checkout\Model\Notification
	 */
	protected $_notificationHandler;

	/**
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Tco\Checkout\Model\Notification $notificationHandler
	 * @param \Psr\Log\LoggerInterface $logger
	 */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Tco\Checkout\Model\Notification $notificationHandler,
		\Psr\Log\LoggerInterface $logger
	) {
		$this->_logger = $logger;
		$this->_notificationHandler = $notificationHandler;
		parent::__construct($context);
	}

	public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

	public function execute()
	{
		
		if (!$this->getRequest()->isPost()) {
			return;
		}

		$writer = new \Zend\Log\Writer\Stream(BP . "/var/log/payment_log/2co_ipn_".date("d-m-Y").".log");
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($this->getRequest()->getPostValue());


		try {
			$params = $this->getRequest()->getPostValue();
			
			$this->_notificationHandler->processNotification($params);
		} catch (\Exception $e) {
			$this->_logger->critical($e);
			$this->getResponse()->setHttpResponseCode(500);
		}
	}
}
