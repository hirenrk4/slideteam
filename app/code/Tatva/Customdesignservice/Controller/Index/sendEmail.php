<?php
namespace Tatva\Customdesignservice\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class sendEmail extends \Magento\Framework\App\Action\Action
{
	protected $_customerSession;
	protected $_filesystem;
	protected $_messageManager;
	protected $_storeManager;
	protected $_template;
	protected $_transportBuilder;
	protected $_scopeConfig;
	protected $_redirect;

	const XML_PATH_EMAIL_CUSTMDESIGNSERVICES = 'tatva/customdesignservices/email';

	public function __construct
	(
		Context $context,
		\Magento\Framework\App\Response\RedirectInterface $redirectInterface,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Controller\ResultFactory $resultFactory,
		\Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\UrlInterface $url
	)
	{
		$this->_customerSession = $customerSession;
		$this->_redirect = $redirectInterface;
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

		parent::__construct($context);
	}

	public function execute() {

		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $previous_full_url = $this->_redirect->getRefererUrl();

        $bs_params = array();
        $bs_params = $this->getRequest()->getParams();
        $captcha_formID = 'custom_design_service_captcha';
        $this->_customerSession->setData('bs_params', array($bs_params));        
		$_captcha = $this->_customerSession->getData($captcha_formID . '_word');

		/*Master captcha*/
        $master_enable = $this->_scopeConfig->getValue('customer/captcha/master_enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $master_captcha = $this->_scopeConfig->getValue('customer/captcha/master_captcha', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($bs_params['captcha'][$captcha_formID] != $_captcha['data'])
        {
            if($master_enable)
            {
                $_captcha['data'] = $master_captcha;
            }
        }

		if ($bs_params['captcha'][$captcha_formID] == $_captcha['data']) 
		{

	        if ((count($bs_params) > 0) && !empty($bs_params['name']) && !empty($bs_params['email']) && !empty($bs_params['comment'])) 
	        {
	        	$attachment_dir = 'design_services_files/';
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
					foreach ($_uniqueFilesToAttach as $key => $value) {
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
						$this->_messageManager->addError('Your attachment size is greater than 5MB. Please reduce the size of the attachment(s) or use Dropbox/Google Drive or a similar tool to send us the attachment(s). OR Send the attachment directly via email to <a href="mailto:design@slideteam.net">design@slideteam.net</a>');
						$resultRedirect->setUrl($previous_full_url);
	        			return $resultRedirect;
					}

					$allowed_file_type = array("pdf", "ppt", "pptx","doc", "docx", "jpg", "jpeg", "png", "gif", "xlsx", "xlsm", "xlsb", "xls", "xltx", "xltm", "xlt", "csv", "xlam", "xla", "ods", "zip", "txt");
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
								$this->_messageManager->addError('This extension is not allowed.');
								$resultRedirect->setUrl($previous_full_url);
	        					return $resultRedirect;
							}
						}
					}
				}

	            $store = $this->_storeManager->getStore()->getId();
	            $emailTemplateVariables = array();
	            $emailTemplateVariables = $this->getRequest()->getParams();
	            $emailTemplateVariables['emailTitle'] = 'Design Services Request';
				$emailTemplateVariables['tableTitle'] = 'Design Services Form of '.$emailTemplateVariables['name'];
				$emailTemplateVariables['emailSubject'] = "Design Services Form of ".$emailTemplateVariables['name'] ;
	            
	            $headers = 'MIME-Version: 1.0' . "\r\n";
	            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	            
	            $to_array = $this->_scopeConfig->getValue('contact/customemail/recipient_email_design_services');
	            $email_to = explode(",",$to_array);
	            $cc_array = $this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_design_services');
	            $email_cc = explode(",",$cc_array);
	            $bcc_array = $this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_design_services');
	            $email_bcc = explode(",",$bcc_array);
	            
	            try{
	                $this->_template  = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_CUSTMDESIGNSERVICES, ScopeInterface::SCOPE_STORE, $store);
	                $this->_transportBuilder->setTemplateIdentifier($this->_template)
	                        ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store))
	                        ->setTemplateVars($emailTemplateVariables)
							->setFrom(['name' => 'SlideTeam Support','email' => 'support@slideteam.net']);

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

	                $emailTemplateVariables['emailSubject'] = 'Design Services request Form submitted';
	                $emailTemplateVariables['tableTitle'] = 'Thank you for submitting a request to SlideTeam, Our team will contact you soon. Below is a copy of the request you sent for reference.';
					
					$this->_template  = $this->_scopeConfig->getValue(self::XML_PATH_EMAIL_CUSTMDESIGNSERVICES, ScopeInterface::SCOPE_STORE, $store);
	                $this->_transportBuilder->setTemplateIdentifier($this->_template)
	                        ->setTemplateOptions(array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $store))
	                        ->setTemplateVars($emailTemplateVariables)
							->setFrom(['name' => 'Slideteam Design Team','email' => 'design@slideteam.net'])
	                        ->addTo($emailTemplateVariables['email'], $emailTemplateVariables['name']);
 

						foreach ($oneDimensionalArray as $file)
						{
							if (file_exists($file_dir.$file))
							{
								$this->_transportBuilder->addAttachment(file_get_contents($file_dir.$file),$file);						
							}
						}					
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
	                $resultRedirect->setUrl($previous_full_url);
	        		return $resultRedirect;
	            }
	        } else {
	            $this->_messageManager->addError('Please fill up required fields');
	            $resultRedirect->setUrl($previous_full_url);
	        	return $resultRedirect;
	        }
	    } else {
	    	$this->_messageManager->addError("Invalid Captcha");
	        $resultRedirect->setUrl($previous_full_url);
	        return $resultRedirect;
	    }
    }
}