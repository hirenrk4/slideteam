<?php
namespace Tatva\Theme\Block\Html;

use Magento\Framework\View\Element\Template\Context;

class HomePage extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;

     /**
     * @param Context $context
     * @param array $data
     */
    public function __construct
    (
    	Context $context,
    	\Magento\Cms\Model\Page $page,
    	array $data = []
    )
    {
    	$this->_page = $page;
    	parent::__construct($context, $data);
    }

    public function getTitle()
    {
        return $this->_page->getTitle();
    }

    public function getMetaDescription()
    {
        return $this->_page->getMetaDescription();
    }
            
}