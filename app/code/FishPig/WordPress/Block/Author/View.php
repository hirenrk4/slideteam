<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Author;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
	/**
	 * Caches and returns the current category
	 *
	 * @return \FishPig\WordPress\Model\User
	 */
	protected $date;

	protected $_registry;
        
        public function __construct(
                \Magento\Framework\View\Element\Template\Context $context, 
                \FishPig\WordPress\Model\Context $wpContext, 
                \FishPig\WordPress\Model\Post $post,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManger,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
                array $data = array()
        ) {
            parent::__construct($context, $wpContext, $data);
            $this->_post = $post;
            $this->date = $date;
            $this->_scopeConfig = $scopeConfig;
            $this->_storeManger = $storeManger;
            $this->_registry = $wpContext->getRegistry();
        }


        public function getUserRegistrationDate($author){
      return $this->date->timestamp($author);
	}			
	public function getEntity()
	{
		return $this->_registry->registry(\FishPig\WordPress\Model\User::ENTITY);
	}
	
	/**
	 * Generates and returns the collection of posts
	 *
	 * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
	 */
	protected function _getPostCollection()
	{
		return parent::_getPostCollection()->addFieldToFilter('post_author', $this->getEntity()->getId());
	}
}
