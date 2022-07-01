<?php
namespace Tatva\Customdesignsubcategories\Block;

class Queryform extends \Magento\Framework\View\Element\Template
{
	protected $_CustomDesignSubCategoriesModel;
	protected $_urlInterface;
	protected $_customerSession;

	public function __construct
	(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\UrlInterface $urlInterface,
		\Magento\Customer\Model\Session $customerSession,
		\Tatva\Customdesignsubcategories\Model\Customdesignsubcategories $customDesignSubCategories
	)
	{
		$this->_customerSession = $customerSession;
		$this->_urlInterface = $urlInterface;
		$this->_CustomDesignSubCategoriesModel = $customDesignSubCategories;
		$this->_CustomDesignSubCategoriesModel->saveDataAccPageType($this->getPageType());
		parent::__construct($context);
	}

	public function getPageTypeData()
	{
		return $this->_CustomDesignSubCategoriesModel->getFormHeading();
	}

	public function getClassNameData()
	{
		return $this->_CustomDesignSubCategoriesModel->getFormClassName();
	}

	public function getPageType()
	{
		$url = $this->_urlInterface->getCurrentUrl();
		$last_slash = strrpos($url,'/');
		$second_last_slash = strrpos($url,'/',-2);
		$this->page_type = str_replace('/','',substr($url,$second_last_slash));
		$this->page_type = str_replace('.html','',$this->page_type);
		return $this->page_type;
	}

	public function getCustomerSession()
	{
		return $this->_customerSession;
	}

	public function getModel()
	{
		return $this->_CustomDesignSubCategoriesModel;
	}
}