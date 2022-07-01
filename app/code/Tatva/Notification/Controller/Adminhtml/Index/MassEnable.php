<?php

namespace Tatva\Notification\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Tatva\Notification\Model\ResourceModel\Notification\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class MassEnable extends \Magento\Backend\App\Action
{
    /**
     *  Filter
     *
     * @var Filter
     */
    protected $filter;

    const ADMIN_RESOURCE = 'Tatva_Notification::massEnable';

    /**
     *  CollectionFactory
     *
     * @var CollectionFactory
     */
    protected $collectionFactory;


    /**
     * Construct
     *
     * @param Context           $context           context
     * @param Filter            $filter            filter
     * @param CollectionFactory $collectionFactory collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
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
        $collectionSize = $collection->getSize();

        foreach ($collection as $item) {
            $item->setStatus(1)->save();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been enabled.', $collectionSize));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
