<?php
namespace Tatva\Free\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    protected $downloadLinkFactory;

    protected $_productCollectionFactory;

    protected $customerSession;

    protected $productDownloadHistoryLogFactory;

    protected $subscriptionModel;

    protected $productVisibility;
    
    protected $productStatus;

    protected $categoryCollectionFactory;

    public function __construct(
    	\Magento\Catalog\Block\Product\Context $context, 
    	\Magento\Framework\Data\Helper\PostHelper $postDataHelper, 
    	\Magento\Catalog\Model\Layer\Resolver $layerResolver, 
    	CategoryRepositoryInterface $categoryRepository, 
    	\Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Downloadable\Model\ResourceModel\Link\CollectionFactory $downloadLinkFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Tatva\Catalog\Model\ResourceModel\Productdownloadhistorylog\CollectionFactory $productDownloadHistoryLogFactory,
        \Tatva\Subscription\Model\Subscription $subscriptionModel,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
    	array $data = array()
    ) {
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->downloadLinkFactory = $downloadLinkFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->customerSession = $customerSession;
        $this->productDownloadHistoryLogFactory = $productDownloadHistoryLogFactory;
        $this->subscriptionModel = $subscriptionModel;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        parent::__construct(
        	$context, 
        	$postDataHelper, 
        	$layerResolver, 
        	$categoryRepository, 
        	$urlHelper, 
        	$data);
    }

    public function _prepareLayout()
    {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Home'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
        $breadcrumbs->addCrumb(
            'free', 
            ['label'=>'Free Downloads', 
            'title'=>'Free Downloads'
            ]
            );
    }

    public function getCategoryIds() {
        $category = $this->catalogCategoryFactory->create()->getCollection()
                                ->addAttributeToFilter('name',array(array('in' => array('Business Slides','Icons'))));
        foreach ($category as $cat) {
            $cat_List[]=$cat->getId();    
        } 
        return $cat_List;
    }

    public function get_link($id) {
        $links = $this->downloadLinkFactory->create()
                            ->addFieldToFilter('product_id',array('eq'=>$id));
        return $links;
    }

    protected function _getProductCollection()
    {
        $category = $this->categoryCollectionFactory->create();
        $category->addFieldToFilter('name',array(
            array('in' => array('Business Slides')),
        ));
        foreach ($category as $cat) {
            $cat_List[]=$cat->getId();    
        }   

        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
                    ->addCategoriesFilter(['in' => $cat_List])
                    ->addAttributeToSelect("free")
                    ->addAttributeToSelect("url_path")
                    ->addAttributeToFilter("free", array("eq" => 1));
        $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
        $collection->getSelect()->group('entity_id');
        return $collection;
    }

    public function getCategoryProductCollection() 
    {
        $category = $this->categoryCollectionFactory->create();
        $category->addFieldToFilter('name',array(
            array('in' => array('Icons')),
        ));
        foreach ($category as $cat) {
            $cat_List[]=$cat->getId();    
        }   

        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*')
                    ->addCategoriesFilter(['in' => $cat_List])
                    ->addAttributeToSelect("free")
                    ->addAttributeToSelect("url_path")
                    ->addAttributeToFilter("free", array("eq" => 1));
        $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
        $collection->getSelect()->group('entity_id');
        return $collection;
    }

    public function isLoggedIn()
    {
        $flag = 0;
        if($this->customerSession->isLoggedIn()){
            $flag = 1;
        }
        return $flag;
    }

    public function getCustomerId() {
        return $this->customerSession->getCustomer()->getId();
    }

    public function getProductDownloadcount($customer_id,$id) {
        $collection = $this->productDownloadHistoryLogFactory->create();
        $product_download = $collection->addFieldToFilter('customer_id',array('eq'=>$customer_id))
                                        ->addFieldToFilter('product_id',array('eq'=>$id))
                                        ->addfieldtofilter('download_date',array('gteq' => '2019-01-07 00:00:00'));
        $product_download->getSelect()->group('product_id');
        $f = 0;
        $count = $product_download->count();
        if($count >= 1)
        {
            return 2;
        }
        $download_collection = $this->productDownloadHistoryLogFactory->create();
        $download_customer = $download_collection->addFieldToFilter('customer_id',array('eq'=>$customer_id))
                                        ->addfieldtofilter('download_date',array('gteq' => '2019-01-07 00:00:00'));
        $download_customer->getSelect()->group('product_id');

        $product_id=array();
        foreach ($download_customer as $value) {
            if(isset($product_id[1])) {
                $f=1;
                break;
            }
            if(!in_array($value->getProductId(),$product_id)){
                $product_id[]=$value->getProductId();   
            }
        }
        return $f;
    }

    public function getSubscriptionModel() {
        return $this->subscriptionModel;
    }
}