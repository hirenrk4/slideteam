<?php 
namespace Tatva\Catalog\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
class AddSeoUrl implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
    	$select = $observer->getSelect();
    	return $select->columns('seo_url');
    }
}
?>