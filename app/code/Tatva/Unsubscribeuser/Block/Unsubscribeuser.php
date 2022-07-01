<?php
namespace Tatva\Unsubscribeuser\Block;
use Tatva\Unsubscribeuser\Model\UnsubscribeFactory;
class Unsubscribeuser extends \Magento\Framework\View\Element\Template
{
    protected $_unsubscribeFactory;
    public function __construct(UnsubscribeFactory $UnsubscribeFactory,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->_unsubscribeFactory = $UnsubscribeFactory;
        parent::__construct($context);
    }

    public function getUnsubscribeData()
    {
        $model = $this->_unsubscribeFactory->create();
        $resultPageData = $model->load('*');
        $collection = $resultPageData->getCollection();
        return $collection;
    }
}
