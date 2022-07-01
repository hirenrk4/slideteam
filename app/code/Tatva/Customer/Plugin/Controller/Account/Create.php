<?php 

namespace Tatva\Customer\Plugin\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
use Zend_Controller_Router_Route;
class Create
{

	protected $_redirect;

	/**
	 * [$_customerSession ]
	 * @var [\Magento\Customer\Model\Session]
	 */
	protected $_customerSession;

	/**
     * List of required request parameters
     * Order sensitive
     * @var string[]
     */
    protected $_requiredParams = ['moduleFrontName', 'actionPath', 'actionName'];

	/**
	 * [$_urlInterface ]
	 * @var [\Magento\Framework\UrlInterface]
	 */
	protected $_urlInterface;

	public function __construct(
		\Magento\Framework\App\Response\RedirectInterface $redirect,
        UrlInterface $urlInterface,
		\Magento\Customer\Model\Session $customerSession)
	{
		$this->_redirect = $redirect;
		$this->_customerSession = $customerSession;        
        $this->_urlInterface = $urlInterface;    
	}

	/**
     * This is not required for redirection as all the logic is now in LoginPostAfter Plugin
     */
	public function beforeExecute(\Magento\Customer\Controller\Account\Create $create)
	{

		/*$refrelUrl = $this->_redirect->getRedirectUrl();
		$refrelUrlParams = $this->getUrlParams($refrelUrl);
		$isCartRedirected = isset($refrelUrlParams['variables']) ? (isset($refrelUrlParams['variables']['cart']) ? $refrelUrlParams['variables']['cart'] : false ) : false;
		$referer = isset($refrelUrlParams['variables']) ? (isset($refrelUrlParams['variables']['referer']) ? $refrelUrlParams['variables']['referer'] : false ) : false;

		*/


		/*$redirectUrl = $this->_urlInterface->getUrl('pricing');
		if($redirectUrl){
			$this->_customerSession->setBeforeAuthUrl($redirectUrl);
		}*/
		
	}

	/**
	 * return params like magento router
	 * @param  string $url full url
	 * @return array of parameters
	 */
	private function getUrlParams(string $url) {
	    $output = [];
	    $url = str_replace($this->_urlInterface->getUrl(),"",$url);
        $path = trim($url, '/');

        $params = explode('/', $path );
        foreach ($this->_requiredParams as $paramName) {
            $output[$paramName] = array_shift($params);
        }

        for ($i = 0, $l = sizeof($params); $i < $l; $i += 2) {
            $output['variables'][$params[$i]] = isset($params[$i + 1]) ? urldecode($params[$i + 1]) : '';
        }
        return $output;
	}
}