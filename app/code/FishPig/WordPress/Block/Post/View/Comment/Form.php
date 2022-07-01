<?php
/*
 *
 */
namespace FishPig\WordPress\Block\Post\View\Comment;

/* Parent Class */
use FishPig\WordPress\Block\AbstractBlock;

class Form extends AbstractBlock
{
	/**
	 * Ensure a valid template is set
	 *
	 * @return $this
	 */
         
    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context, 
    	\FishPig\WordPress\Model\Context $wpContext, 
    	\Magento\Framework\UrlInterface $urlInterface,
    	\FishPig\WordPress\Model\Url $wpUrlBuilder,
    	array $data = array()) 
    {
    	parent::__construct($context, $wpContext, $data);
    	$this->_urlInterface = $urlInterface;
    	$this->wpUrlBuilder = $wpUrlBuilder;
    }
	
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('post/view/comment/form.phtml');
		}
		
		return parent::_beforeToHtml();		
	}

	/**
	 * Retrieve the comment form action
	 *
	 * @return string
	 */
	public function getCommentFormAction()
	{
		return $this->wpUrlBuilder->getUrl('upgrade/captcha/postcomment');
	}

	/**
	 * Determine whether the customer needs to login before commenting
	 *
	 * @return bool
	 */
	public function customerMustLogin()
	{
		if ($this->optionManager->getOption('comment_registration')) {
			return !$this->wpContext->getCustomerSession()->isLoggedIn();
		}
		
		return false;
	}

	/**
	 * Retrieve the link used to log the user in
	 * If redirect to dashboard after login is disabled, the user will be redirected back to the blog post
	 *
	 * @return string
	 */
	public function getLoginLink()
	{
		$ref = $this->getPost() ? base64_encode($this->getPost()->getPermalink() . '#respond') : '';
		
		return $this->getUrl('customer/account/login', ['referer' => $ref]);
	}

	/**
	 * Returns true if the user is logged in
	 *
	 * @return bool
	 */
	public function isCustomerLoggedIn()
	{
		return $this->wpContext->getCustomerSession()->isLoggedIn();
	}
	
	/**
	 * Retrieve the current post object
	 *
	 * @return null|\FishPig\WordPress\Model\Post
	 */
	public function getPost()
	{
		return $this->hasPost() ? $this->_getData('post') : $this->registry->registry('wordpress_post');
	}
	
	/**
	 * Returns the ID of the currently loaded post
	 *
	 * @return int|false
	 */
	public function getPostId()
	{
		return $this->getPost() ? $this->getPost()->getId() : false;
	}

	public function getCaptchValidationAction(){

		return $this->_urlInterface->getUrl('wordpress/index/index');
	}
}
