<?php
namespace Tatva\Metadescription\Model;


class Metadescription extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var \Tatva\Metadescription\Model\Mysql4\Metadescription\CollectionFactory
     */

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
        \Tatva\Metadescription\Model\ResourceModel\Metadescription\CollectionFactory $metadescriptionCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\Product\Action $catalogProductAction,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
        ) {
        $this->metadescriptionCollectionFactory = $metadescriptionCollectionFactory;
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


    public function _construct()
    {
        parent::_construct();
        $this->_init('Tatva\Metadescription\Model\ResourceModel\Metadescription');
    }
    
    public function importCsv($line)
    {

        $metadescription_collection_count_product = $this->metadescriptionCollectionFactory->create();

        // Get Maximum Number of Count from column 'number_of_usage'

        if ($metadescription_collection_count_product->getSize() > 0)
        {
            $metadescription_collection_count_product->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('MAX(number_of_usage_product) as number_of_usage_product');
            foreach ($metadescription_collection_count_product as $c)
            {
                $max_count_product = $c['number_of_usage_product'];
            }
        }
        else
        {
            $max_count_product = 0;
        }

        $flag = 1;
        $count = 0;
        
        
        $line_value = $line[0];
        $line_normal = iconv("ASCII", "UTF-8//IGNORE", $line_value);
        $line_normal = trim($line_normal, ' ');

        $line_lower = strtolower($line_normal);

        $model = $this->metadescriptionCollectionFactory->create();

        foreach ($model as $m)
        {
            $metadescription = $m->getMetadescription();
            $metadescription = strtolower($metadescription);

            if (strcmp($metadescription, $line_lower) == '0')
            {
                $count++;
                break;
            }

        }

        if ($count == '0')
        {   
         $this->setMetadescription($line_normal);

         if ($max_count_product > 0)
         {
            $temp_product = $max_count_product - 1;
            $this->setNumberOfUsageProduct($temp_product);
        }
        else
        {
            $this->setNumberOfUsageProduct($max_count_product);
        }

        $this->save();
        $this->unsetData();
        return $flag;
    }
}

}

