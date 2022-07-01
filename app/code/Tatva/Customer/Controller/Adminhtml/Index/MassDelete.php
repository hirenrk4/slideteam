<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Customer\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 */
class MassDelete extends \Magento\Customer\Controller\Adminhtml\Index\MassDelete
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
        \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_httpClientFactory   = $httpClientFactory;
        $this->EmarsysHelper = $EmarsysHelper;
        $this->emarsysApiHelper = $emarsysApiHelper;
        parent::__construct($context, $filter, $collectionFactory,$customerRepository);
    }

    /**
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $customersDeleted = 0;
        foreach ($collection->getAllIds() as $customerId) {
            $this->customerRepository->deleteById($customerId);
             $field4 =  $this->EmarsysHelper->isApiEnabled();
            if($field4)
            {
                
                try {
                    $apiHelper = $this->emarsysApiHelper;
                    $result = $apiHelper->send('POST', 'contact/delete', '{"keyId": "485","485":"'.$customerId.'"}');
                   
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            }
            $customersDeleted++;
        }

        if ($customersDeleted) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) were deleted.', $customersDeleted));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }
}
