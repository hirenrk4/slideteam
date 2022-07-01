<?php 
namespace Tatva\Customproductreview\Model;

class Customproductreview extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $catalogProductAction;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Tatva\Customproductreview\Model\ResourceModel\Customproductreview\CollectionFactory $customproductreviewCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\Product\Action $catalogProductAction,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
        ) {
        $this->customproductreviewCollectionFactory = $customproductreviewCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->catalogProductAction = $catalogProductAction;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
            );
    }	
	public function _construct(){
		$this->_init("Tatva\Customproductreview\Model\ResourceModel\Customproductreview");
	}
	public function importCsv($line)
    {

        $product_review_collection_count_product = $this->customproductreviewCollectionFactory->create();
        $flag = 1;
        $count = 0;
        
        
        $line_value = $line[0];
        $line_normal = iconv("ASCII", "UTF-8//IGNORE", $line_value);
        $line_normal = trim($line_normal, ' ');

        $model = $this->customproductreviewCollectionFactory->create();

        foreach ($model as $m)
        {
            $product_review = $m->getReviewDetail();

            if (strcmp($product_review, $line_normal) == '0')
            {
                $count++;
                break;
            }

        }

        if ($count == '0')
        {   
         	$this->setReviewDetail($line_normal);

        	$this->save();
        	$this->unsetData();
        	return $flag;
    	}
    }
}
?>