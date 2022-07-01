<?php

namespace Tatva\Schedualdemo\Controller\Index;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\ScopeInterface;

class Sendmail extends \Magento\Framework\App\Action\Action
{

    protected $_resultPageFactory;
    protected $_redirect;
    protected $resultJsonFactory; 
    protected $_transportBuilder;
    protected $_storeManager;
    protected $_template;

    //const XML_PATH_EMAIL_TEMPLATE = 'tatva/schedualdemo/pricingemail';

    public function __construct
    (
        Context $context, 
        JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirectInterface,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\UrlInterface $url
    )
    {
        $this->customerSession = $customerSession;
        $this->resultFactory = $resultFactory; 
        $this->_transportBuilder = $transportBuilder; 
        $this->_storeManager = $storeManager; 
        $this->_redirect = $redirectInterface;
        $this->messageManager = $messageManager;
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->url = $url;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        /*echo "string";
        exit();*/
        $result = $this->resultJsonFactory->create();

        $post = (array) $this->getRequest()->getPost();

        if (!empty($post)) {
                
                $isd = $post['isd-code'];
                $contact_number = $post['contact_number'];
                $email_id = $post['email_id'];
                echo $isd;
                echo $contact_number;
                echo $email_id;
                //exit();
            }

        $mail = new \Zend_Mail();
            $message = "Please find below details of submit for schedule a demo";
            //$message .= "<br/><br/>Customer Email :: ".$email;
            $message .= "<br/><br/>Customer PhoneNum. :: +".$isd." ".$contact_number;
            $message .= "<br/><br/>Customer Email_id. :: ".$email_id;
            $mail->setFrom("support@slideteam.net",'SlideTeam Support');
            $mail->setSubject('Schedule a demo Slideteam');
            $mail->setBodyHtml($message);

            //$to_email = explode(',',$email_to);
            //$to_email = "ruchitanathani998@gmail.com";
            $to_email = explode(',',$this->_scopeConfig->getValue('button/schedule/demomail_to',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $cc_email = explode(',',$this->_scopeConfig->getValue('button/schedule/demomail_cc',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            $bcc_email = explode(',',$this->_scopeConfig->getValue('button/schedule/demomail_bcc',\Magento\Store\Model\ScopeInterface::SCOPE_STORE));
            
            $send = 0;
                if(!empty($to_email))
                {
                    $mail->addTo($to_email);
                    $send = 1;
                }
                if(!empty($cc_email))
                {
                    $mail->addCc($cc_email);
                }
                if(!empty($bcc_email))
                {
                    $mail->addBcc($bcc_email);
                }
                
                try
                {
                    if($send) :
                        $mail->send();
                    endif;
                }catch(Exception $e)
                {
                    echo $e->getMessage();
                    $this->messageManager->addError("Unable to send email");
                    return $result;
                }

                $this->messageManager->addSuccessMessage('Thank you for submitting the demo request form. Our team will contact you shortly.');
                return $result;
    }
}
