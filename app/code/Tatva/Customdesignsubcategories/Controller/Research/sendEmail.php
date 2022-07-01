<?php

namespace Tatva\Customdesignsubcategories\Controller\Research;

use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\Result\JsonFactory;

class sendEmail extends \Magento\Framework\App\Action\Action {

    protected $_pageFactory;
    protected $_messageManager;
    protected $_scopeConfig;
    protected $_filesystem;
    protected $_transportBuilder;
    protected $_storeManager;
    protected $_template;
    protected $_customerSession;
    
    const XML_PATH_EMAIL_CUSTMDESIGNSUBCATEGORIES = 'tatva/customdesignsubcategories/email';
	const XML_PATH_EMAIL_CUSTMDESIGNSUBCATEGORIES_CUSTOMER = 'tatva/customdesignsubcategories/customeremail';

    public function __construct(
    \Magento\Framework\App\Action\Context $context, 
            \Magento\Framework\View\Result\PageFactory $pageFactory, 
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\Filesystem $filesystem, 
            \Magento\Framework\Message\ManagerInterface $messageManager,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
            \Tatva\Portfolio\Model\Mail\TransportBuilder $transportBuilder,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Controller\ResultFactory $resultFactory,
            \Magento\Framework\Session\SessionManagerInterface $sessionManager,
            \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
            \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
            \Magento\Framework\UrlInterface $url,
            JsonFactory $resultJsonFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_customerSession = $customerSession;
        $this->_filesystem = $filesystem;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->resultFactory = $resultFactory;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
        $this->url = $url;
        $this->resultJsonFactory = $resultJsonFactory;

        return parent::__construct($context);
    }

    public function execute() {

        $response = array();
        $resultJson = $this->resultJsonFactory->create();

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $previous_full_url = $this->_redirect->getRefererUrl();

        $hd_params = array();
        $hd_params = $this->getRequest()->getParams();
        $page_type = (string)$this->pageType();

        $this->_customerSession->setData('hd_params', array($hd_params));

        $captcha_formID = 'business_research_services_captcha';
        $_captcha = $this->_customerSession->getData($captcha_formID . '_word');

        $master_enable = $this->_scopeConfig->getValue('customer/captcha/master_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $master_captcha = $this->_scopeConfig->getValue('customer/captcha/master_captcha', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($master_captcha == $hd_params['captcha'][$captcha_formID]){
            if ($master_enable) {
                $_captcha['data'] = $master_captcha;
            }
        }

        if ($hd_params['captcha'][$captcha_formID] == $_captcha['data'])
        {    

            if ((count($hd_params) > 0) && !empty($hd_params['name']) && !empty($hd_params['form_email']) && !empty($hd_params['comment'])) {

                $attachment_dir = 'businessresearch_files/';
                $file_dir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath() . $attachment_dir;     
                $uploadedFiles = $this->getRequest()->getParam('uploadedfiles');
                $_uploadedFiles = explode(",",str_replace('"','',ltrim($uploadedFiles,",")));
                $filesToAttach = $this->getRequest()->getParam('filesToAttach');
                $result = array();
                $oneDimensionalArray = array();
                if($filesToAttach !="")
                {
                    $_filesToAttach = explode(",",rtrim($filesToAttach,","));
                    $_uniqueFilesToAttach = array_unique($_filesToAttach);
                    foreach($_uniqueFilesToAttach as $key=>$value)
                    {
                        if($this->getRequest()->getParam(str_replace(".","_",$value)))
                        {
                            $temp[] = $this->getRequest()->getParam(str_replace(".","_",$value));
                            foreach($temp as $val)
                            {
                                $result[] = $val;
                            }
                            unset($temp);
                        }
                    }
                }

                if(sizeof($result) > 0)
                {
                    $oneDimensionalArray = call_user_func_array('array_merge', $result);
                    $max_file_size = 5242880; //5MB
                    $file_size = 0;

                    foreach($oneDimensionalArray as $file) 
                    {
                        if(file_exists($file_dir.$file) && $file) 
                        {
                            $currentFileSize = filesize($file_dir.$file);
                            $file_size = $file_size + $currentFileSize;
                        }
                    }

                    if($file_size > $max_file_size) 
                    {
                        $arr = array();
                        $arr = $this->getRequest()->getParams();
                        $this->_customerSession->setData('data', array($arr));
                        //$this->messageManager->addError('Your attachment size is greater than 5MB. Please reduce the size of the attachment(s) or use Dropbox/Google Drive or a similar tool to send us the attachment(s). OR Send the attachment directly via email to <a href="mailto:design@slideteam.net">design@slideteam.net</a>');
                        $response['status'] = "totalfileziseerror";
                        return $resultJson->setData($response);
                    }

                    $allowed_file_type = array("pdf", "ppt", "pptx", "doc", "docx", "jpg", "jpeg", "png", "gif", "xlsx", "xlsm", "xlsb", "xls", "xltx", "xlt", "xlt", "csv", "xlam", "xla", "ods", "zip", "txt");

                    foreach($oneDimensionalArray as $file)
                    {
                        if(file_exists($file_dir.$file) && $file) 
                        {
                            $ext = pathinfo($file_dir.$file, PATHINFO_EXTENSION);
                            $ext = strtolower($ext);
                            if(!in_array($ext, $allowed_file_type)) 
                            {
                                $arr = array();
                                $arr = $this->getRequest()->getParams();
                                $this->_customerSession->setData('data', array($arr));
                                //$this->messageManager->addError('This extension is not allowed.');
                                $resultRedirect->setUrl($previous_full_url);
                                //return $resultRedirect;
                                $response['status'] = "extentionerror";
                                return $resultJson->setData($response);
                            }
                        }
                    }
                }

                $store = $this->_storeManager->getStore()->getId();
                $emailTemplateVariables = array();
                $emailTemplateVariables = $this->getRequest()->getParams();
                $emailTemplateVariables['emailSubject'] = 'Business Research Requirement Form of ';
                $emailTemplateVariables['tableTitle'] = 'Business Research Requirement Form of ';
                    
                $headers = 'MIME-Version: 1.0' . "\r\n";
                $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                
                $to_array = $this->_scopeConfig->getValue('contact/customemail/recipient_email_businessresearch', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $email_to = explode(",",$to_array);
                $cc_array = $this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_businessresearch', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $email_cc = explode(",",$cc_array);
                $bcc_array = $this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_businessresearch', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $email_bcc = explode(",",$bcc_array);

                try{
                    $this->_template  = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_CUSTMDESIGNSUBCATEGORIES, ScopeInterface::SCOPE_STORE, $store);
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
                        if (file_exists($file_dir.$file))
                        {
                            $this->_transportBuilder->addAttachment(file_get_contents($file_dir.$file),$file);                      
                        }
                    }

                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();

                    /*Customer Email*/
                    $emailTemplateVariables['customerSubject'] = 'Business Research Requirement Services Request submitted';
                    $emailTemplateVariables['customerTitle'] = 'Thank you for submitting a request to SlideTeam, Our team will contact you soon. Below is a copy of the request you sent for reference.';
                    $this->_template  = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_CUSTMDESIGNSUBCATEGORIES_CUSTOMER, ScopeInterface::SCOPE_STORE, $store);
                    $this->_transportBuilder->setTemplateIdentifier($this->_template)
                            ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store))
                            ->setTemplateVars($emailTemplateVariables)
                            ->setFrom(['name' => 'Slideteam Research Team', 'email' => 'research@slideteam.net'])
                            ->addTo($emailTemplateVariables['form_email'], $emailTemplateVariables['name']);

                    /*foreach ($oneDimensionalArray as $file)
                    {
                        if (file_exists($file_dir.$file))
                        {
                            $this->_transportBuilder->addAttachment(file_get_contents($file_dir.$file),$file);                      
                        }
                    }*/

                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();

                    $metadata = $this->_cookieMetadataFactory
                        ->createPublicCookieMetadata()
                        ->setDuration(60)
                        ->setPath($this->_sessionManager->getCookiePath())
                        ->setDomain($this->_sessionManager->getCookieDomain());

                    $this->_cookieManager->setPublicCookie(
                        "thankyouEmail",
                        $emailTemplateVariables['form_email'],
                        $metadata
                    );

                    $this->_cookieManager->setPublicCookie(
                        "previousPage",
                        $previous_full_url,
                        $metadata
                    );

                    //$response = $this->url->getUrl('thankyou');
                    $response['status'] = 'Success';
                    $response['url'] = $this->url->getUrl('thankyou');
                    //$resultRedirect->setUrl($customRedirectionUrl);
                    $this->_customerSession->setData($page_type, array());
                    //return $resultRedirect;
                    return $resultJson->setData($response);
                    
                } catch (\Exception $ex) {
                    
                    echo $ex->getMessage();
                    //$this->_messageManager->addError("Unable to send email");
                    //$this->_redirect($previous_full_url);
                    $response['status'] = "mailerror";
                    return $resultJson->setData($response);
                }
            } else {
                //$this->_messageManager->addError('Please fill up required fields');
                //$this->_redirect($previous_full_url);
                $response['status'] = "Error";
                return $resultJson->setData($response);
            }
        }
        else
        {
            $response['status'] = "CaptchaError";
            return $resultJson->setData($response);
            /*$this->messageManager->addError("Invalid Captcha");
            $this->_customerSession->setBrCaptchaError(true);
            $resultRedirect->setUrl($previous_full_url);
            return $resultRedirect;*/
        }
   
    }

    public function pageType()
	{
		$url = $this->_redirect->getRefererUrl();
		$second_last_slash = strrpos($url,'/',-2);
		$this->page_type = str_replace('/','',substr($url,$second_last_slash));
		$this->page_type = str_replace('.html','',$this->page_type);
		return $this->page_type;
	}

}
