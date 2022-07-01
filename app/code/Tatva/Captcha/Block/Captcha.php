<?php

namespace Tatva\Captcha\Block;

class Captcha extends \Magento\Captcha\Block\Captcha\DefaultCaptcha
{
	protected $_CustomDesignSubCategoriesModel;
	 protected $_template = 'default.phtml';
	 protected $_customtemplate = 'Tatva_Downloadable::product/view/captcha.phtml';
	protected $_reviewtemplate = "Magento_Review::product/view/captcha.phtml";
	public function __construct
	(
		\Tatva\Customdesignsubcategories\Model\Customdesignsubcategories $customDesignSubCategories,
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Captcha\Helper\Data $captchaData,
		array $data = []
	) 
	{
		$this->_captchaData = $captchaData;
		$this->_CustomDesignSubCategoriesModel = $customDesignSubCategories;
		parent::__construct($context, $captchaData,$data);
		$this->_isScopePrivate = true;
	}

	public function getCaptchaModel()
	{
		if(!empty($this->_CustomDesignSubCategoriesModel->getCaptchaFormId()))
		{
			$this->setFormIdCustom();
		}
		
		return $this->_captchaData->getCaptcha($this->getFormId());
	}

	public function setFormIdCustom()
	{
		$this->setFormId($this->_CustomDesignSubCategoriesModel->getCaptchaFormId());
	}

	public function getCaptchaId()
	{
		return $this->_CustomDesignSubCategoriesModel->getCaptchaFormId();
	}

	public function getTemplate()
    {
    	if($this->getFormId()=="downloadable_captcha" || $this->getFormId() == $this->getCaptchaId())
    	{
    		return $this->getIsAjax() ? '' : $this->_customtemplate;
    	}
    	if($this->getFormId()=="review-form" || $this->getFormId() == $this->getCaptchaId())
    	{
    		return $this->getIsAjax() ? '' : $this->_reviewtemplate;
    	}
    	if($this->getFormId()=="product-contact-cap")
    	{
    		return $this->getIsAjax() ? '' : 'Tatva_Downloadable::product/view/captcha-contact.phtml';
    	}
        return $this->getIsAjax() ? '' : $this->_template;
    }
	
	public function getCacheLifetime()
	{
		return NULL;
	}
}
