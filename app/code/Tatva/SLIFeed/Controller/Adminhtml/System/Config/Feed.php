<?php
namespace Tatva\SLIFeed\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Tatva\SLIFeed\FeedManager;
use SLI\Feed\Model\GenerateFlag;

abstract class Feed extends Action
{
    /**
     * Return feed manager singleton
     *
     * @return FeedManager
     */
    protected function getUpdateFeedManager()
    {
        return $this->_objectManager->get('Tatva\SLIFeed\FeedManager');
    }

   
}