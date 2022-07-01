<?php
namespace Tatva\Catalog\Block;

class Logolink extends \Magento\Framework\View\Element\Template
{
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	)
	{
		$this->_storeManager=$storeManager;
		parent::__construct($context);
	}

	public function GetCustomData()
	{
		$storeCode= $this->_storeManager->getStore()->getCode();
        $homePageUrl= $this->_storeManager->getStore(1)->getBaseUrl();
        $result=[];
        $result['storeCode']=$storeCode;
        $result['homePageUrl']=$homePageUrl;
		return $result;
	}
}