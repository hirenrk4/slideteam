<?php
namespace Tatva\ZohoCrm\Controller\Index;


class Delete extends \Magento\Framework\App\Action\Action
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
        echo "Test Delete<pre>";
        try{
            $connection = $this->resourceConnection->getConnection();
            $sqlUpdate="DELETE FROM zoho_customer_tracking WHERE isSend=1";
            $connection->query($sqlUpdate);
        } catch (\Exception $e) {
            print_r($e->getMessage());
        }
    }

}