<?php
/**
 * Copyright © MageKey. All rights reserved.
 */
namespace MageKey\CustomerPassword\Block\Adminhtml\Edit;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Customer\Block\Adminhtml\Edit\GenericButton;

class NewPasswordButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

     /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param AccountManagementInterface $customerAccountManagement
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context, $registry);
        $this->customerAccountManagement = $customerAccountManagement;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $canModify = $customerId && !$this->customerAccountManagement->isReadonly($this->getCustomerId());
        $data = [];
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enable = $this->scopeConfig->getValue('customer/customerpassword/allow_changepassword', $storeScope);
        if ($enable == 1){
        if ($customerId && $canModify) {
            $data = [
                'label' => __('Change Password'),
                'class' => 'change-password',
                'id' => 'magekey-customeredit-newpassword-button',
                'data_attribute' => [
                    'mage-init' => ['MageKey_CustomerPassword/js/changepassword' => [
                        'saveUrl' => $this->getSaveUrl()
                    ]]
                ],
                'on_click' => '',
                'sort_order' => 60,
            ];
        }
    }
        return $data;
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('magekey/customer/changepassword', ['customer_id' => $this->getCustomerId()]);
    }
}
