<?php
namespace Tatva\Customerreport\Controller\Adminhtml\Customer;


class Reportsfree extends \Magento\Framework\App\Action\Action {

    /**
     * @var \Tatva\Subscription\Model\SubscriptionhistoryFactory
     */
    protected $subscriptionSubscriptionhistoryFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerCustomerFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;


    public function __construct(
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\View\Result\PageFactory $resultPageFactory
      ) {
     parent::__construct($context);
     $this->resultPageFactory = $resultPageFactory;
   }
   public function execute()
   {
    $resultPage = $this->resultPageFactory->create();
    $resultPage->getConfig()->getTitle()->prepend((__('Free Customer Report(Customers who have never ever subscribed)')));
    return $resultPage;
  }

}