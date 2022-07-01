<?php

namespace Tatva\Customdesignsubcategories\Model;

class Customdesignsubcategories extends \Magento\Framework\Model\AbstractModel
{
    private $page_type;

    private $attachement_saving_dir;

    private $email_subject;

    private $email_message_title;

    private $table_title;

    private $email_reciept;

    private $form_heading;

    private $form_class_name;

    private $captcha_form_id;

    private $bcc_email_reciept;

    public function __construct
    (
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }


    public function saveDataAccPageType($page_type)
    {   
        if(!is_null($page_type))
        {
            $this->setPageType($page_type);
        }

        if(!is_null($this->getPageType()))
        {
            switch ($this->getPageType()) 
            {
                case 'custom-powerpoint-diagrams':
                $this->setAttachementSavingDir("powerpoint_diagrams_files/");
                $this->setEmailSubject("Custom PowerPoint Diagram Form Of ");
                $this->setCustomerSubject("Custom PowerPoint Diagram Request submitted");
                $this->setEmailMessageTitle("Custom PowerPoint Diagram Request");
                $this->setTableTitle("Custom PowerPoint Diagram Form Of ") ;
                $this->setEmailReciept($this->_scopeConfig->getValue('contact/customemail/recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setCcEmailReciept($this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setBccEmailReciept($this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setFormHeading('Submit Your Custom PowerPoint Diagram Request');
                $this->setFormClassName('custom-powerpoint-diagrams');
                $this->setCaptchaFormId('custom_powerpoint_diagrams_captcha');
                break;

                case 'flat-powerpoint-designs':
                $this->setAttachementSavingDir("flat_powerpoint_files/");
                $this->setEmailSubject("Custom Flat PowerPoint Design Form Of ");
                $this->setCustomerSubject("Custom Flat PowerPoint Design Request submitted");
                $this->setEmailMessageTitle("Custom Flat PowerPoint Design Request");
                $this->setTableTitle("Custom Flat PowerPoint Design Form Of ") ;
                $this->setEmailReciept($this->_scopeConfig->getValue('contact/customemail/recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setCcEmailReciept($this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setBccEmailReciept($this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setFormHeading('Submit Your Flat PowerPoint Design Request');
                $this->setFormClassName('flat-powerpoint-designs');
                $this->setCaptchaFormId('flat_powerpoint_designs_captcha');
                break;

                case 'custom-business-slides':
                $this->setAttachementSavingDir("custom_business_files/");
                $this->setEmailSubject("Custom Business Slide Form of ");
                $this->setCustomerSubject("Custom Business Slides Request submitted");
                $this->setEmailMessageTitle("Custom Business Slides Request");
                $this->setTableTitle("Custom Business Slides Form of ");
                $this->setEmailReciept($this->_scopeConfig->getValue('contact/customemail/recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setCcEmailReciept($this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setBccEmailReciept($this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setFormHeading('Submit Your Custom Business Slides Request');
                $this->setFormClassName('custom-business-slides');
                $this->setCaptchaFormId('custom_business_slides_captcha');
                break;

                case 'custom-powerpoint-themes':
                $this->setAttachementSavingDir("custom_theme_files/");
                $this->setEmailSubject("Custom PowerPoint Themes Form of ");
                $this->setCustomerSubject("Custom PowerPoint Themes Request submitted");
                $this->setEmailMessageTitle("Custom PowerPoint Themes Request");
                $this->setTableTitle("Custom PowerPoint Themes Form of ") ;
                $this->setEmailReciept($this->_scopeConfig->getValue('contact/customemail/recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setCcEmailReciept($this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setBccEmailReciept($this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setFormHeading('Submit Your Custom PowerPoint Themes Request');
                $this->setFormClassName('custom-powerpoint-themes');
                $this->setCaptchaFormId('custom_powerpoint_themes_captcha');
                break;

                case 'professional_word_formatting_services':
                $this->setAttachementSavingDir("professional_word_formatting_services/");
                $this->setEmailSubject("Professional Word Formatting Services Form of ");
                $this->setCustomerSubject("Professional Word Formatting Services Request submitted");
                $this->setEmailMessageTitle("Professional Word Formatting Services Request");
                $this->setTableTitle("Professional Word Formatting Services Form of ") ;
                $this->setEmailReciept($this->_scopeConfig->getValue('contact/customemail/recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setCcEmailReciept($this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setBccEmailReciept($this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setFormHeading('Submit Your Professional Word Formatting Services Request');
                $this->setFormClassName('professional_word_formatting_services');
                $this->setCaptchaFormId('professional_word_formatting_services_captcha');
                break;

                case 'logo_design_and_branding':
                $this->setAttachementSavingDir("branding_files/");
                $this->setEmailSubject("Logo design and Branding Form of ");
                $this->setCustomerSubject("Logo design and Branding Services Request submitted");
                $this->setEmailMessageTitle("Logo design and Branding Services Request");
                $this->setTableTitle("Logo design and Branding Form of ") ;
                $this->setEmailReciept($this->_scopeConfig->getValue('contact/customemail/recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setCcEmailReciept($this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setBccEmailReciept($this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setFormHeading('Submit Your Logo Design and Branding Request');
                $this->setFormClassName('logo_design_and_branding');
                $this->setCaptchaFormId('logo_design_and_branding_captcha');
                break;

                case 'infographics':
                $this->setAttachementSavingDir("infographic_files/");
                $this->setEmailSubject("Infographic Form of ");
                $this->setCustomerSubject("Infographic Services Request submitted");
                $this->setEmailMessageTitle("Infographic Services Request");
                $this->setTableTitle("Infographic Form of ") ;
                $this->setEmailReciept($this->_scopeConfig->getValue('contact/customemail/recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setCcEmailReciept($this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setBccEmailReciept($this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setFormHeading('Submit Your Infographics Request');
                $this->setFormClassName('infographics');
                $this->setCaptchaFormId('infographics_captcha');
                break;

                case 'marketing_collaterals':
                $this->setAttachementSavingDir("marketing_files/");
                $this->setEmailSubject("Marketing Collaterals Form of ");
                $this->setCustomerSubject("Marketing Collaterals Services Request submitted");
                $this->setEmailMessageTitle("Marketing Collaterals Services Request");
                $this->setTableTitle("Marketing Collaterals Form of ") ;
                $this->setEmailReciept($this->_scopeConfig->getValue('contact/customemail/recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setCcEmailReciept($this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setBccEmailReciept($this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_design_services', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setFormHeading('Submit Your Marketing Collateral Request');
                $this->setFormClassName('marketing_collaterals');
                $this->setCaptchaFormId('marketing_collaterals_captcha');
                break;

                case 'business_research_services':
                $this->setAttachementSavingDir("businessresearch_files/");
                $this->setEmailSubject("Business Research Requirement Form of ");
                $this->setCustomerSubject("Business Research Requirement Services Request submitted");
                $this->setEmailMessageTitle("Business Research Requirement Services Request");
                $this->setTableTitle("Business Research Requirement Form of ") ;
                $this->setEmailReciept($this->_scopeConfig->getValue('contact/customemail/recipient_email_businessresearch', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setCcEmailReciept($this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_businessresearch', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setBccEmailReciept($this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_businessresearch', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setFormHeading('Submit Your Business Research Requirements');
                $this->setFormClassName('business_research_services');
                $this->setCaptchaFormId('business_research_services_captcha');
                break;

                default:
                $this->setAttachementSavingDir("businessresearch_files/");
                $this->setEmailSubject("Business Research Requirement Form of ");
                $this->setCustomerSubject("Business Research Requirement Services Request submitted");
                $this->setEmailMessageTitle("Business Research Requirement Services Request");
                $this->setTableTitle("Business Research Requirement Form of ") ;
                $this->setEmailReciept($this->_scopeConfig->getValue('contact/customemail/recipient_email_businessresearch', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setCcEmailReciept($this->_scopeConfig->getValue('contact/customemail/cc_recipient_email_businessresearch', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setBccEmailReciept($this->_scopeConfig->getValue('contact/customemail/bcc_recipient_email_businessresearch', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                $this->setFormHeading('Submit Your Business Research Requirements');
                $this->setFormClassName('business_research_services');
                $this->setCaptchaFormId('business_research_services_captcha');
                break;
            }
        }
    }
}
