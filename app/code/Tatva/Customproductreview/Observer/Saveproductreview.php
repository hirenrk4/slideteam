<?php

namespace Tatva\Customproductreview\Observer;

use Magento\Review\Model\Review as ReviewModel;

class Saveproductreview implements \Magento\Framework\Event\ObserverInterface
{
	
    protected $csvProcessor;

    protected $reviewFactory;

    protected $ratingFactory;

	public function __construct(
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Tatva\Customproductreview\Model\ResourceModel\Customproductreview\CollectionFactory $customproductreviewCollectionFactory
	) {
        $this->productModel = $productModel;
        $this->storeManager = $storeManager;
        $this->csvProcessor = $csvProcessor;
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->customproductreviewCollectionFactory = $customproductreviewCollectionFactory;
	}

 
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        if ($products = $observer->getEvent()->getBunch()) {

            //Product Review
            $importClientNames = $this->csvProcessor->getData(BP . '/pub/clientnames.csv');
            $productreviewCollection = $this->customproductreviewCollectionFactory->create();
            $importComments = $productreviewCollection->getData();

            foreach ($products as $product) {
                $perproductcnt = mt_rand(1,5);
                while($perproductcnt != 0)
                {
                    $product['sku']= substr($product['sku'], 0, 64);

                    $productId = $this->productModel->getIdBySku($product['sku']);
                    $storeCode = $product['store_view_code'];
                    $storeId = $this->storeManager->getStore($storeCode)->getStoreId();

                    $rateoption = array(5,5,4,4,5,4,5,4,4,5);
                    $rate = $rateoption[array_rand($rateoption,1)];
                    $data = array();


                    $data['detail'] = $importComments[mt_rand(1,count($importComments)-1)]['review_detail'];
                    $data['productid'] = $productId;
                    $data['rating'][4] = 15 + $rate;

                    $review = $this->reviewFactory->create()->setData($data);

                    $namearr = $importClientNames[mt_rand(1, count($importClientNames) - 1)];
                    $review->setData('nickname',$namearr[0].' '.$namearr[1]);
                    $review->setData('title',$data['detail']);

                    $validate = $review->validate();
                    if($validate == true)
                    {
                        try {
                            $review->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
                                ->setEntityPkValue($data['productid'])
                                ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
                                ->setStoreId($storeId)
                                ->setStores($storeId)
                                ->save();         
                            foreach ($data['rating'] as $ratingId => $optionId) {
                                $this->ratingFactory->create()
                                    ->setRatingId($ratingId)
                                    ->setReviewId($review->getId())
                                    ->setCustomerId('')
                                    ->addOptionVote($optionId,$data['productid']);
                            }        
                            $review->aggregate();       
                        } catch (Exception $e) {
                            echo $e->getMessage();
                        }
                    }
                    $perproductcnt -= 1;
                }
            }
        }				
	}				
}
