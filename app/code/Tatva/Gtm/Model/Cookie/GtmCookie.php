<?php
namespace Tatva\Gtm\Model\Cookie;
 
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
 
class GtmCookie
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;
 
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $_cookieMetadataFactory;
 
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_sessionManager;   
 
    /**
     * [__construct ]
     *
     * @param CookieManagerInterface                    $cookieManager
     * @param CookieMetadataFactory                     $cookieMetadataFactory
     * @param SessionManagerInterface                   $sessionManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager        
    ) {
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;       
    }
 
    /**
     * Get data from cookie set in remote address
     *
     * @return value
     */
    public function get($name)
    {
        return $this->_cookieManager->getCookie($name);
    }
 
    /**
     * Set data to cookie in remote address
     *
     * @param [string] $value    [value of cookie]
     * @param integer  $duration [duration for cookie]
     *
     * @return void
     */
    public function set($name , $value, $duration = 86400)
    {	    	
    	$domain = substr($this->_sessionManager->getCookieDomain(),3);		
		
        $metadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath($this->_sessionManager->getCookiePath())
            ->setDomain($domain);
 
        $this->_cookieManager->setPublicCookie(
            $name,
            $value,
            $metadata
        );
    }
 
    /**
     * delete cookie remote address
     *
     * @return void
     */
    public function delete($name)
    {
    	$domain = substr($this->_sessionManager->getCookieDomain(),3);
		
        $this->_cookieManager->deleteCookie(
            $name,
            $this->_cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->_sessionManager->getCookiePath())
                ->setDomain($domain)
        );
    }
}