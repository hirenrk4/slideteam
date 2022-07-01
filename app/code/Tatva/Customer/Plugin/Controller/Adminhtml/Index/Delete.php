<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Customer\Plugin\Controller\Adminhtml\Index;


class Delete 
{
   /**
     * $EmarsysHelper
     *
     * @type \Tatva\Emarsys\Helper\Data
     */
    protected $EmarsysHelper;
    /**
     * Delete customer action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function __construct
    (
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
      \Magento\Framework\Message\ManagerInterface $messageManager,
      \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
      \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper
    ) {

        $this->_scopeConfig = $scopeConfig;
        $this->_httpClientFactory   = $httpClientFactory;
        $this->_messageManager = $messageManager;
        $this->EmarsysHelper = $EmarsysHelper;
        $this->emarsysApiHelper = $emarsysApiHelper;
    }

    public function afterexecute(\Magento\Customer\Controller\Adminhtml\Index\Delete $deleteAccount)
    {     
          $customerId = $deleteAccount->getRequest()->getParam('id');
         if (!empty($customerId)) {
                 
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
             }
    }
}
