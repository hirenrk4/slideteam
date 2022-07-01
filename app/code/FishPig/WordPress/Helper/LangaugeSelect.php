<?php
/*
 *
 */
namespace FishPig\WordPress\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use FishPig\WordPress\Helper\CoreInterface;

class LangaugeSelect extends AbstractHelper
{


	protected $customerSession;

	/**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory CookieMetadataFactory
     */
    private $cookieMetadataFactory;

	public function __construct(
		Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory

	)
	{
		parent::__construct($context);
		$this->customerSession = $customerSession;
		$this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
	}

	/**
	* @reutrn langauge 
	*/
	public function getLangaugeSession($params){

		$langaugeCookie = '';

		$langaugeCookie = $this->cookieManager->getCookie('blog_language');

		$langParam = '';

		if(isset($params['lang'])){
		    $langaugeCookie = ($langaugeCookie != $params['lang'])?$params['lang']:$langaugeCookie;
		}

		if ($langaugeCookie == 'Spanish' || $langaugeCookie == 'German' || $langaugeCookie == 'French' || $langaugeCookie == 'Portuguese' || $langaugeCookie == 'spanish' || $langaugeCookie == 'german' || $langaugeCookie == 'french' || $langaugeCookie == 'portuguese')
		{
		   return $langParam = $langaugeCookie;
		} 
		return $langParam;
	}
}
