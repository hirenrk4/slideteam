<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Post;

use \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper;
use \FishPig\WordPress\Model\ResourceModel\Post\Collection as PostCollection;

class ListPost extends \FishPig\WordPress\Block\Post
{
	/**
	 * Cache for post collection
	 *
	 * @var PostCollection
	 */
	protected $_postCollection = null;

	protected $popupBlock;

	protected $popupHelper;


    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context,
    	\FishPig\WordPress\Model\Context $wpContext,
    	\Tatva\Popup\Block\GeneralPopup $popupBlock,
    	\Tatva\Customer\Helper\Popup $popupHelper,
    	\FishPig\WordPress\Model\ResourceModel\Post\Collection $postCollection,
    	\FishPig\WordPress\Model\ResourceModel\Term\Collection $termCollection,
    	\FishPig\WordPress\Block\Post $postBlock,
    	array $data = array()) {
        parent::__construct($context, $wpContext, $data);
        $this->popupBlock = $popupBlock;
        $this->popupHelper = $popupHelper;
        $this->postcollection = $postCollection;
        $this->termCollection = $termCollection;
        $this->postBlock = $postBlock;
    }
        /*
	 * Returns the collection of posts
	 *
	 * @return 
	 */
	public function getPosts()
	{
		if ($this->_postCollection === null) {
			if ($this->getWrapperBlock()) {
				if ($this->_postCollection = $this->getWrapperBlock()->getPostCollection()) {
					if ($this->getPostType()) {
						$this->_postCollection->addPostTypeFilter($this->getPostType());
					}
				}
			}
			else {

				$this->_postCollection = $this->postcollection;
			}
			

			if ($this->_postCollection && ($pager = $this->getChildBlock('pager'))) {
				$pager->setPostListBlock($this)->setCollection($this->_postCollection);
			}
		}
		$params = $this->getRequest()->getParams();
		
		if(isset($params['sort']) && $params['sort'] == "popular")
		{
			$this->_postCollection->getSelect()->order("total_visits DESC");	
		}
		elseif(isset($params['sort']) && $params['sort'] == "comment")
		{
			$this->_postCollection->getSelect()->joinLeft(
				"wp_comments","comment_post_ID=main_table.ID",array("count(*) as count_comment"));
			$this->_postCollection->getSelect()->where("comment_approved = 1");
			$this->_postCollection->getSelect()->order("count_comment DESC");
			$this->_postCollection->getSelect()->group("main_table.ID");
		}
		elseif(isset($params['sort']) && $params['sort'] == 'newly')
		{
			$this->_postCollection->getSelect()->order("post_date_gmt DESC");	
		}
		else
		{
			$this->_postCollection->getSelect()->order("total_visits DESC");
		}

		if (isset($params['lang']))
		{
			$params['lang'] = strtolower($params['lang']);
			if(($params['lang'] == 'spanish' || $params['lang'] == 'german' || $params['lang'] == 'french' || $params['lang'] == 'portuguese')){
				$category_id = $this->getCategoryIdfromName($params['lang']);
				$this->_postCollection->addCategoryIdFilter($category_id);
			}
		}
		else
		{
			$excludeArray = $this->getCustomCategoryIdfromName(array("Spanish","German","French","Portuguese"));

			$excludeCategory = array();
			$excludeCollection = $this->postcollection->addCategoryIdFilter($excludeArray);

			if(!empty($excludeCollection->getSize()))
			{
				$excludeCollection->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns(['ID']);
			}			
			
			if(!empty($excludeCollection->getSize()))
			{
				$this->_postCollection->getSelect()->where('ID NOT IN ('.new \Zend_Db_Expr($excludeCollection->getSelect()).')' );
			}
		}
		
		$this->_postCollection->getSelect()->where('post_title !=""');
		//echo $this->_postCollection->getSelect();die;
		return $this->_postCollection;
	}
	
	/*
	 * Sets the parent block of this block
	 * This block can be used to auto generate the post list
	 *
	 * @param AbstractWrapper $wrapper
	 * @return $this
	 */
	public function setWrapperBlock(AbstractWrapper $wrapper)
	{
		return $this->setData('wrapper_block', $wrapper);
	}
	
	/**
	 * Get the HTML for the pager block
	 *
	 * @return string
	 */
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}
	
	/*
	 * Retrieve the correct renderer and template for $post
	 *
	 * @param \FishPig\WordPress\Model\Post $post
	 * @return FishPig\WordPress\Block_Post_List_Renderer
	 */
	public function renderPost(\FishPig\WordPress\Model\Post $post)
	{
		// Create post block
		$postBlock = $this->postBlock->setPost($post);
		
		$vendors = [
  		$this->getCustomBlogThemeVendor(),
  		'FishPig_WordPress',
		];

		// First try post type specific template then fall back to default
		$templates = [
  		'post/list/renderer/' . $post->getPostType() . '.phtml',
  		'post/list/renderer/default.phtml',
		];
		
		$templatesToTry = [];
		
	    foreach($templates as $template) {
	  		foreach($vendors as $vendor) {
	    		if ($vendor) {
	          $templatesToTry[] = $vendor . '::' . $template;
	        }
	      }
	    }
    
	    if ($rendererTemplate = $this->getData('renderer_template')) {
	      array_unshift($templatesToTry, $rendererTemplate);
	    }

		foreach($templatesToTry as $templateToTry) {
			if ($this->getTemplateFile($templateToTry)) {
				$postBlock->setTemplate($templateToTry);
				break;
			}
		}

		// Get HTML and return
		return $postBlock->toHtml();
	}


	public function renderResumePost(\FishPig\WordPress\Model\Post $post)
	{
		// Create post block
		$postBlock = $this->postBlock->setPost($post);
		
		$vendors = [
  		$this->getCustomBlogThemeVendor(),
  		'FishPig_WordPress',
		];

		// First try post type specific template then fall back to default
		$templates = [
  		'post/list/renderer/' . $post->getPostType() . '.phtml',
  		'post/list/renderer/resume.phtml',
		];
		
		$templatesToTry = [];
		
	    foreach($templates as $template) {
	  		foreach($vendors as $vendor) {
	    		if ($vendor) {
	          $templatesToTry[] = $vendor . '::' . $template;
	        }
	      }
	    }
	    
	    if ($rendererTemplate = $this->getData('renderer_template')) {
	      array_unshift($templatesToTry, $rendererTemplate);
	    }

		foreach($templatesToTry as $templateToTry) {
			if ($this->getTemplateFile($templateToTry)) {
				$postBlock->setTemplate($templateToTry);
				break;
			}
		}

		// Get HTML and return
		return $postBlock->toHtml();
	}
	
	/*
   *
   *
   */
  public function getCustomBlogThemeVendor()
  {
    return false; 
  }

	/*
	 *
	 *
	 *
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('FishPig_WordPress::post/list.phtml');
    }
		
		return parent::_beforeToHtml();
	}

	public function renderBlogOnePost(\FishPig\WordPress\Model\Post $post,$type)
	{
		// Create post block
		$postBlock = $this->postBlock->setPost($post);
		
		$vendors = [
  		$this->getCustomBlogThemeVendor(),
  		'FishPig_WordPress',
		];

		// First try post type specific template then fall back to default

		if($type == "brochure")
		{
			$templates = [
	  		'post/list/renderer/' . $post->getPostType() . '.phtml',
	  		'post/list/renderer/brochure-blog.phtml',
			];
		}
		elseif($type == "resume")
		{
			$templates = [
	  		'post/list/renderer/' . $post->getPostType() . '.phtml',
	  		'post/list/renderer/resume-blog.phtml',
			];
		}
		elseif($type == "onepage")
		{
			$templates = [
	  		'post/list/renderer/' . $post->getPostType() . '.phtml',
	  		'post/list/renderer/onepage-blog.phtml',
			];
		}
		elseif($type == 'documentreport')
		{
			$templates = [
	  		'post/list/renderer/' . $post->getPostType() . '.phtml',
	  		'post/list/renderer/documentreport-blog.phtml',
			];	
		}
		elseif($type == 'letterhead')
		{
			$templates = [
	  		'post/list/renderer/' . $post->getPostType() . '.phtml',
	  		'post/list/renderer/letterhead-blog.phtml',
			];	
		}
		
		
		$templatesToTry = [];
		
	    foreach($templates as $template) {
	  		foreach($vendors as $vendor) {
	    		if ($vendor) {
	          $templatesToTry[] = $vendor . '::' . $template;
	        }
	      }
	    }
	    
	    if ($rendererTemplate = $this->getData('renderer_template')) {
	      array_unshift($templatesToTry, $rendererTemplate);
	    }

		foreach($templatesToTry as $templateToTry) {
			if ($this->getTemplateFile($templateToTry)) {
				$postBlock->setTemplate($templateToTry);
				break;
			}
		}

		// Get HTML and return
		return $postBlock->toHtml();
	}
	public function getPopupData(){
		return $this->popupBlock;
	}

	public function checkCustomerType() {
		return $this->popupHelper->customerType();
	}

	public function getCategoryIdfromName($name)
	{
		$collection = $this->termCollection;
		$collection->addFieldToFilter('name',$name);
		$category_id = '';
		foreach ($collection as $value) {
			$category_id = $value->getTermId();
		}
		return $category_id;
	}

	public function getCustomCategoryIdfromName($name)
	{
		$collection = $this->termCollection;
		$collection->addFieldToFilter(['name', 'name', 'name','name'],
	    [
	        ['eq' => $name[0]],
	        ['eq' => $name[1]],
	        ['eq' => $name[2]],
	        ['eq' => $name[3]]
	    ]);
		$category_id = array();
		foreach ($collection as $value) {
			$category_id[] = $value->getTermId();
		}
		
		return $category_id;
	}
}
