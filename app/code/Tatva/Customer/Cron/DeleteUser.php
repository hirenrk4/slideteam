<?php
namespace Tatva\Customer\Cron;

class DeleteUser
{
    protected $_scopeConfig;
    protected $_customerCollectionFactory;
    protected $registry;

    public function __construct(       
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \Magento\Framework\Registry $registry
    ) {     
        $this->_scopeConfig  = $scopeConfig; 
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->registry = $registry;
    }

    public function execute()
    {
        $email = $this->_scopeConfig->getValue('button/user_delete/email_like',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $customerCollection = $this->_customerCollectionFactory->create();
        $customerCollection->addAttributeToFilter('email',array('like' => '%'.$email.'%'));

        $this->registry->register('isSecureArea', true);

        foreach ($customerCollection as $customer) {
            $customer->delete();
        }
    }
}