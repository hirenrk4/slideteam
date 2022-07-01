<?php
namespace Tatva\ZohoCrm\Cron;

class DeleteZohoTrackingData
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute()
    {
        try{
            $connection = $this->resourceConnection->getConnection();
            $sqlDelete="DELETE FROM zoho_customer_tracking WHERE isSend=1";
            $connection->query($sqlDelete);
        } catch (\Exception $e) {
            print_r($e->getMessage());
        }
    }
}