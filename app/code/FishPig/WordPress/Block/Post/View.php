<?php
/**
 *
**/

namespace FishPig\WordPress\Block\Post;

class View extends \FishPig\WordPress\Block\Post
{
	protected $_resourceConnection;

	protected $popupBlock;
	
    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context, 
    	\FishPig\WordPress\Model\Context $wpContext, 
    	\FishPig\WordPress\Model\ResourceConnection $resourceConnection,
    	\Tatva\Popup\Block\GeneralPopup $popupBlock,
    	\Tatva\Customer\Helper\Popup $popupHelper,
    	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    	array $data = array()
    ) {
        parent::__construct($context, $wpContext, $data);
        $this->_resourceConnection = $resourceConnection;
        $this->popupBlock = $popupBlock;
        $this->popupHelper = $popupHelper;
        $this->_scopeConfig = $scopeConfig;
    }

    public function configData($path){
        return $this->_scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
  	}

	protected function _prepareLayout()
	{
		if ($this->getPost()) {
			$this->getPost()->applyPageConfigData($this->pageConfig);
		}
        
		return parent::_prepareLayout();
	}

	protected function _beforeToHtml()
	{
		if (!$this->getTemplate() && $this->getPost()) {
			$postType = $this->getPost()->getTypeInstance();
			$this->setTemplate('FishPig_WordPress::post/view.phtml');

			if ($postType->getPostType() !== 'post') {
				$postTypeTemplate = 'FishPig_WordPress::' . $postType->getPostType() . '/view.phtml';

				if ($this->getTemplateFile($postTypeTemplate)) {
					$this->setTemplate($postTypeTemplate);
				}
			}
		}
		
		return parent::_beforeToHtml();
	}

	public function displayRelatedPosts($currentPostId)
	{ 
		if($this->getPost())
		{
			$post = $this->getPost();
			
			if(!empty(unserialize($post->getData('related'))))
			{
				echo $this->getChildHtml('related_posts');
			}
		}        
	}

	public function getKeywords($currentPostId)
	{
		if($this->getPost())
		{
			$post = $this->getPost();
			
			if(!empty($post->getData('keywords')))
			{
				return $post->getData('keywords');
			}
		}    
	}

	public function getPopupData(){
		return $this->popupBlock;
	}

	public function checkCustomerType() {
		return $this->popupHelper->customerType();
	}
}
