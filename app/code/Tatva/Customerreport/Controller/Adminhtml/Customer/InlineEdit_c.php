<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Customerreport\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action\Context;
use Tatva\Subscription\Model\Subscription  as SubscriptionRepository;
use Magento\Framework\Controller\Result\JsonFactory;
use Tatva\Subscription\Api\Data\SubscriptionInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action
{

	protected $subscriptionRepository;
	protected $jsonFactory;

	public function __construct(
		Context $context,
		SubscriptionRepository $subscriptionRepository,
		JsonFactory $jsonFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Customer\Api\CustomerRepositoryInterface $customer
	) {
		parent::__construct($context);
		$this->subscriptionRepository = $subscriptionRepository;
		$this->jsonFactory = $jsonFactory;
		$this->_scopeConfig = $scopeConfig;
		$this->_httpClientFactory   = $httpClientFactory;
		$this->messageManager=$messageManager;
		$this->customer=$customer;

	}

	public function execute()
	{
		/** @var \Magento\Framework\Controller\Result\Json $resultJson */
		$resultJson = $this->jsonFactory->create();
		$error = false;
		$messages = [];

		if ($this->getRequest()->getParam('isAjax')) {
			$postItems = $this->getRequest()->getParam('items', []);
			
			if (!count($postItems)) {
				$messages[] = __('Please correct the data sent.');
				$error = true;
			} else {
				foreach (array_keys($postItems) as $blockId) {
					/** @var \Magento\Cms\Model\Block $block */


					try {
						$subscriptionModel=$this->subscriptionRepository;
						$subscriptionModel->load($blockId);  
						$postItems[$blockId]['renew_date'] = date("Y-m-d",strtotime($postItems[$blockId]['to_date'] . "+ 1 day"));      
						$subscriptionModel->setData(array_merge($subscriptionModel->getData(), $postItems[$blockId]));  
						$subscriptionModel->setId($blockId); 
												
						$this->subscriptionRepository->save();

						$customer_id = $postItems[$blockId]["customer_id"];
						$customer_type = 1;   
						$subscription_duration = null;
						$period_full = $postItems[$blockId]['subscription_period'];
						$customerData = $this->customer->getById($customer_id);

						if(isset($postItems[$blockId]['download_limit'])){
						 //Emarsys integration start//

							$filed1=$this->_scopeConfig->getValue('button/emarsys_config/field1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
							if($filed1)
							{
								$newsletter_subscribe = $customer->getIsSubscribed();
								$url = 'https://suite11.emarsys.net/u/register_bg.php';

								$fields = array('owner_id' => '545455284', 'key_id' => '485', 'inp_485' => $customer_id,'inp_490' => $customer_type);


								$client = $this->_httpClientFactory->create();
								$client->setUri($url);
								$client->setHeaders(
									[
										'Accept: application/json'
									]
								);
								$client->setParameterPost($fields);
								$client->setMethod(\Zend_Http_Client::POST);


								try {
									$responseBody = $client->request()->getBody();

								} catch (\Exception $e) {
									echo $e->getMessage();
								}
							//Emarsys integration end//
							}

						}
					   //Emarsys integration start//
						if($filed1)
						{

							switch($period_full)
							{
								case($period_full=="Monthly") : $subscription_duration = 1;break;
								case($period_full=="Semi-annual") : $subscription_duration = 2;break;
								case($period_full=="Annual") : $subscription_duration = 3;break;
								case($period_full=="Annual + Custom Design") : $subscription_duration = 4;break;
								case((stripos($period_full,'enterprise') !== false)) : $subscription_duration = 5;break;
								case((stripos($period_full,'Monthly +') !== false)) : $subscription_duration = 6;break;
								case((stripos($period_full,'Semi-annual +') !== false)) : $subscription_duration = 7;break;
							}

							$customerData = Mage::getModel('customer/customer')->load($customer_id);
							$url = 'https://suite11.emarsys.net/u/register_bg.php';
							$fields = array('owner_id' => '545455284', 'key_id' => '485', 'f' => '457', 'inp_1' => $customerData->getFirstname(), 'inp_2' => $customerData->getLastname(), 'inp_3' => $customerData->getEmail(),'inp_485' => $customer_id,'inp_488' => $subscription_expiry_date,'inp_489' => $subscription_duration,'inp_490' => $customer_type);


							$client = $this->_httpClientFactory->create();
							$client->setUri($url);
							$client->setHeaders(
								[
									'Accept: application/json'
								]
							);
							$client->setParameterPost($fields);
							$client->setMethod(\Zend_Http_Client::POST);


							try {
								$responseBody = $client->request()->getBody();

							} catch (\Exception $e) {
								echo $e->getMessage();
							}
						}
					}
					catch (\Magento\Framework\Exception\LocalizedException $e) {
						$this->messageManager->addError($e->getMessage());
					} catch (\Exception $e) {
						$this->messageManager->addException($e, __('We can\'t save the customer.'));
					}
				}
			}
		}

		return $resultJson->setData([
			//'messages' => $messages,
			'error' => $error
		]);
	}

	protected function getErrorWithBlockId(SubscriptionInterface $block, $errorText)
	{
		return '[Block ID: ' . $block->getId() . '] ' . $errorText;
	}
}
