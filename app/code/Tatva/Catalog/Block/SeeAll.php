<?php

namespace Tatva\Catalog\Block;

class SeeAll extends \Magento\Framework\View\Element\Template
{
	private $catalogCategoryFactory;
	private $scopeConfig;

	public function __construct(
		\Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\View\Element\Template\Context $context
	)
	{
		$this->catalogCategoryFactory = $catalogCategoryFactory;
		$this->scopeConfig = $scopeConfig;
		parent::__construct($context);
	}

	public function getOnepagecategory()
	{
		$onepagecategoryId = $this->scopeConfig->getValue('resume/general/onepagecategoryName', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		return $this->catalogCategoryFactory->create()->load($onepagecategoryId);
	}
	public function getDocReportCategory()
	{
		$docReportcategoryId = $this->scopeConfig->getValue('resume/general/document_report_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		return $this->catalogCategoryFactory->create()->load($docReportcategoryId);
	}
	public function getUkrainCrisisCategory()
	{
		$docReportcategoryId = $this->scopeConfig->getValue('resume/general/ukrain_crisis_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		return $this->catalogCategoryFactory->create()->load($docReportcategoryId);
	}
	public function getLetterheadCategory()
	{
		$letheadcategoryId = $this->scopeConfig->getValue('resume/general/letterhead_category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		return $this->catalogCategoryFactory->create()->load($letheadcategoryId);
	}
}