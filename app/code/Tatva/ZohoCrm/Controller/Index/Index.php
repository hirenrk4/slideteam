<?php
namespace Tatva\ZohoCrm\Controller\Index;


class Index extends \Magento\Framework\App\Action\Action
{
    /**
    * Zoho CRM Helper
    * @var \Tatva\ZohoCrm\Helper\Data
    */
    protected $zohoCRMHelper;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Tatva\ZohoCrm\Helper\Data $zohoCRMHelper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->zohoCRMHelper = $zohoCRMHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        echo "Test<pre>";
        try{
            $customerIds=[];
            $connection = $this->resourceConnection->getConnection();
            $sql = "SELECT id,customer_id,created_at FROM `zoho_customer_tracking` where isSend=0 GROUP by customer_id";

            //$sql = "SELECT id,customer_id,created_at FROM `zoho_customer_tracking` where isSend=0 AND TIMESTAMPDIFF(MINUTE,created_at,NOW()) > 15 GROUP by customer_id";
            print_r($sql);
            $results = $connection->fetchAll($sql);
            print_r($results);

            $scopeSql="SET SESSION group_concat_max_len = 1048576";
            $connection->query($scopeSql);

            foreach ($results as $key => $value) {
                $customerIds[]=$value['customer_id'];
                $sql = "SELECT GROUP_CONCAT(page_uri SEPARATOR '\n') As page_uris, customer_id FROM zoho_customer_tracking where customer_id=".$value['customer_id']."  GROUP BY customer_id";
            
                $pageUriResults = $connection->fetchAll($sql);

                $urlsModule =array(
                    "URLS_Browsed"=>$pageUriResults[0]['page_uris'],
                    "Priority"=>"4",
                    "Comment"=> "URL Browsed Information",
                );
                $this->zohoCRMHelper->editCustomer($urlsModule,$value['customer_id']);
            }
            if(!empty($customerIds)){
                $sqlUpdate="UPDATE zoho_customer_tracking SET isSend=1 WHERE customer_id IN (".implode(',',$customerIds).")";
                $connection->query($sqlUpdate);
            }
        } catch (\Exception $e) {
            print_r($e->getMessage());
        }
    }

}