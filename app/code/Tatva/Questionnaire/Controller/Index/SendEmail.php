<?php

namespace Tatva\Questionnaire\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Controller\ResultFactory;

class SendEmail extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;
    protected $_customerSession;
    protected $_messageManager;
    protected $_filesystem;
    protected $_scopeConfig;
    protected $_inlineTranslation; 
    protected $_transportBuilder;
    protected $_questionnaireModel; 
    protected $_template; 
    protected $_storeManager;
    protected $_file;
    protected $_cookieManager;
    protected $_cookieMetadataFactory;    

    const XML_PATH_EMAIL_QUESTIONNAIRE = 'tatva/questionnaire/email';

    public function __construct
    (
        Context $context, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
        \Magento\Framework\Filesystem $filesystem, 
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Customer\Model\Session $customerSession, 
        \Magento\Framework\Message\ManagerInterface $messageManager, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Tatva\Questionnaire\Model\Questionnaire $questionnaireModel,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        File $file
    )
    {
        $this->_questionnaireModel = $questionnaireModel;
        $this->_customerSession = $customerSession;
        $this->_messageManager = $messageManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_filesystem = $filesystem;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;        
        $this->_storeManager = $storeManager;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->resultFactory = $resultFactory;
        $this->_file = $file;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        
        $arr = array();
        $arr = $this->getRequest()->getParams();
        $captcha_formID = 'questionnaire_captcha';
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

            if ((count($arr) > 0) && !empty($arr['name']) && !empty($arr['email']))
            {
                $file_dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . 'questionnaire_files/';           
                $filesToAttach = $this->getRequest()->getParam('filesToAttach');

                $result = array();

                $oneDimensionalArray = array();
                if ($filesToAttach != "")
                {
                    $_filesToAttach = explode(",", rtrim($filesToAttach, ","));			   
                    $_uniqueFilesToAttach = array_unique($_filesToAttach);

                    foreach ($_uniqueFilesToAttach as $key => $value)
                    {                	
                        if ($this->getRequest()->getParam(str_replace(".", "_", $value)))
                        {                    	
                            $temp[] = array_unique($this->getRequest()->getParam(str_replace(".", "_", $value)));						
                            foreach ($temp as $val)
                            {
                                $result[] = $val;
                            }
                            unset($temp);
                        }
                    }
                }

                $this->_questionnaireModel->setData('questionnaries_file', '');
                $max_file_size = 5242880; //5MB
                $file_size = 0;

                if (count($result) > 0)
                {
                    $oneDimensionalArray = call_user_func_array('array_merge', $result);

                    foreach ($oneDimensionalArray as $file)
                    {
                        if ($this->_file->isExists($file_dir . $file) && $file)
                        {
                            $currentFileSize = filesize($file_dir . $file);
                            $file_size = $file_size + $currentFileSize;
                        }
                    }
                    if ($file_size > $max_file_size)
                    {
                        $arr = array();
                        $arr = $this->getRequest()->getParams();
                        $this->_customerSession->setData('data', array($arr));
                        $this->_messageManager->addError('Your attachment size is greater than 5MB. Please reduce the size of the attachment(s) or use Dropbox/Google Drive or a similar tool to send us the attachment(s). OR Send the attachment directly via email to <a href="mailto:design@slideteam.net">design@slideteam.net</a>');
                        $this->_redirect('*/');
                        return;
                    }

                    $allowed_file_type = array("pdf", "ppt", "pptx", "doc", "docx", "jpg", "jpeg", "png", "gif", "xlsx", "xlsm", "xlsb", "xls", "xltx", "xltm", "xlt", "csv", "xlam", "xla", "ods", "zip", "txt");
                    foreach ($oneDimensionalArray as $file)
                    {
                        if ($this->_file->isExists($file_dir . $file) && $file)
                        {
                            $ext = pathinfo($file_dir . $file, PATHINFO_EXTENSION);
                            $ext = strtolower($ext);
                            if (!in_array($ext, $allowed_file_type))
                            {
                                $arr = array();
                                $arr = $this->getRequest()->getParams();
                                $this->_customerSession->setData('data', array($arr));
                                $this->_messageManager->addError('This extension is not allowed.');
                                $this->_redirect('*/');
                                return;
                            }
                        }
                    }
                }
                $store = $this->_storeManager->getStore()->getId();
                $emailTemplateVariables = array();
                $emailTemplateVariables = $this->getRequest()->getParams();
                $emailTemplateVariables['phone'] = !empty($arr['phone']) ? $arr['phone'] : "";
                $emailTemplateVariables['call_flag'] = !empty($arr['call_flag']) ? $arr['call_flag'] : "";
                $emailTemplateVariables['number_of_slides'] = !empty($arr['number_of_slides']) ? $arr['number_of_slides'] : "";
                $emailTemplateVariables['style_option'] = !empty($arr['style_option']) ? $arr['style_option'] : "";
                $emailTemplateVariables['template_or_diagram_details'] = !empty($arr['template_or_diagram_details']) ? $arr['template_or_diagram_details'] : "";
                $emailTemplateVariables['description'] = !empty($arr['description']) ? $arr['description'] : "";
                $emailTemplateVariables['emailSubject'] = 'Questionnaire Form of '.$emailTemplateVariables['name'];
                
                
                $to_array = $this->_scopeConfig->getValue('contact/customemail/recipient_email_design');
                $email_to = explode(",",$to_array);
                $cc_array = $this->_scopeConfig->getValue('contact/customemail/recipient_email_design_cc1');
                $email_cc = explode(",",$cc_array);
                $bcc_array = $this->_scopeConfig->getValue('contact/customemail/recipient_email_design_bcc');
                $email_bcc = explode(",",$bcc_array);


                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

                foreach ($emailTemplateVariables as $key => $val)
                {
                    $this->_questionnaireModel->setData($key, $val);
                }

                if (count($result) > 0)
                {
                    $comma_separated = implode(",", $oneDimensionalArray);
                    $this->_questionnaireModel->setData('questionnaire_file', $comma_separated);
                }
                $this->_questionnaireModel->save();		   

                try 
                {
                    $this->_template  = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_QUESTIONNAIRE,ScopeInterface::SCOPE_STORE,$store);		
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

                    foreach ($oneDimensionalArray as $file)
                    {
                        if(file_exists($file_dir.$file))
                        {
                            $this->_transportBuilder->addAttachment(file_get_contents($file_dir.$file),$file);
                        }
                    }

                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();

                    $emailTemplateVariables['emailSubject'] = 'Questionnaire Form Submitted';
                    $this->_transportBuilder->setTemplateIdentifier($this->_template)
                    ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store))
                    ->setTemplateVars($emailTemplateVariables)
                    ->setFrom(['name' => 'Slideteam Design Team', 'email' => 'design@slideteam.net'])
                    ->addTo($emailTemplateVariables['email'],$emailTemplateVariables['name']);

                    foreach ($oneDimensionalArray as $file)
                    {
                        if (file_exists($file_dir.$file))
                        {
                            $this->_transportBuilder->addAttachment(file_get_contents($file_dir.$file),$file);						
                        }
                    }

                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();			

                    $metadata = $this->_cookieMetadataFactory->createPublicCookieMetadata()->setDuration(60);
                    $this->_cookieManager->setPublicCookie('questionnaireEmail',$emailTemplateVariables['email'],$metadata);
                    $this->_redirect('*/index/thankyou');
                }
                catch (\Exception $e) 
                {
                    echo $e->getMessage();
                    $this->_messageManager->addError("Unable to send email");
                    $this->_redirect('*/');
                }           
            } 
            else
            {
                $this->_messageManager->addError('Please fill up required fields');
                $this->_redirect('*/');
            }
        } else {
            $this->_messageManager->addError("Invalid Captcha");
            $resultRedirect->setUrl('/questionnaire');
            return $resultRedirect;
        }
    }
}