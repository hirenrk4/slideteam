<?php
namespace Tatva\Downloadscount\Block;
use Tatva\Catalog\Model\Productdownloadhistorylog;

class Downloadscount extends \Magento\Framework\View\Element\Template {

    /**
     * @var \Tatva\Subscription\Model\Mysql4\Productdownloadhistorylog\CollectionFactory
     */
    //protected $subscriptionMysql4ProductdownloadhistorylogCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    protected $pageConfig;
    protected $downloadItemCollection;
    protected $productIDs;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Productdownloadhistorylog $model,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
       // \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\Http $request,
        array $data = [])
    {
       parent::__construct($context,$data);
       $this->model = $model;
       $this->_productFactory = $productFactory;
       $this->productCollection = $productCollection;
       $this->pageConfig = $pageConfig;
       $this->customerSession = $customerSession;
       $this->_request = $request;

   }

   public function _prepareLayout() {
    $this->pageConfig->getTitle()->set(__('My Downloads'));
    $pager = $this->getLayout()->createBlock(
        '\Tatva\Downloadscount\Block\Pager',
        'downloadscount.count.pager');
    $pager->setAvailableLimit(array(5=>5,10=>10,15=>15,20=>20)); 
    $customerId = $this->customerSession->getCustomerId();
    $collection=$this->getCustomCollection($customerId);
    $pager->setCollection($collection);
    $this->setChild('pager', $pager);
    return $this;
   }
	public function getCustomCollection($customerId){
		$this->downloadItemCollection = $this->model->getCollection()
	                                    ->addFieldToFilter('customer_id',$customerId)
	                                    ->addFieldToSelect('product_id');
	    $this->downloadItemCollection->getSelect()->columns('COUNT(product_id) AS no_of_download');
	    $this->downloadItemCollection->getSelect()->columns('MAX(download_date) AS recent_download');
	    $this->downloadItemCollection->getSelect()->group('product_id');
	    $this->downloadItemCollection->setOrder('recent_download','DESC');
	    return $this->downloadItemCollection;

	}
public function getProductData($id)
{
		 $customerId = $this->customerSession->getCustomerId();

		 $productData=$this->getCustomCollection($customerId)->getData();
		 		
		 foreach($productData as $key=>$value)
		 {
		 	$productsData[]= $value['product_id'];
		 }

		$collection = $this->productCollection->create()
						->addFieldToFilter('entity_id',array("in"=>$productsData))
						->addAttributeToSelect(array('name','free','url_path'));
     
     foreach ($collection as $product) {
         $dataArray['id'][]=$product->getEntityId();
         $dataArray[$product->getEntityId()]['name']=$product->getName();
         $dataArray[$product->getEntityId()]['url']= $product->getProductUrl();
         $dataArray[$product->getEntityId()]['free']= $product->getFree();

         }      
        
		   if(!in_array($id,$dataArray['id']))
			{
				$dataArray[$id]['name']='';
				$dataArray[$id]['url']='';
				$dataArray[$id]['free']='';
			}          
    return $dataArray[$id];
}

public function getPagerHtml() {
    return $this->getChildHtml('pager');
}

public function getDownloadlist(){
    return $this->downloadItemCollection;    
}
public function getRedirectLoginUrl(){
    $this->_request->setRedirect($this->getBaseUrl()."customer/account/login");    
}
}
