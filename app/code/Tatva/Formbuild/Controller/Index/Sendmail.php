<?php

namespace Tatva\Formbuild\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\ScopeInterface;

class Sendmail extends \Magento\Framework\App\Action\Action
{

    protected $_resultPageFactory;
    protected $_customerSession;
    protected $messageManager;
    protected $_redirect;
    protected $resultJsonFactory;
    protected $_template; 
    protected $_transportBuilder; 
    protected $_scopeConfig;
    protected $_storeManager;

    const XML_PATH_EMAIL_TEMPLATE = 'tatva/formbuild/customeremail';

    public function __construct
    (
        Context $context, 
        JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirectInterface,
        \Tatva\Formbuild\Model\PostFactory $postFactory,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\UrlInterface $url
    )
    {
        $this->_customerSession = $customerSession;
        $this->resultFactory = $resultFactory;
        $this->_transportBuilder = $transportBuilder;   
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_redirect = $redirectInterface;
        $this->messageManager = $messageManager;
        $this->_postFactory = $postFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->url = $url;

        parent::__construct($context);
    }

    public function execute()
    {
        $form_id = $this->getRequest()->getParam('form_id');
        $name = $this->getRequest()->getParam('name');
        $resultPage = $this->_resultPageFactory->create();
        $result = $this->resultJsonFactory->create();
        
        /*$cds_params = array();
        $cds_params = $this->getRequest()->getParams();
        $captcha_form = 'page_landing_form_captcha';
        $_captcha = $this->_customerSession->getData($captcha_form . '_word');

        $master_enable = $this->_scopeConfig->getValue('customer/captcha/master_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $master_captcha = $this->_scopeConfig->getValue('customer/captcha/master_captcha', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($master_captcha == $cds_params['captcha'][$captcha_form]){
            if ($master_enable) {
                $_captcha['data'] = $master_captcha;
            }
        }*/

        $post = (array) $this->getRequest()->getPost();
        /*if ($cds_params['captcha'][$captcha_form] == $_captcha['data']){*/

            if (!empty($post)) {
                
                $form_id = $post['form_id'];
                $name  = $post['name'];
                $email = $post['email'];
                $phone = $post['phone'];
                /*echo $form_id;
                echo $name;
                echo $email;
                echo $phone;*/
            }

            $collection = $this->_postFactory->create()->getCollection()->addFieldToFilter('form_id', $form_id);/*->addFieldToSelect('post_content')->addFieldToSelect('image');*/
            //$data = $collection->getData();

            foreach ($collection->getData() as $data) {
                $form_name = $data['form_name'];
                $url = $data['url_key'];
                $email_to = $data['email_to'];
                $email_cc = $data['email_cc'];
                $email_bcc = $data['email_bcc'];
                //print_r($form_name);
                //print_r($email_to);
                //print_r($email_cc);
                //print_r($email_bcc);
                //exit();
            }
            //exit();
            $mail = new \Zend_Mail();
            $message = "Please find below details of ".$form_name." submitter";
            $message .= "<br/><br/>Form Name :: ".$form_name;
            $message .= "<br/><br/>Customer Name :: ".$name;
            $message .= "<br/><br/>Customer Email :: ".$email;
            $message .= "<br/><br/>Customer PhoneNum. :: ".$phone;
            $mail->setFrom("support@slideteam.net",'SlideTeam Support');
            $mail->setSubject('Marketing campaign - '.$form_name);
            $mail->setBodyHtml($message);

            $to_email = explode(',',$email_to);
            $cc_email = explode(',',$email_cc);
            $bcc_email = explode(',',$email_bcc);   

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
                }

            /*$mail = new \Zend_Mail();
            $message = "Thank you for submitting ".$form_name.". Our team will contact you shortly.";
            $mail->setFrom("support@slideteam.net",'SlideTeam Support');
            $mail->setSubject($form_name.' submitted successfully');
            $mail->setBodyHtml($message);

            $to_email = explode(',',$email);   

            $send = 0;
                if(!empty($to_email))
                {
                    $mail->addTo($to_email);
                    $send = 1;
                }
                
                try
                {
                    if($send) :
                        $mail->send();
                    endif;
                }catch(Exception $e)
                {
                    echo $e->getMessage();
                }*/

                $store = $this->_storeManager->getStore()->getId();
                $emailTemplateVariables = array();
                $emailTemplateVariables['customerSubject'] = 'Marketing campaign - '.$form_name;
                $emailTemplateVariables['customerTitle'] = 'Thank you for submitting a '.$form_name.' to SlideTeam, Our team will contact you soon.';
                $to_email = explode(',',$email);
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                try{
                    $this->_template  = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_TEMPLATE,ScopeInterface::SCOPE_STORE,$store);
                    $this->_transportBuilder->setTemplateIdentifier($this->_template)
                    ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store))
                    ->setTemplateVars($emailTemplateVariables)
                    ->setFrom(['name' => 'SlideTeam Support', 'email' => 'support@slideteam.net'])
                    ->addTo($to_email);

                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();
                }
                catch (\Exception $e) 
                {
                    echo $e->getMessage();
                    $this->messageManager->addError("Unable to send email");
                    //$resultRedirect->setUrl($previous_full_url);
                    return $result;
                }


                $this->messageManager->addSuccessMessage('Thank You for Submitting the Form');
                //$resultRedirect->setUrl($this->getLinkUrl($url));
                //return $resultRedirect;
                return $result;
        /*}
        else
        {
            $this->messageManager->addError("Invalid Captcha");
            $this->_customerSession->setBrCaptchaError(true);
            //$resultRedirect->setUrl($this->getLinkUrl($url));
            return $result;
        }*/
    }
}
