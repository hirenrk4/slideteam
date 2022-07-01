<?php
namespace Tatva\Paypalrec\Controller\Ipn;

class Index extends \Magento\Paypal\Controller\Ipn\Index
{

	/**
	 * @var \Magento\Paypal\Model\IpnFactory
	 */
	protected $paypalIpnFactory;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var \Magento\Framework\Http\Adapter\CurlFactory
	 */
	protected $adapterCurlFactory;
	 public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Paypal\Model\IpnFactory $ipnFactory,
		\Psr\Log\LoggerInterface $logger
	) {
		$this->_logger = $logger;
		$this->_ipnFactory = $ipnFactory;
		parent::__construct($context,$ipnFactory,$logger);
	}

	/**
	 * Instantiate IPN model and pass IPN request to it
	 */
	public function execute()
	{                        
		if (!$this->getRequest()->isPost()) {
			if (!$this->getRequest()) {
			return;
			}
		}

		try {
			$data = $this->getRequest()->getPost();
			$count_values = count($data);
			if($count_values == 0)
			{
			  $data = $this->getRequest()->getParams();
			}

			$this->_ipnFactory->create(['data' => $data])->processIpnRequest();
		} catch (RemoteServiceUnavailableException $e) {
			$this->logger->critical($e);
			$this->getResponse()->setHeader(503, '1.1', 'Service Unavailable')->sendResponse();
			exit;
		} catch (Exception $e) {
			$this->logger->critical($e);
			$this->getResponse()->setHttpResponseCode(500);
		}
	}
}
