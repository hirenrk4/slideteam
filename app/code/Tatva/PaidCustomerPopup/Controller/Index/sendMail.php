<?php
namespace Tatva\PaidCustomerPopup\Controller\Index;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\Result\PageFactory;
use Tatva\PaidCustomerPopup\Model\CustomerAdditionlDataFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\CouldNotSaveException;

class sendMail extends \Magento\Framework\App\Action\Action
{
    const XML_PATH_EMAIL_PAIDCUSTOMER = 'tatva/paidcustomer/email';
    protected $_customerSession;
    protected $_messageManager;
    protected $_url;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Tatva\PaidCustomerPopup\Model\CustomerAdditionlData
     */
    protected $paidCustomerAdditionlData;

    /**
     * @var \Tatva\PaidCustomerPopup\Model\CustomerAdditionlData
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct
    (
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $pageFactory,
        CustomerAdditionlDataFactory $paidCustomerAdditionlData,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    )
    {
        $this->pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_url = $url;
        $this->_messageManager = $messageManager;
        $this->_customerSession = $customerSession;
        $this->paidCustomerAdditionlData = $paidCustomerAdditionlData;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $resultPage = $this->pageFactory->create();

        $params = $this->getRequest()->getParams();
        $company = $params['company'];
        $designation = $params['designation'];
        $customerId = $this->_customerSession->getCustomerId();

        try {
            $model = $this->paidCustomerAdditionlData->create();
            $model->addData([
                "customer_id" => $customerId,
                "company" => $company,
                "designation" => $designation
            ]);

            $saveData = $model->save();
            if($saveData){
                $response['status'] = 'Success';
                $sendMailEnable = $this->scopeConfig->getValue('paidcustomer/email_config/enable');
                if($sendMailEnable){
                    $this->sendMail($params);
                }
            }else{
                 $response['status'] = 'Error';
            }
        } catch (\Exception $e) {
            if($e->getMessage() == 'emailError'){
                throw new MailException(__('Unable to send mail'));
            }else{
                throw new CouldNotSaveException(__('We can\'t save the data.'));
            }
        }

        $result->setData($response);
        return $result;
    }

    public function sendMail($params)
    {
        $to_array = $this->scopeConfig->getValue('paidcustomer/email_config/to_email');
        $email_to = explode(",",$to_array);
        $cc_array = $this->scopeConfig->getValue('paidcustomer/email_config/cc_email');
        $email_cc = explode(",",$cc_array);
        $bcc_array = $this->scopeConfig->getValue('paidcustomer/email_config/bcc_email');
        $email_bcc = explode(",",$bcc_array);

        $emailTemplateVariables = array();
        $emailTemplateVariables = $params;
        $emailTemplateVariables['emailSubject'] = 'Paid Customer Survey Data of ';
        $emailTemplateVariables['customerID'] = $this->_customerSession->getCustomer()->getId();
        $emailTemplateVariables['customerName'] = $this->_customerSession->getCustomer()->getName();
        $emailTemplateVariables['customerEmail'] = $this->_customerSession->getCustomer()->getEmail();
        $store = $this->storeManager->getStore()->getId();

        try{
            $this->_template  = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_PAIDCUSTOMER,ScopeInterface::SCOPE_STORE,$store);
            $this->transportBuilder->setTemplateIdentifier('tatva_paidcustomer_email')
            ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store))
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom(['name' => 'SlideTeam Support', 'email' => 'support@slideteam.net']);
            
            if(!empty($email_to)){
                foreach($email_to as $to){
                    $this->transportBuilder->addTo($to);
                }
            } 
            if(!empty($email_cc)){
                foreach($email_cc as $cc){
                    $this->transportBuilder->addCc($cc);
                }
            }
            if(!empty($email_bcc)){
                foreach($email_bcc as $bcc){
                    $this->transportBuilder->addBcc($bcc);
                }
            }

            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        }
        catch (\Exception $e){
            throw new MailException(__('emailError'));
        }
    }
}