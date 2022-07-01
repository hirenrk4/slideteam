<?php
namespace Tatva\Subscription\Model;
/**
 * M-Connect Solutions.
 *
 * NOTICE OF LICENSE
 *

 *
 * @category   Catalog
 * @package   Mconnect_Shareanddownloadproducts
 * @author      M-Connect Solutions (http://www.magentoconnect.us)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Shareanddownloadproducts extends \Magento\Framework\Model\AbstractModel
{
	protected function _construct()
	{
		$this->_init('Tatva\Subscription\Model\ResourceModel\Shareanddownloadproducts');
	}

	/**
	 * [isShareAndDownloadProduct] Validate the product with product_id is shareable or not
	 * @param  [type]  $product_id 
	 * @return boolean | product_object if true
	 */
	public function isShareAndDownloadProduct($product_id)
	{
		$isShareAndDownloadProduct = false;
		if($product_id){
			$share_and_download = $this->getCollection()->addFieldToFilter('product_id', $product_id);
            $isShareAndDownloadProduct = $share_and_download->getData();
		}
		return $isShareAndDownloadProduct;
	}
}
