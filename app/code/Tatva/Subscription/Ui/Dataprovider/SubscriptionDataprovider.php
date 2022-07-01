<?php
namespace Tatva\Subscription\Ui\DataProvider;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\RequestInterface;

class SubscriptionDataprovider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
	    /**
     * Data Provider name
     *
     * @var string
     */
	    protected $name;

    /**
     * Data Provider Primary Identifier name
     *
     * @var string
     */
    protected $primaryFieldName;

    /**
     * Data Provider Request Parameter Identifier name
     *
     * @var string
     */
    protected $requestFieldName;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * Provider configuration data
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var ReportingInterface
     */
    protected $reporting;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SearchCriteria
     */
    protected $searchCriteria;

    protected $needVerifyCollectionFatory;
    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
    	$name,
    	$primaryFieldName,
    	$requestFieldName,
    	ReportingInterface $reporting,
    	SearchCriteriaBuilder $searchCriteriaBuilder,
    	RequestInterface $request,
    	FilterBuilder $filterBuilder,
    	\Tatva\Subscription\Model\ResourceModel\Subscription\Grid\CollectionFactory $needVerifyCollectionFatory,
    	array $meta = [],
    	array $data = []
    	) {
    	$this->request = $request;
    	$this->filterBuilder = $filterBuilder;
    	$this->name = $name;
    	$this->primaryFieldName = $primaryFieldName;
    	$this->requestFieldName = $requestFieldName;
    	$this->reporting = $reporting;
    	$this->searchCriteriaBuilder = $searchCriteriaBuilder;
    	$this->meta = $meta;
    	$this->data = $data;
    	parent::__construct($name,$primaryFieldName,$requestFieldName,$reporting,$searchCriteriaBuilder,$request,$filterBuilder,$meta,$data);
    	$this->needVerifyCollectionFatory = $needVerifyCollectionFatory;
    	$this->initCollection();
    }
    public function initCollection()
    {

    	$collection = $this->needVerifyCollectionFatory->create();
    	return $this->collection = $collection->addFieldToFilter("reminder_purchase",array("eq"=>1));
    }
}
?>