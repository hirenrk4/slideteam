<?php
namespace Tatva\PaidCustomerPopup\Block;

class PopupView extends \Magento\Framework\View\Element\Template
{
    protected $httpContext;
    protected $customerSession;
    protected $customerFactory;
    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Request\Http $request
    ){
        $this->httpContext = $httpContext;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->request = $request;
        parent::__construct($context);
    }

    public function showPopup()
    {
        if($this->getCustomerIsLoggedIn() && $this->customerSession->getIsPaidCurrentLogin()){
            $customerId = $this->customerSession->getCustomerId();
            $custom = $this->customerFactory ->create();
            $customer = $custom->load($customerId)->getDataModel();

            $secondLoginValue = $customer->getCustomAttribute('second_login')->getValue();
            if($secondLoginValue == 1){
                $this->customerSession->setIsPaidCurrentLogin(0);
                return true;
            }
        }
        return false;
    }

    public function getCustomerIsLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    public function checkPDPPage()
    {
        $pageIdentifier = $this->request->getFullActionName();
        if($pageIdentifier == 'catalog_product_view'){
            return false;
        }
        return true;
    }
}