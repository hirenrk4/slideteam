<?php


namespace Tatva\Unsubscribeuser\Controller\Adminhtml\Items;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Tatva\Unsubscribeuser\Model\ResourceModel\Unsubscribeplan\CollectionFactory;

use Zend\Log\Filter\Timestamp;
use Magento\Store\Model\StoreManagerInterface;
class MassDelete extends \Magento\Backend\App\Action
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
        
        foreach ($collection as $record) {
            
            $this->_subscription->create()->load($record->getSubscriptionId())->setUnsubscribeOrder(0)->save();
            $record->delete();

            $customer = $this->_customerRepositoryInterface->getById($record['customer_id']);
            $customerName = $customer->getFirstname();
            $customerEmail = $customer->getEmail();
            $delayedTime = $this->_scopeConfig->getValue('unsubscription_options/unsubscription/unsubscription_delay_time', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

            try
            {
                $this->_inlineTranslation->suspend();
                             
                $sender = [
                    'name' => 'Slideteam',
                    'email' => $this->_scopeConfig->getValue("trans_email/ident_sales/email", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
                ];
                 
                $sentToEmail = $customerEmail;
                $sentToName = $customerName;
                 
                 
                $transport = $this->_transportBuilder
                ->setTemplateIdentifier('unsubscribeuser_email_template_delete')
                ->setTemplateOptions(
                    [
                        'area' => 'frontend',
                        'store' => $this->storeManager->getStore()->getId()
                    ]
                    )
                    ->setTemplateVars([
                        'name'  => $customerName,
                        'message1' => 'Your Unsubscribe request removed.',
                        'message2' => 'Please contact to support.',
                        'subId' => $record['subscription_id'],
                        'custId' => $record['customer_id']
                    ])
                    ->setFrom($sender)
                    ->addTo($sentToEmail,$sentToName)
                    //->addTo('owner@example.com','owner')
                    ->getTransport();
                     
                    $transport->sendMessage();
                     
                    $this->_inlineTranslation->resume();
                    
                    $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));
                     
            } catch(\Exception $e){
                $this->messageManager->addError($e->getMessage());
                $this->_logLoggerInterface->debug($e->getMessage());
                // exit;
            }

        }

        if($collectionSize != 0){

            
        }else{
            $this->messageManager->addNoticeMessage(__('A total of %1 record(s) have not deleted,  The customer does not request for unsubscribe plan  .', $collectionSize));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
