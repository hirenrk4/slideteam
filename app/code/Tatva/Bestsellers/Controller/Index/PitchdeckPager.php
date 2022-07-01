<?php

namespace Tatva\Bestsellers\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class PitchdeckPager extends \Magento\Framework\App\Action\Action
{

    protected $_resultPageFactory;
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result =  $this->_resultLayoutFactory->create();
        $response =  $this->resultJsonFactory->create();
        $resultPage = $this->_resultPageFactory->create();
        
        if ($this->getRequest()->isAjax()) 
            {
                $block = $resultPage->getLayout()
                    ->createBlock('Tatva\Catalog\Block\Product\PitchdeckProductPager')
                    ->setTemplate('Magento_Cms::pitch-deck/pitchdeck_slide_collection_pager.phtml')
                    ->toHtml();

                $response->setData($block);
                return $response;
            }
    }

}
