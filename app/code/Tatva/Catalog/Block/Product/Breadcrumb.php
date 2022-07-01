<?php
namespace Tatva\Catalog\Block\Product;

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
    	$url = $this->_redirect->getRefererUrl();
    	/*$refererPage = substr($url, strpos($url, '/',10));*/
    	$refererUrl = array('/professional-powerpoint-templates/','/share-and-download-products/','/new-powerpoint-templates/','/free-business-powerpoint-templates/');
    	$evercrumbs = array();

    	$evercrumbs[] = array(
    		'label' => 'Home',
    		'title' => 'Go to Home Page',
    		'link' => $this->_storeManager->getStore()->getBaseUrl()
    	);

    	$breadcrumbCategories = $this->_catalogData->getBreadcrumbPath();
        $product = $this->registry->registry('current_product');
        
        if( (strpos($url, 'professional-powerpoint-templates') || strpos($url, 'share-and-download-products') || strpos($url, 'new-powerpoint-templates')) || strpos($url, 'free-business-powerpoint-templates')=== false)
        {
            foreach($breadcrumbCategories as $breadcrumb)
            {
                $evercrumbs[] = array(
                    'label' => $breadcrumb['label'],
                    'title' => $breadcrumb['label'],
                    'link' => isset($breadcrumb['link']) ? $breadcrumb['link'] : NULL
                );    
            }
        }
        else
        {
            $evercrumbs[] = array(
                'label' => $product->getName(),
                'title' => $product->getName(),
                'link' => ''
            );    
        }

    	// $product = $this->registry->registry('current_product');
    	// $categoryCollection = clone $product->getCategoryCollection();
    	// $categoryCollection->clear();
    	// $categoryCollection->addAttributeToSort('level', $categoryCollection::SORT_ORDER_DESC)->addAttributeToFilter('path', array('like' => "1/" . $this->_storeManager->getStore()->getRootCategoryId() . "/%"));
    	// $categoryCollection->setPageSize(1);
    	// $breadcrumbCategories = $categoryCollection->getFirstItem()->getParentCategories();
    	
    	// if( (strpos($url, 'professional-powerpoint-templates') || strpos($url, 'share-and-download-products') || strpos($url, 'new-powerpoint-templates')) || strpos($url, 'free-business-powerpoint-templates')=== false)
    	// {
    	// 	foreach ($breadcrumbCategories as $category) 
    	// 	{
    	// 		$evercrumbs[] = array(
    	// 			'label' => $category->getName(),
    	// 			'title' => $category->getName(),
    	// 			'link' => $category->getUrl()
    	// 		);
    	// 	}
    	// }

    	// $evercrumbs[] = array(
    	// 	'label' => $product->getName(),
    	// 	'title' => $product->getName(),
    	// 	'link' => ''
    	// );

    	return $evercrumbs;
    }
}