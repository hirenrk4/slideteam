<?php
namespace Tatva\Contacts\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;

class Send extends \Magento\Framework\App\Action\Action 
{
    protected $_customerSession;
    protected $_storeManager;
    protected $_messageManager;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_template;

    const XML_PATH_EMAIL_RECIPIENT  = 'contact/email/recipient_email';
    const XML_PATH_EMAIL_SENDER     = 'contact/email/sender_email_identity';
    const XML_PATH_EMAIL_TEMPLATE   = 'tatva/contacts/email';
    const XML_PATH_ENABLED          = 'contact/contacts/enabled';
    const XML_PATH_BCC_EMAIL        = 'contact/email/recipient_contacs_bcc';
    
    public function __construct
    (
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Tatva\Portfolio\Model\Mail\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\UrlInterface $url
    ) 
    {
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->resultFactory = $resultFactory;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
        $this->url = $url;
        parent::__construct($context);
    }
    
    public function execute() 
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $previous_full_url = $this->_redirect->getRefererUrl();

        $contact_data = array();
        $contact_data = $this->getRequest()->getParams();
        $captcha_data = $this->_customerSession->getData('contact_us_word')['data'];
        $this->_customerSession->setData('contact_data',array($contact_data));

        $master_enable = $this->_scopeConfig->getValue('customer/captcha/master_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $master_captcha = $this->_scopeConfig->getValue('customer/captcha/master_captcha', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($master_captcha == $contact_data['captcha']['contact_us']){
            if ($master_enable) {
                $captcha_data = $master_captcha;
            }
        }
        if ($captcha_data != $contact_data['captcha']['contact_us']){
            $this->messageManager->addError("Invalid Captcha");
            $this->_customerSession->setBrCaptchaError(true);
            $this->_redirect($previous_full_url);

        }
        else
        {
            if((count($contact_data) > 0) && !empty($contact_data['name']) && !empty($contact_data['email']) && !empty($contact_data['comment']))
            {
                $store = $this->_storeManager->getStore()->getId();
                $emailTemplateVariables = array();
                $emailTemplateVariables = $this->getRequest()->getParams();
                
                $emailTemplateVariables['emailSubject'] = 'Contact Form of '.$emailTemplateVariables['name'];
                $emailTemplateVariables['tableTitle'] = 'Contact Form of '.$emailTemplateVariables['name'];

                $to_array = $this->_scopeConfig->getValue('contact/customemail/recipient_contact_email_design');
                $email_to = explode(",",$to_array);
                $cc_array = $this->_scopeConfig->getValue('contact/customemail/recipient_email_design_cc1');
                $email_cc = explode(",",$cc_array);
                $bcc_array = $this->_scopeConfig->getValue('contact/customemail/recipient_email_design_bcc');
                $email_bcc = explode(",",$bcc_array);

                try
                {
                    $this->_template = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE,$store);
                    $this->_transportBuilder->setTemplateIdentifier($this->_template)
                    ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store))
                    ->setTemplateVars($emailTemplateVariables)
                    ->setFrom(['name' => 'SlideTeam Support', 'email' => 'support@slideteam.net']);

                    if(!empty($email_to))
                    {
                        foreach($email_to as $to)
                        {
                            $this->_transportBuilder->addTo($to);
                        }
                    } 
                    if(!empty($email_cc))
                    {
                        foreach($email_cc as $cc)
                        {
                            $this->_transportBuilder->addCc($cc);
                        }
                    }
                    if(!empty($email_bcc))
                    {
                        foreach($email_bcc as $bcc)
                        {
                            $this->_transportBuilder->addBcc($bcc);
                        }
                    }
                    
                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();
                    
                    /*Customer Email*/
                    $emailTemplateVariables['emailSubject'] = 'Contact Form submitted';
                    $emailTemplateVariables['tableTitle'] = 'Thank you for submitting a request to SlideTeam, Our team will contact you soon. Below is a copy of the request you sent for reference.';
                    $this->_template  = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE, ScopeInterface::SCOPE_STORE, $store);
                    $this->_transportBuilder->setTemplateIdentifier($this->_template)
                            ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store))
                            ->setTemplateVars($emailTemplateVariables)
                            ->setFrom(['name' => 'SlideTeam Support', 'email' => 'support@slideteam.net'])
                            ->addTo($emailTemplateVariables['email'], $emailTemplateVariables['name']);

                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();

                    $metadata = $this->_cookieMetadataFactory
                        ->createPublicCookieMetadata()
                        ->setDuration(60)
                        ->setPath($this->_sessionManager->getCookiePath())
                        ->setDomain($this->_sessionManager->getCookieDomain());

                    $this->_cookieManager->setPublicCookie(
                        "thankyouEmail",
                        $emailTemplateVariables['email'],
                        $metadata
                    );

                    $this->_cookieManager->setPublicCookie(
                        "previousPage",
                        $previous_full_url,
                        $metadata
                    );

                    $customRedirectionUrl = $this->url->getUrl('contacts/thankyou');
                    $resultRedirect->setUrl($customRedirectionUrl);
                    return $resultRedirect;
                    
                } 
                catch (\Exception $ex) 
                {
                    echo $ex->getMessage();
                    $this->_messageManager->addError('Unable to submit your request. Please, try again later');
                    $this->_redirect($previous_full_url);
                }   
            }
            else
            {
                $this->_messageManager->addError('Please fill up required fields');
                $this->_redirect($previous_full_url);
            }
        }
    }
}