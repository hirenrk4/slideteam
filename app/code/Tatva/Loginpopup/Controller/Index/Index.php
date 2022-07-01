<?php
namespace Tatva\Loginpopup\Controller\Index;

use Magento\Framework\Controller\Result\JsonFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    protected $_customerSession;

    public function __construct(
        \Magento\Backend\App\Action\Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        JsonFactory $resultJsonFactory
    )
    {
        
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }
    public function execute()
    {
        $response = array();
        $resultPage = $this->resultPageFactory->create();
        $resultJson = $this->resultJsonFactory->create();
        
        try {

            $customerData = $this->_customerSession;  
            $getSession = $customerData->getRegister();
            $getcurrentLogin = $customerData->getCurrentLogin(); 
            if($customerData->isLoggedIn())
            {
                if($getcurrentLogin == "Currently Loggedin")
                {
                    if(!$getSession == "Register")
                    {
                        $customerId = $customerData->getCustomerId();

                        $blockInstance = $resultPage->getLayout()->createBlock('Tatva\Loginpopup\Block\Loginpopup');    
                        $check = $blockInstance->check_user($customerId);

                        foreach ($check->getData() as $customerValue) {
                            $industry = $customerValue['industry'];
                            $job_profile = $customerValue['job_profile'];
                        }

                        $customer_type_flag = $blockInstance->check_user_type($customerId);

                        if((!$check->count() || $industry == null || $job_profile == null) && $customer_type_flag)
                        {
                            $block = $this->resultPageFactory->create()->getLayout()
                                    ->createBlock("Tatva\Loginpopup\Block\Loginpopup")
                                     ->setTemplate("Tatva_Loginpopup::loginpopup.phtml")
                                    ->toHtml();
                            $response = [
                                    'content' => $block,
                            ]; 
                            
                        }
                        else
                        {
                            $response['status'] = "Error";
                        }
                    }
                    $getcurrentLogin = $customerData->unsCurrentLogin();
                }
            }
            else
            {
                $response['status'] = "Error";
            }
        }   catch (\Exception $exception) {

            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
            /** @var array $response */
            $response = [
                'message' => __('An error occurred')
            ];
            $this->_logger->critical($exception);
        }
        return $resultJson->setData($response);    
    }

}