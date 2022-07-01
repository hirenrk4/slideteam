<?php
namespace Tatva\Couponcode\Controller\Index;

class Coupon extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $scopeConfig;

	public function __construct(
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Tatva\Couponcode\Model\CouponRatingFactory $couponrating,
		\Tatva\Couponcode\Model\ResourceModel\CouponRating\CollectionFactory $couponFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Tatva\Couponcode\Block\Coupon\Lists $couponList,
		\Magento\Framework\App\Action\Context $context
	) {
		$this->resultJsonFactory = $resultJsonFactory;
		$this->couponrating = $couponrating;
		$this->couponFactory = $couponFactory;
		$this->customerSession = $customerSession;
		$this->couponlist = $couponList;
		parent::__construct($context);
	}
	public function execute()
	{
		$customer_id = $this->customerSession->getCustomerId();
		$params = $this->getRequest()->getParams();
		$action = $params['action'];
		$coupon_id = $params['coupon_id'];
		$rating = array();


		if($action == 'like' || $action == 'dislike') {

			$collection = $this->couponFactory->create()
						->addFieldToFilter('customer_id',array('eq'=>$customer_id))
						->addFieldToFilter('coupon_id',array('eq'=>$coupon_id));

			$model = $this->couponrating->create();

			if(!empty($collection->getData()))
			{
				foreach($collection as $coll)
				{
				    $id = $coll->getId();
			    	$model->load($id);
			    	$model->setData('rating_action',$action);
			    	$model->save();
				}	
			}
			else{
				$model->setData('customer_id',$customer_id);
		        $model->setData('coupon_id',$coupon_id);
		        $model->setData('rating_action',$action);
		        $model->save();
			}
			
		
	        $likes = count($this->couponlist->getCouponData($coupon_id,'like'));
	        $dislikes = count($this->couponlist->getCouponData($coupon_id,'dislike'));
			$resultJson = $this->resultJsonFactory->create();
			$response = ['likes' => $likes,'dislikes' => $dislikes];
			$resultJson->setData($response);
			return $resultJson;
		} elseif ($action == 'unlike' || $action == 'undislike') {
			$collection = $this->couponFactory->create()
						->addFieldToFilter('customer_id',array('eq'=>$customer_id))
						->addFieldToFilter('coupon_id',array('eq'=>$coupon_id));
			foreach($collection as $coll)
			{
			    $this->couponrating->create()->load($coll->getId())->delete();
			}

            $likes = count($this->couponlist->getCouponData($coupon_id,'like'));
	        $dislikes = count($this->couponlist->getCouponData($coupon_id,'dislike'));
	        $resultJson = $this->resultJsonFactory->create();
			$response = ['likes' => $likes,'dislikes' => $dislikes];
			$resultJson->setData($response);
			return $resultJson;
		} else {
			$likes = count($this->couponlist->getCouponData($coupon_id,'like'));
	        $dislikes = count($this->couponlist->getCouponData($coupon_id,'dislike'));
	        $resultJson = $this->resultJsonFactory->create();
			$response = ['likes' => $likes,'dislikes' => $dislikes];
			$resultJson->setData($response);
			return $resultJson;
		}
	}

}
