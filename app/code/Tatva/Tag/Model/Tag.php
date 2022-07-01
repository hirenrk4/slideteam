<?php
namespace Tatva\Tag\Model;

class Tag extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_DISABLED = -1;
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    // statuses for tag relation add
    const ADD_STATUS_SUCCESS = 'success';
    const ADD_STATUS_NEW = 'new';
    const ADD_STATUS_EXIST = 'exist';
    const ADD_STATUS_REJECTED = 'rejected';

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'tag';

    /**
     * Event prefix for observer
     *
     * @var string
     */
    protected $_eventPrefix = 'tag';

    /**
     * This flag means should we or not add base popularity on tag load
     *
     * @var bool
     */
    protected $_addBasePopularity = false;

    /**
     * @var \Magento\Indexer\Model\Indexer
     */
    protected $indexerIndexer;

    /**
     * @var \Tatva\Tag\Model\Resource\Product\CollectionFactory
     */
    protected $tagResourceProductCollectionFactory;

    /**
     * @var \Tatva\Tag\Model\Resource\Customer\CollectionFactory
     */
    protected $tagResourceCustomerCollectionFactory;

    /**
     * @var \Tatva\Tag\Model\Resource\Popular\CollectionFactory
     */
    protected $tagResourcePopularCollectionFactory;

    /**
     * @var \Tatva\Tag\Model\Tag\RelationFactory
     */
    protected $tagTagRelationFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Indexer\Model\Indexer $indexerIndexer,
        \Tatva\Tag\Model\ResourceModel\Product\CollectionFactory $tagResourceProductCollectionFactory,
        \Tatva\Tag\Model\ResourceModel\Customer\CollectionFactory $tagResourceCustomerCollectionFactory,
        \Tatva\Tag\Model\ResourceModel\Popular\CollectionFactory $tagResourcePopularCollectionFactory,
        \Tatva\Tag\Model\Tag\RelationFactory $tagTagRelationFactory,
        \Tatva\Tag\Helper\Tag $tagHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlInterface,   
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->indexerIndexer = $indexerIndexer;
        $this->tagResourceProductCollectionFactory = $tagResourceProductCollectionFactory;
        $this->tagResourceCustomerCollectionFactory = $tagResourceCustomerCollectionFactory;
        $this->tagResourcePopularCollectionFactory = $tagResourcePopularCollectionFactory;
        $this->tagTagRelationFactory = $tagTagRelationFactory;
        $this->storeManager = $storeManager;
        $this->_urlInterface = $urlInterface;
        $this->_tagHelper = $tagHelper;

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }


    protected function _construct()
    {
        $this->_init('Tatva\Tag\Model\ResourceModel\Tag');
    }

  
    public function getCustomTaggedProductsUrl($tagName="")
    {
        $tagname=$tagName!=""?$tagName:$this->getName();
        $identifier = $this->_tagHelper->tagNameToUrlIdentifier($tagname);
        
        $storeUrl = $this->_urlInterface->getUrl('tag');
      
        $url = $storeUrl.trim($identifier);
        $url = strtolower(str_replace(" ","-",$url));

        // 1545 tag elastic search url rewrite
        $tagReqUrl = 'tag/'.strtolower(str_replace(" ","-",trim($identifier))).'-powerpoint-templates-ppt-slides-images-graphics-and-themes';
        $tagTargeturl = 'catalogsearch/result/?q='.$tagname;
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $urlrewrite = $objectManager->create('\Magento\UrlRewrite\Model\UrlRewrite');

        $tagurlCollection = $urlrewrite->getCollection()->addFieldToFilter('request_path',$tagReqUrl);

        if (empty($tagurlCollection->getData())) {
                $urlrewrite->setStoreId($this->storeManager->getStore()->getId());
                $urlrewrite->setEntityType('custom');
                $urlrewrite->setEntityId('0');
                $urlrewrite->setTargetPath($tagTargeturl);
                $urlrewrite->setRequestPath($tagReqUrl);
                $urlrewrite->save();
        }
        // 1545 tag elastic search url rewrite end

        return $url.'-powerpoint-templates-ppt-slides-images-graphics-and-themes';
        
    }
      public function getPopularCollection()
    {
        return $this->tagResourcePopularCollectionFactory->create();
    }


    public function getApprovedStatus()
    {
        return self::STATUS_APPROVED;
    }

     public function saveTags($importDatatags, $producttableId, $only_inserts)
    {
       $connection = $this->_getResource()->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

        $item = explode(",", $importDatatags);


        if (is_array($item))
            $item = array_map("trim", $item);

        $all_tag_ids = array();
        $all_tag_ids_string = "";
        $relation_tag_ids = array();
        $relation_tag_ids_string = "";

        foreach ($item as $tag_value)
        {        
            if ($tag_value != "")
            {
                $tag_id = $this->getTagId($connection, $tag_value);
    
                $all_tag_ids[] = $tag_id;
                $relation = $this->insert_into_tag_relation($connection, $tag_id, $producttableId);
                if (is_array($relation))
                {
                    $all_tag_ids_string.="'" . $tag_id . "',";
                    $relation_tag_ids[] = $relation[0];
                    $relation_tag_ids_string.="'" . $relation[0] . "',";
                }
                else
                {
                    $relation_tag_ids[] = $relation;
                    $relation_tag_ids_string.="'" . $relation . "',";
                }
            }
        }
         $all_tag_ids_string = rtrim($all_tag_ids_string, ",");
        $relation_tag_ids_string = rtrim($relation_tag_ids_string, ",");

        if (!$only_inserts && !empty($relation_tag_ids_string))
        {

            $sql_relation_delete = "delete from tag_relation where tag_relation_id not in ($relation_tag_ids_string) and product_id='$producttableId'";
            $connection->query($sql_relation_delete);
        }
        $row_relation = array();

        if ($all_tag_ids_string != "")
        {
            $sql_relation = "select tag_id,count(tag_id) as 'number' from tag_relation where tag_id in ($all_tag_ids_string) group by tag_id";
            $row_relation = $connection->fetchAll($sql_relation);
        }

        foreach ($row_relation as $relation)
        {
            $tag_id = $this->check_tag_id_in_tag_summery($connection,$relation["tag_id"]);
                    
            if ($tag_id == "")
            {
                $store_id = '0';
                $sql = "insert into tag_summary(tag_id,store_id,customers,products,uses,historical_uses,popularity,base_popularity) values ('" . $relation["tag_id"] . "','$store_id','0','" . $relation["number"] . "','0','0','" . $relation["number"] . "','0')";
                $connection->query($sql);

                $store_id = '1';
                $sql = "insert into tag_summary(tag_id,store_id,customers,products,uses,historical_uses,popularity,base_popularity) values ('" . $relation["tag_id"] . "','$store_id','0','" . $relation["number"] . "','0','0','" . $relation["number"] . "','0')";
                $connection->query($sql);
            }
            else
            {
                $sql = "update tag_summary set products='" . $relation["number"] . "' where tag_id='$tag_id'";
                $connection->query($sql);
            }

        }
    }

    public function check_tag_id_in_tag_summery($connection, $tag_id)
    {
        $sql = "select tag_id from tag_summary where tag_id='" . $tag_id . "'";
        $row = array();
        $row = $connection->fetchAll($sql);
        if(count($row) > 0){
            if (!empty($row["0"]["tag_id"]))
            {
                return $row["0"]["tag_id"];
            }
            else
                return "";    
        }
        
    }

    public function insert_into_tag_relation($connection, $tag_id, $product_id)
    {

        $sql = "select tag_relation_id from tag_relation where tag_id='" . $tag_id . "' and product_id='" . $product_id . "'";

        $row = array();
        $row = $connection->fetchAll($sql);
            if(count($row) > 0){
                if (!empty($row["0"]["tag_relation_id"]))
                {
                    return $row["0"]["tag_relation_id"];
                }
            }
            else
            {

                $sql_insert = "insert into tag_relation(tag_id,customer_id,product_id,store_id,active) values ('$tag_id',0,'$product_id','1','1')";
                $connection->query($sql_insert);
                $tag_relation_id = $connection->lastInsertId();
                return array($tag_relation_id);
            }    
       
        
    }

    public function getTagId($connection,$tag)
    {

        $sql = "select tag_id from tag where name='" . $tag . "'";
        $row = array();
         $row = $connection->fetchAll($sql);
     
                if(count($row) > 0){
                    if (!empty($row["0"]["tag_id"]))
                    {
                        return $row["0"]["tag_id"];
                    }
                }
            else
            {
                $sql_insert = "insert into tag(name,status,first_customer_id,first_store_id) values ('$tag','1',NULL,'1')";   
                $connection->query($sql_insert);
                $tag_id = $connection->lastInsertId();
                $sql_insert = "insert into tag_properties(tag_id,store_id,base_popularity) values ('$tag_id','1','0')";
                $connection->query($sql_insert);
                return $tag_id;
            }    
        
    }
    



}
