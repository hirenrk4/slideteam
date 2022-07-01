<?php
Namespace FishPig\WordPress\Block\Sidebar\Widget;
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
class Subscribe extends AbstractWidget
{
	/**
	 * Retrieve the action URL for the search form
	 *
	 * @return string
	 */
    protected  $router;


    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context, 
            \FishPig\WordPress\Model\Context $wpContext, 
            \FishPig\WordPress\Model\Url $router,
            array $data = array()) {
        parent::__construct($context, $wpContext, $data);
        $this->router = $router;
    }

	public function getFormActionUrl()
	{
        $baseRoute=$this->router->getBlogRoute().'/';
        $subscribePath=$this->router->getSubscribeRoute(). '/';
        return $baseRoute.$subscribePath;
	}

	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
    {
        return __('Subscribe to Blog');
    }
    

	/**
	 * Retrieve the search term used
	 *
	 * @return string
	 */
	public function getSearchTerm()
	{
		//return $this->helper('wordpress/router')->getSearchTerm();
	}

	/**
	 * Ensure template is set
	 *
	 * @return string
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/subscribe.phtml');
		}

		return parent::_beforeToHtml();
	}
}
