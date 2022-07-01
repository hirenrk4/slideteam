<?php


namespace Tatva\Unsubscribeuser\Controller\Adminhtml\Items;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Tatva\Unsubscribeuser\Model\ResourceModel\Unsubscribeplan\CollectionFactory;
use Zend\Log\Filter\Timestamp;
use Magento\Store\Model\StoreManagerInterface;
class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $_scopeConfig;
    protected $_logLoggerInterface;
    protected $storeManager;
    protected $_customerRepositoryInterface;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $loggerInterface,
        StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Tatva\Subscription\Model\SubscriptionFactory $subscription,
        array $data = []
    )
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_scopeConfig = $scopeConfig;
        $this->_logLoggerInterface = $loggerInterface;
        $this->messageManager = $context->getMessageManager();
        $this->storeManager = $storeManager;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_subscription = $subscription;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collection->addFieldToFilter("customer_id",array("neq"=>0));
        $collection->addFieldToFilter("subscription_id",array("neq"=>0));
        $collectionSize = $collection->getSize();

        $successcount = 0;
        foreach ($collection as $record) {
            
            $statusRec = $record->getData('status');
            if ($statusRec == 'pending' || $statusRec == 'Pending')
            {     
                try
                {
                    $this->_subscription->create()->load($record->getSubscriptionId())->setUnsubscribeOrder(0)->save();
                    $statusCancel = 'Removed From Queue';
                    $record->setStatus($statusCancel)->save();
                    $successcount++;

                } catch(\Exception $e){
                    
                    $this->messageManager->addError($e->getMessage());
                    $this->_logLoggerInterface->debug($e->getMessage());
                    // exit;
                }
            }
        }

        if($successcount > 0)
        {
            $this->messageManager->addSuccess(__('The custmer plan removed successfully from queue, A total of %1 record(s) have been processed.', $successcount));
        }
        else
        {
            $this->messageManager->addError(__('Something went wrong. Please try again later.'));   
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}