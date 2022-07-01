<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2017 Emarsys. (http://www.emarsys.net/)
 */
namespace Tatva\Emarsys\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;

class ApiData extends \Magento\Framework\App\Helper\AbstractHelper
{
   /**
     * @var Context
     */
    protected $context;

     public function __construct(
        Context $context
    ) {
        $this->context = $context;
        parent::__construct($context);
    }
   /**
    * isApiEnabled
    *
    * @return void
    */
   public function isApiEnabled ()
   {
      return $this->scopeConfig->getValue(
         'button/emarsys_config/field3',
         \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   }
   /**
    * getApiUrl
    *
    * @return void
    */
   public function getApiUrl ()
   {
      return $this->scopeConfig->getValue(
         'button/emarsys_config/field4',
         \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   }
}