<?php
namespace Tatva\SLIFeed\Controller\Adminhtml\System\Config\Feed;

use SLI\Feed\Controller\Adminhtml\System\Config\Feed;

class Update extends Feed
{
    public function execute() {
        session_write_close();
        $this->getUpdateFeedManager()->updateAllStores();
    }

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