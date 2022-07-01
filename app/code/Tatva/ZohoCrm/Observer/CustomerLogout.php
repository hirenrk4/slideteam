<?php
namespace Tatva\ZohoCrm\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class CustomerLogout implements ObserverInterface
{
	/**
    * Zoho CRM Helper
    * @var \Tatva\ZohoCrm\Helper\Data
    */
    protected $zohoCRMHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $sessionManager;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Http\Context $httpContext,
        \Tatva\ZohoCrm\Helper\Data $zohoCRMHelper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->httpContext = $httpContext;
        $this->zohoCRMHelper = $zohoCRMHelper;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	if($this->cookieManager->getCookie('zoho_customer_data')){
	        try{
	            $customerId = $this->getCustomerId();
                $connection = $this->resourceConnection->getConnection();

                $scopeSql="SET SESSION group_concat_max_len = 1048576";
                $connection->query($scopeSql);
                
	            $sql = "SELECT GROUP_CONCAT(page_uri SEPARATOR '\n') As page_uris, customer_id FROM zoho_customer_tracking where customer_id=".$customerId."  GROUP BY customer_id";
	            
	            $results = $connection->fetchAll($sql);

	            $urlsModule =array(
	                "URLS_Browsed"=>$results[0]['page_uris'],
	                "Priority"=>"4",
	                "Comment"=> "URL Browsed Information",
	            );
	            $this->zohoCRMHelper->editCustomer($urlsModule,$customerId);
	            $this->deleteCookie();

                $sqlUpdate="UPDATE zoho_customer_tracking SET isSend=1 WHERE customer_id=".$customerId;
                $connection->query($sqlUpdate);
	        } catch (\Exception $e) {
	        	print_r($e->getMessage());
	        }
	        return $this;
	    }
    }

    public function deleteCookie()
    {
    	if ($this->cookieManager->getCookie('zoho_customer_data')) {
        	$metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            		->setPath($this->sessionManager->getCookiePath())
                	->setDomain($this->sessionManager->getCookieDomain());

            return $this->cookieManager->deleteCookie(
            	'zoho_customer_data',$metadata);
        }
    }

    public function getCustomerId()
    {
        return $this->httpContext->getValue('customer_id');
    }
}