<?php
namespace Tatva\Paypalrec\Controller\Unsubscribe;

use \Magento\Framework\App\Action\Action;

class Index extends \Tatva\Paypalrec\Controller\Unsubscribe
{
     /**
     * @var string $payment_method
     */
    private $_paymentMethod;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendAuthSession;

    /**
     * @var \Tatva\Subscription\Model\SubscriptionFactory
     */
    protected $_subscriptionSubscriptionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_generic;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_salesOrderFactory;

    /**
     * @var \Tatva\Paypalrec\Model\ResourceModel\Result\CollectionFactory
     */
    protected $_paypalrecResultCollectionFactory;

    /**
     * @var \Magento\Email\Model\TemplateFactory
     */
    protected $_emailTemplateFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;

    /**
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_inlineTranslation;

    /**
     * [$_notification description]
     * @var \Tco\Checkout\Model\Ins
     */
    protected $_ins;
    /**
     * [$_checkout description]
     * @var \Tco\Checkout\Model\Checkout
     */
    protected $_checkout;

    protected  $_sendemail;

    protected  $_urlInterface;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Backend\Model\Auth\Session $_backendAuthSession,
        \Tatva\Subscription\Model\Subscription $_subscriptionSubscriptionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $_scopeConfig,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $_salesOrderFactory,
        \Tatva\Paypalrec\Model\ResourceModel\Result\CollectionFactory $_paypalrecResultCollectionFactory,
        \Tatva\Paypalrec\Model\ResourceModel\PaypalRecurringMapper\CollectionFactory $_paypalrecMapperResultCollectionFactory,
        \Magento\Email\Model\TemplateFactory $_emailTemplateFactory,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Payment\Helper\Data $_paymentHelper,
        \Magento\Framework\Translate\Inline\StateInterface $_inlineTranslation,
        \Tco\Checkout\Model\Ins $_ins,
        \Tco\Checkout\Model\Checkout $_checkout,
        \Tatva\Paypalrec\Helper\SendEmail $_sendemail,
        \Magento\Framework\UrlInterface $_urlInterface,
        \Magento\Backend\Helper\Data $_helper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Tatva\Emarsys\Helper\ApiData $EmarsysHelper,
        \Tatva\Subscription\Helper\EmarsysHelper $emarsysApiHelper,
        \Tatva\ZohoCrm\Helper\Data $zohoCRMHelper,
        \Amasty\RecurringPayments\Api\Subscription\RepositoryInterface $subscriptionRepository,
        \Amasty\RecurringPayments\Model\Subscription\Operation\SubscriptionCancelOperation $subscriptionCancelOperation,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Customer $customerModel,
        \Tatva\Subscription\Helper\Data $SubscriptionHelper,
        \Tatva\Subscription\Helper\TeamPlans $teamplanModel
    ) {
        parent::__construct(
            $context,
            $_backendAuthSession,
            $_subscriptionSubscriptionFactory,
            $_scopeConfig,
            $messageManager,
            $_salesOrderFactory,
            $_paypalrecResultCollectionFactory,
            $_paypalrecMapperResultCollectionFactory,
            $_emailTemplateFactory,
            $_storeManager,
            $_paymentHelper,
            $_inlineTranslation,
            $_ins,
            $_checkout,
            $_sendemail,
            $_urlInterface,
            $_helper,
            $transportBuilder,
            $httpClientFactory,
            $logger,
            $date,
            $EmarsysHelper,
            $emarsysApiHelper,
            $zohoCRMHelper,
            $subscriptionRepository,
            $subscriptionCancelOperation,
            $session,
            $registry,
            $customerModel,
            $SubscriptionHelper,
            $teamplanModel
        );
    }
    
    public function execute(){
        $url=$this->index();
        $this->_redirect($url);
    }
}