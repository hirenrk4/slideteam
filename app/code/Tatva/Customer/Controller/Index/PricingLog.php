<?php 
namespace Tatva\Customer\Controller\Index;

class PricingLog extends \Magento\Framework\App\Action\Action
{

    protected $_pageFactory;
    protected $customerSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\Action\Context $context
    )
    {
        $this->customerSession = $customerSession;
        $this->_pageFactory = $pageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        if($this->customerSession->isLoggedIn()) {
            $customerId = $this->customerSession->getId();   
            $customerName = $this->customerSession->getName();


        }else{
            $customerId = ' Guest ';   
            $customerName = 'Guest Customer';
        }

        $post = $this->getRequest()->getPostValue();
        $BrowserName = json_encode($post['BrowserName']);
        $BrowserVersion = json_encode($post['BrowserVersion']);
        $pricingMode = json_encode($post['pricingMode']);


        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pricing_ab_test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->info("\n   Customer Data >>>> ". " Customer Id : ".$customerId. "\n   Browser Info >>>>> " .' Browser Name '. $BrowserName .' ,  Browser Version '. $BrowserVersion."\n   Pricing Mode >>>>>  " . $pricingMode );

    }
}