<?php

namespace Tatva\Downloadable\Cron;
use Magento\Review\Model\Review as ReviewModel;

class Review
{
   const ENTITY_PRODUCT_CODE = 'product';
   protected $reviewFactory;
   protected $ratingFactory;
   protected $csvProcessor;
   protected $productRepository;
   protected $filesystem;
   protected $scopeConfig;
   protected $resourceConfig;
   protected $cacheTypeList;
   protected $productCollectionFactory;
   public function __construct(\Magento\Review\Model\ReviewFactory $reviewFactory,
                              \Magento\Review\Model\RatingFactory $ratingFactory,
                              \Magento\Framework\File\Csv $csvProcessor,
                              \Magento\Catalog\Model\Product $productRepository,
                              \Magento\Framework\Filesystem $filesystem,
                              \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                              \Magento\Config\Model\ResourceModel\Config $resourceConfig,
                              \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
                              \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                              \Tatva\Customproductreview\Model\ResourceModel\Customproductreview\CollectionFactory $customproductreviewCollectionFactory
                           ){
      $this->reviewFactory = $reviewFactory;
      $this->ratingFactory = $ratingFactory;
      $this->csvProcessor = $csvProcessor;
      $this->productRepository = $productRepository;
      $this->filesystem = $filesystem;
      $this->scopeConfig = $scopeConfig;
      $this->resourceConfig = $resourceConfig;
      $this->cacheTypeList = $cacheTypeList;
      $this->productCollectionFactory = $productCollectionFactory;
      $this->customproductreviewCollectionFactory = $customproductreviewCollectionFactory;
   }

	public function execute()
	{
     
      
      $createdat = $lastdat= null;
      $perproductcnt = 0;
      $countsarray = array(1500,2000);
      $counts = $countsarray[array_rand($countsarray, 1)];
      $productid = '';
      $importClientNames = $this->csvProcessor->getData(BP . '/pub/clientnames.csv');
      $productreviewCollection = $this->customproductreviewCollectionFactory->create();
      $importComments = $productreviewCollection->getData();
      $productid = $this->scopeConfig->getValue('button/review/next_review_product');
      $productCollection = $this->productCollectionFactory->create()->addAttributeToFilter('entity_id', array('gt'=>$productid))->addFilter('type_id','downloadable')->setPageSize($counts);
      
      foreach($productCollection as $prod){
         $perproductcnt = 4 ;
         while ($perproductcnt != 0 ){
            $createdat = date("Y-m-d H:i:s", mt_rand(($lastdat ? strtotime($lastdat) : strtotime(date('Y-m-d H:i:s', strtotime("-2 days")))), strtotime(date("Y-m-d H:i:s"))));
            $rateoption = array(5,5,4,4,5,4,5,4,4,5);
            $rate = $rateoption[array_rand($rateoption,1)];
            $data = array('rating' => array(4 => ''), 'detail' => '', 'productid' => '', 'created_at' => $createdat );
            $lastdat = $createdat;

            $data['detail'] = $importComments[mt_rand(1,count($importComments)-1)]['review_detail'];
            $data['productid'] = $prod->getId();
            $data['rating'][4]= 15 + $rate;

            $review = $this->reviewFactory->create()->setData($data);
            $review->unsetData('review_id');
            $namearr = $importClientNames[mt_rand(1, count($importClientNames) - 1)];
            $review->setData('nickname', $namearr[0] .' '. $namearr[1] );
            $review->setData('title', $data['detail']);
            //$review->setCreatedAt($data['created_at']);
            $validate = $review->validate();
            
            if ($validate === true) {
               try {
                  $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                     ->setEntityPkValue($data['productid'])
                     ->setStatusId(ReviewModel::STATUS_APPROVED)
                     // ->setCustomerId($this->customerSession->getCustomerId())
                     ->setStoreId($perproductcnt % 2 == 0 ? 1:2)
                     ->setStores([$perproductcnt % 2 == 0 ? 1:2])
                     ->save();
                  foreach ($data['rating'] as $ratingId => $optionId) {
                     $this->ratingFactory->create()
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId('')
                        ->addOptionVote($optionId, $data['productid']);
                  }
                  $review->aggregate();
               }
               catch (\Exception $e) {
                  
               }
            }
            $lastdat =null;
            $perproductcnt -= 1;
         }
         $productid=$prod->getId();
      }
      
      try {
         $this->resourceConfig->saveConfig('button/review/next_review_product',$productid);
         $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
      } catch (\Exception $e) {
         
      }
   }
}
