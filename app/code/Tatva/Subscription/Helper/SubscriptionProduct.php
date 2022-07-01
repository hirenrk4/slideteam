<?php
namespace Tatva\Subscription\Helper;

use \Magento\Framework\App\Helper\Context;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\State;
use \Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Exception\LogicException;

class SubscriptionProduct extends \Magento\Framework\App\Helper\AbstractHelper
{
	const ARG_COUNT = 'count';
	const ARG_WEBSITE = 'website';

	protected $storeManager;
	protected $state;
	protected $ProductFactory;
	protected $data;
	protected $subscriptionProductsSkus;
	protected $subscriptionProducts;
	protected $productFactory;
	protected $productRepository;

	public function __construct(
		Context $context,
		StoreManagerInterface $storeManager,
		State $state,
		\Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
  		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository
	) {
		$this->storeManager = $storeManager;
		$this->state = $state;
		$this->productFactory = $productFactory;
		$this->productRepository = $productRepository;
		$this->setSubscriptionProducts();
		parent::__construct($context);
	}


	protected function setSubscriptionProducts()
	{
		$this->subscriptionProductsSkus = ["st_monthly_sub","st_semianual_sub","st_anual_sub","st_anaul_w_cd_sub","st_team_sub"];
		
		// Monthly Subscription
		$this->subscriptionProducts['st_monthly_sub'] = [	
											"sku" => "st_monthly_sub" , 
											"name" => "Monthly",
											"price" => "49.99",
											"subscription_period" => "1 month",
											"download_limit" => "20",
											"recurring_payment" => ["period_unit" => "month", "period_frequency"=>"1"]
										];
		
		// Semiannual Subscription
		$this->subscriptionProducts['st_semianual_sub'] = [	
											"sku" => "st_semianual_sub",
											"name" => "Semi Annual",
											"price" => "149.99",
											"cost" => "299.94",
											"subscription_period" => "6 month",
											"download_limit" => "-1",
											"recurring_payment" => ["period_unit" => "month", "period_frequency"=>"6"]	
										];

		// Annual Subscription 
		$this->subscriptionProducts['st_anual_sub'] = [	
											"sku" => "st_anual_sub",
											"name" => "Annual",
											"price" => "249.99",
											"cost" => "599.88",
											"subscription_period" => "12 month",
											"download_limit" => "-1",
											"recurring_payment" => ["period_unit" => "month", "period_frequency"=>"12"]
										];

		// Annual with custom design
		$this->subscriptionProducts['st_anaul_w_cd_sub'] = [	
											"sku" => "st_anaul_w_cd_sub",
											"name" => "Annual + Custom Design",
											"price" => "299.99",
											"cost" => "849.78",
											"subscription_period" => "12 month ( Annual + CustomDesign )",
											"download_limit" => "-1",
											"recurring_payment" => ["period_unit" => "month", "period_frequency"=>"12"]
										];

		// Team Subscription
		$this->subscriptionProducts['st_team_sub'] = [	
											"sku" => "st_team_sub",
											"name" => "Team License",
											"price" => "599.99",
											"cost" => "2774.37",
											"subscription_period" => "12 month ( Enterprise )",
											"download_limit" => "-1",
											"recurring_payment" => ["period_unit" => "month", "period_frequency"=>"12"]
										];
		return $this;

	}

	public function createSubscriptionProduct($count){
		$website = $this->data->getOption(self::ARG_WEBSITE)?$this->data->getOption(self::ARG_WEBSITE):1;
		$productSKU = $this->subscriptionProductsSkus[$count];
		
		$isProductExist = $this->checkIsSubExist($productSKU);

		try {
			
			if(!$isProductExist){
				$product = $this->productFactory->create();
				$productData = $this->subscriptionProducts[$productSKU];
				$product = $this->setProductData($product);				
				$product->save();				 
				return $productSKU; 
			}
			else{
				$product = $this->setProductData($isProductExist);
				$product->save();
				return $productSKU; 
				// throw new LogicException('Product having SKU :'.$productSKU.' is already exists');
			}			
		} catch (LogicException $e) {
			
		} catch (Exception $e){
		    
		}
		
	}

	protected function setProductData($product)
	{
		$productData = $this->subscriptionProducts[$productSKU];
		$product->setSku($productSKU); 
		$product->setName($productData['name']);
		$product->setDescription($productData['name']);
		$product->setWebsiteIds(array(1)); // website ID
		$categories = ["1826"];
		$product->setCategoryIds($categories); 
		$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL);
		$product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE);
		$product->setPrice($productData['price']); 
		$product->setTaxClassId(0); 
		$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED); 
		$product->setAttributeSetId(9);
		// subscription_period
		// download_limit
		// Meta information need ask sir if needed
		// enable recurring = 1
		// customer_can_define_start_date = 0
		// maximum_payment_failures = 0
		// auto_bill_next_style = 0
		// billing_period_unit = month
		// billing_frequency = 1
		
		$product->setStockData(
		 	array(
		 		'use_config_manage_stock' => 1,
		 		'manage_stock' => 0
 				)
 			);
		return $product;
	}

	protected function checkIsSubExist($productSKU)
	{
		try {
		    $product = $this->productRepository->get($productSKU);
		} catch (\Magento\Framework\Exception\NoSuchEntityException $e){
		    $product = false;
		}
		return $product;
	}

	public function execute() {
		$this->state->setAreaCode('frontend');

		$count = count($this->subscriptionProductsSkus);
		
		for($i =0;$i<=$count;$i++){
			$productSKU = $this->createSubscriptionProduct($i); 
			$i++;
			echo $i."- Product is created - SKU : ".$productSKU."\n";

		}
	}

}