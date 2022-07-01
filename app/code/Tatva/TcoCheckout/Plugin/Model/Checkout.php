<?php

namespace Tatva\TcoCheckout\Plugin\Model;

use Tco\Checkout\Model\Checkout as CheckoutModel;

/**
 * Plugin which add recurring params in buildCheckoutRequest before redirected to 2co website
 */
class Checkout
{
	
	public function afterBuildCheckoutRequest(CheckoutModel $checkoutModel,$result,$quote){
		$recurring_params = [];
		$recurring_params = $this->getRecurringParams($quote);
		if(is_array($result)){
			$result = array_merge($result,$recurring_params);
		}
	
		return $result;
    }


    /**
     * @todo  need to apply discount price for every recurrerance
     * [getRecurringParams description]
     * @param  [type] $quote [description]
     * @return [type]        [description]
     */
    protected function getRecurringParams($quote){
    	$items = $quote->getItems();
    	$lineitems = array();
    	$i = 1;
    	$new_period = "1 Month";   // Default value of subscription - Monthly
    	
    	try {
        	foreach($items as $item){

                $product_name = $item->getName();


                //this condition for ebook allow
                if($item->getProductType() == 'downloadable') {
                    return $lineitems;
                }
                
                // Calculation of subscription period
                // For that the catalog_attributes.xml file should be added 
                // The old m1 code
                /*$sub_period_lable = $item->getProduct()->getAttributeText('subscription_period');   //4 user enterprise license
                $sub_period_attribute = $item->getProduct()->getResource()->getAttribute('subscription_period');

                if ($sub_period_attribute->usesSource()) {
                    $option_id = $sub_period_attribute->getSource()->getOptionId($sub_period_lable);
                    $sub_period_text = $sub_period_attribute->setStoreId(0)->getSource()->getOptionText($option_id);//E.g : 12 month ( Enterprise )
                }
                


                $sub_period = explode(" ", $sub_period_text);
    			$new_period = $sub_period[0];
    			$new_duration = $sub_period[1];
                                 

              	if($new_duration == "day"){
    				$new_period .= " Week";
              	}
                else{
                    $new_period .= " Month";
                }*/

                $product_recurring_profile = $item->getProduct()->getData('recurring_payment');
                $period_no = isset($product_recurring_profile['period_frequency'])?$product_recurring_profile['period_frequency'] : null;        // Number of months or days || 12
                $duration_text = isset($product_recurring_profile['period_unit']) ? $product_recurring_profile['period_unit'] : null ;    // month
                $duration_text = !empty($duration_text) ? ucfirst($duration_text) : null;
                
                if(empty($period_no) || empty($duration_text)){
                    throw new \Magento\Framework\Exception\LocalizedException(__('Invalid value of Subscription Period OR Subscription Frequency.'));
                }

                $new_period = $period_no." ".$duration_text;

    			$lineitems['li_'.$i.'_type'] = 'product';
    			$lineitems['li_'.$i.'_tangible'] = 'N';
    			$lineitems['li_'.$i.'_product_id'] = $item->getSku();
    			$lineitems['li_'.$i.'_quanity'] = $item->getQtyToInvoice();
    			$lineitems['li_'.$i.'_name'] = $product_name;
    			$lineitems['li_'.$i.'_description'] = $item->getDescription();
    			$lineitems['li_'.$i.'_price'] = number_format($quote->getGrandTotal(), 2, '.', '');
    		  	$lineitems['li_'.$i.'_recurrence'] = $new_period;
    		  	
    		  	$lineitems['li_'.$i.'_duration'] = "Forever";

                // There are two cases 
                // 1. Initial Discount : only first payment has discount and all post recurrence has full amount
                //      To use this we can use li_#_startup_fee
                
                // If need to apply the first case then uncomment following and comment second case
                /*$quoteTotal = round($quote->getGrandTotal(), 2);
                if($lineitems['li_'.$i.'_price'] != $quoteTotal){
                    if($lineitems['li_'.$i.'_price'] > $quoteTotal){
                        $lineitems['li_'.$i.'_startup_fee'] = $quoteTotal - $lineitems['li_'.$i.'_price'];
                    }    
                }*/

                // If need to apply the second case then comment first case and uncomment following code
                // 2. Permanent Discount : It makes every first and all next recurring payments discuounted
                //      To do this we don't have to send the startup_fee 
                


                $i++;
            }
            $lineitems['mode'] = "2CO";
    		$lineitems['purchase_step'] = "review-cart";

            return $lineitems;
        } catch (Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }
}
