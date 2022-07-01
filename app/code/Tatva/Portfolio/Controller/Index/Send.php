<?php

namespace Tatva\Portfolio\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Send extends \Magento\Framework\App\Action\Action {

    protected $_pageFactory;
    protected $_messageManager;
    protected $_scopeConfig;
    protected $_transportBuilder;
    protected $_storeManager;
    protected $_template;
    protected $_customerSession;

    const XML_PATH_EMAIL_PORTFOLIO = 'tatva/portfolio/email';

    public function __construct(
    \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\View\Result\PageFactory $pageFactory, 
            \Magento\Framework\Message\ManagerInterface $messageManager,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
            \Tatva\Portfolio\Model\Mail\TransportBuilder $transportBuilder,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\Controller\ResultFactory $resultFactory,
            \Magento\Framework\Session\SessionManagerInterface $sessionManager,
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
            \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
            \Magento\Framework\UrlInterface $url
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_messageManager = $messageManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->resultFactory = $resultFactory;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
        $this->url = $url;
        return parent::__construct($context);
    }

    public function execute() {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $previous_full_url = $this->_redirect->getRefererUrl();
        $arr = $this->getRequest()->getParams();

        $captcha_formID = 'portfolio_captcha';
        $_captcha = $this->_customerSession->getData($captcha_formID . '_word');

        /*Master captcha*/
        $master_enable = $this->_scopeConfig->getValue('customer/captcha/master_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $master_captcha = $this->_scopeConfig->getValue('customer/captcha/master_captcha', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($arr['captcha'][$captcha_formID] != $_captcha['data'])
        {
            if($master_enable)
            {
                $_captcha['data'] = $master_captcha;
            }
        }

        if ($arr['captcha'][$captcha_formID] == $_captcha['data']) 
        {
            if ((count($arr) > 0) && !empty($arr['name']) && !empty($arr['email'])) {

                $store = $this->_storeManager->getStore()->getId();
                $emailTemplateVariables = array();
                $emailTemplateVariables = $this->getRequest()->getParams();
                $emailTemplateVariables['emailSubject'] = 'Portfolio Form of '.$emailTemplateVariables['name'];
                $emailTemplateVariables['tableTitle'] = 'Portfolio Form of '.$emailTemplateVariables['name'];

                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                
                $to_array = $this->_scopeConfig->getValue('contact/customemail/recipient_email_design_services');
                $email_to = explode(",",$to_array);
                $cc_array = $this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_design_services');
                $email_cc = explode(",",$cc_array);
                $bcc_array = $this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_design_services');
                $email_bcc = explode(",",$bcc_array);

                try {
                    $this->_template = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_PORTFOLIO, ScopeInterface::SCOPE_STORE, $store);

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
                    $emailTemplateVariables['emailSubject'] = 'Portfolio service request submitted';
                    $emailTemplateVariables['tableTitle'] = 'Thank you for submitting a request to SlideTeam, Our team will contact you soon. Below is a copy of the request you sent for reference.';
                    $this->_template  = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_PORTFOLIO, ScopeInterface::SCOPE_STORE, $store);
                    $this->_transportBuilder->setTemplateIdentifier($this->_template)
                            ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store))
                            ->setTemplateVars($emailTemplateVariables)
                            ->setFrom(['name' => 'Slideteam Design Team', 'email' => 'design@slideteam.net'])
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

                    $customRedirectionUrl = $this->url->getUrl('thankyou');
                    $resultRedirect->setUrl($customRedirectionUrl);
                    return $resultRedirect;
                    
                } catch (\Exception $ex) {
                    echo $ex->getMessage();
                    $this->_messageManager->addError("Unable to send email");
                    $this->_redirect($previous_full_url);
                }
            }
        } else {
            $this->_messageManager->addError("Invalid Captcha");
            $this->_redirect($previous_full_url);
        }
    }

}
