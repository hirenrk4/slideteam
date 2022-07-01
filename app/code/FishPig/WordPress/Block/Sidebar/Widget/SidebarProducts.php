<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;


class SidebarProducts extends \Magento\Framework\View\Element\Template
{
	protected $url;
	protected $_productCollection;
	protected $_blockProduct;

	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $url,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Block\Product\ListProduct $blockProduct,
        array $data = []
    ) {        
        $this->url = $url;
        $this->_productCollection = $productCollection;
        $this->_scopeConfig= $scopeConfig;
        $this->_blockProduct = $blockProduct;
        parent::__construct($context, $data);
    }

    public function getSidebarProducts()
    {
    	$pids = $this->_scopeConfig->getValue("wordpress/setup/product_list_ids");
    	$pids_array = explode(",",$pids);
    	$finalStr="";
    	foreach($pids_array as $key => $value)
    	{
    		$finalStr .= "'$value',";
    	}
    	$finalStr = rtrim($finalStr,",");
    	
    	$pCollection = $this->_productCollection->create()->addFieldToFilter('entity_id',array("in"=>$pids_array));
    	$pCollection->addAttributeToSelect('*');
    	$pCollection->getSelect()->order(new \Zend_Db_Expr('FIELD(e.entity_id, ' . $finalStr.')'));
    	
    	return $pCollection;
    }

    public function getBlockImage($product,$type)
    {
    	return $this->_blockProduct->getImage($product,$type);
    }
}
