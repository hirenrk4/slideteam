<?php
/**
 * @category   Tatva
 * @package    Tatva_Gtm
 */
namespace Tatva\Gtm\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 * @package Tatva\Gtm\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const XPATH_TATVA_GTM_ENABLED = "button/gtm_config/field1";

	const XPATH_TATVA_GTM_CODE = "button/gtm_config/field2";

	/**
     * @var Context
     */
    protected $context;
    
	public function __construct(
		Context $context
	)
	{
        $this->context = $context;
        parent::__construct($context);
	}


	public function getGtmStatus()
	{
		return (bool)$this->scopeConfig->getValue(
            self::XPATH_TATVA_GTM_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}

	public function getGtmCode()
	{
		return $this->scopeConfig->getValue(
			self::XPATH_TATVA_GTM_CODE,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

}