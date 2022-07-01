<?php
namespace Tatva\Resume\Block;

use Magento\Catalog\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;
use Magento\Framework\Registry;
use Magento\Framework\App\Response\RedirectInterface;

class Breadcrumb extends \Magento\Framework\View\Element\Template
{

    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogData = null;
    protected $_redirect;

    /**
     * @param Context $context
     * @param Data $catalogData
     * @param array $data
     */
    public function __construct
    (
    	RedirectInterface $redirectInterface,
    	Context $context, 
    	Data $catalogData, 
    	Registry $registry,
    	array $data = []
    )
    {
    	$this->_redirect = $redirectInterface;
    	$this->_catalogData = $catalogData;	
    	$this->registry = $registry;
    	parent::__construct($context, $data);
    }

    public function getCrumbs()
    {
    	$evercrumbs = array();

    	$evercrumbs[] = array(
    		'label' => 'Home',
    		'title' => 'Go to Home Page',
    		'link' => $this->_storeManager->getStore()->getBaseUrl()
    	);

    	
        $product = $this->registry->registry('current_product');
        
        $evercrumbs[] = array(
            'label' => "Resume",
            'title' => "Resume",
            'link' => $this->_storeManager->getStore()->getBaseUrl()."resume-template/"
        );    
           
        $evercrumbs[] = array(
            'label' => $product->getName(),
            'title' => $product->getName(),
            'link' => ''
        );
    	return $evercrumbs;
    }
}