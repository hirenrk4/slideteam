<?php
namespace Tatva\ZohoCrm\Controller\Index;


class SendCustomerActivity extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $currentGMTDate;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $sessionManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Tatva\ZohoCrm\Helper\Data $zohoCRMHelper,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->httpContext = $httpContext;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->zohoCRMHelper = $zohoCRMHelper;
        $this->sessionManager = $sessionManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->currentGMTDate = $dateTimeFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
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

            $sqlUpdate="UPDATE zoho_customer_tracking SET isSend=1 WHERE customer_id=".$customerId;
            $connection->query($sqlUpdate);

            $response = ['message' => 'success'];
            $metadata = $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDuration(86400)
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain());
            $currentDate = $this->currentGMTDate->create()->gmtDate('Y-m-d H:i:s');
            $this->cookieManager->setPublicCookie(
                "zoho_customer_data",
                $currentDate,
                $metadata
            );
        } catch (\Exception $e) {
            $response = ['message' => $e->getMessage()];
        }
        $resultJson->setData($response);
        return $resultJson;
    }

    public function getCustomerId()
    {
        return $this->httpContext->getValue('customer_id');
    }

}