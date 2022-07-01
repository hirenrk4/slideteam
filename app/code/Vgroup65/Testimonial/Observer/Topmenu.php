<?php
namespace Vgroup65\Testimonial\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\ObserverInterface;
class Topmenu implements ObserverInterface
{
    protected $testimonialConfiguration;
    
    protected $_url;
    public function __construct(
       \Magento\Cms\Block\Block $cmsBlock, \Magento\Framework\UrlInterface $url,     
       \Vgroup65\Testimonial\Model\ConfigurationFactory $configuration
    )
    {
        $this->_url = $url;
        $this->testimonialConfiguration = $configuration;
    }
    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $testimonialConfiguration = $this->testimonialConfiguration->create();
        $testimonialConfigurationCollection = $testimonialConfiguration->getCollection(); 
        $testimonialConfigurationCollection->addFieldToFilter('configuration_id', '1');
        foreach($testimonialConfigurationCollection as $values):
            $title = $values['top_menu_link'];
            $display_top_menu = $values['display_top_menu'];
        endforeach;
        
        /** @var \Magento\Framework\Data\Tree\Node $menu */
        $menu = $observer->getMenu();
        
        if($display_top_menu == 1):
            $tree = $menu->getTree();
            $data = [
                'name'      => __($title),
                'id'        => 'vgroupinc-testimonial-menu-id',
                'url'       => $this->_url->getUrl('testimonial/testimonial/index'),
                'is_active' => false
            ];
            $node = new Node($data, 'id', $tree, $menu);
            $menu->addChild($node);
        endif;     
        return $this;
    }
}