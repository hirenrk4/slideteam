<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Post\View;

class Related extends \FishPig\WordPress\Block\Post
{

	protected $_resourceConnection;

	protected $_collection = null;

	protected $_registry;

	protected $_factory;

	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, 
        \FishPig\WordPress\Model\Context $wpContext, 
        \FishPig\WordPress\Model\ResourceConnection $resourceConnection,
		\FishPig\WordPress\Model\Plugin $pluginHelper,
        array $data = array()
    ) {
        parent::__construct($context, $wpContext, $data);
        $this->_resourceConnection = $resourceConnection;
        $this->pluginHelper = $pluginHelper;
        $this->_registry  = $wpContext->getRegistry();
        $this->_factory = $wpContext->getFactory();

    }

	public function isPluginEnabled()
	{
		return $this->pluginHelper->isEnabled('yet-another-related-posts-plugin/yarpp.php');
	}
	
	protected function _beforeToHtml()
	{
		if(!$this->isPluginEnabled()){
			return $this;
		}

		parent::_beforeToHtml();

		$this->setPosts($this->_getPostCollection());

		if (!$this->getTemplate()) {
			$this->setTemplate('post/view/related.phtml');
		}

		return $this;
	}

	public function getPost()
	{
		return $this->hasPost() ? $this->_getData('post') : $this->_registry->registry('wordpress_post');
	}

	protected function _getPostCollection()
	{
		if(is_null($this->_collection)) {
			$collection = $this->getRelatedPostCollection($this->getPost())
						->setPageSize($this->getNumber())
						->setCurPage(1);

			$this->_collection = $collection;
		}
		
		return $this->_collection;
	}
	
	public function getRelatedPostCollection(\FishPig\WordPress\Model\Post $post)
	{
		$relatedPostIds = $this->getRelatedPostIds($post);
		$post_collection_t = $this->_factory->create('Post')->getCollection()
			->addIsViewableFilter()
			->addFieldToFilter('ID', array('in' => $relatedPostIds));
		$post_collection_t->getSelect()->order(new \Zend_Db_Expr('FIELD(ID, ' . implode(',', $relatedPostIds).')'));
		return $post_collection_t;
	}

	public function getRelatedPostIds(\FishPig\WordPress\Model\Post $post)
	{
		if (!$this->isPluginEnabled()) {
			return array();
		}
		
		$related_posts_a = unserialize($post->getData('related'));		
		return $related_posts_a;
		
	}

}
