<?php
namespace Tatva\Subscription\Model;
use Tatva\Subscription\Model\Shareanddownloadproducts;


class Mostdownload extends \Magento\Framework\Model\AbstractModel
{
    /*
     * List of Parameters
     * $llimit  = maximam number of products to be returned
     * $category_id = filter with category
     * $date = most download by today or specified date
     * $recursive_off = recursive is true or false. if for the given date , result products are zero then if recursive is on then again same function will be called without date.
     * $query = if this is true then query will be returned instead of array result
     * $free =  whether wants to include free products in result or not
     */

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resourceConnection;
    protected $_shareanddownloadproducts;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        Shareanddownloadproducts $shareanddownloadproducts
        ) {
        $this->_resourceConnection = $resourceConnection;
        $this->_shareanddownloadproducts = $shareanddownloadproducts;

    }
    public function mostDownloadByDate($llimit, $category_id, $date="", $recursive_off = false, $query=false, $free = true)
    {
        $result = array();

        if ($date != "")
        {
            $date_filter1 = " WHERE ri.download_date > '" . $date . "' ";
            $date_filter2 = " WHERE li.download_date > '" . $date . "' ";
        }
        else
        {
            $date_filter1 = "";
            $date_filter2 = "";
        }

        if ($category_id != "")
            $category_filter = "where rr.main_category_id = '" . $category_id . "'";
        else
            $category_filter = "";


        if ($llimit != "")
            $limit_filter = " limit 0,$llimit ";
        else
            $limit_filter = "";


        if ($free)
            $free_filter = "";
        else
            $free_filter = " and `catalog_int`.`value` = '0' ";



        $resource = $this->_resourceConnection;
        $read = $resource->getConnection('core_read');

        $sql = "select main_visibility.* from (select `main`.* from (
        select ll.*, rr.category_ids,rr.main_category_id from  ( SELECT l.product_id, (
        r.no - l.no_minus
        ) AS 'downloaded'
        FROM (


        SELECT rii.product_id, sum( rii.no_minus ) AS 'no_minus'
        FROM (

        SELECT ri.product_id, ri.customer_id, (
        count( ri.product_id ) -1
        ) AS 'no_minus'
        FROM `productdownload_history_log` AS ri
        " . $date_filter1 . "
        GROUP BY ri.product_id, ri.customer_id
        ) AS rii
        GROUP BY rii.product_id



        ) AS l
        LEFT JOIN (



        SELECT li.product_id AS 'p_id', count( li.product_id ) AS 'no'
        FROM `productdownload_history_log` AS li " . $date_filter2 . "
        GROUP BY li.product_id



        ) AS r ON r.p_id = l.product_id order by downloaded desc) as ll left join (select * from  `productdownload_history` as t group by t.product_id,t.category_ids ) as rr on  rr.product_id =ll.product_id  " . $category_filter . "

        ) as `main` left join `catalog_product_entity_int` as `catalog_int` on  `catalog_int`.`entity_id` = `main`.`product_id` where `catalog_int`.`attribute_id` = '126' and `catalog_int`.`store_id` = '0' and `catalog_int`.`entity_type_id` = '4' $free_filter 
        )as main_visibility INNER JOIN `catalog_category_product_index` AS `cat_index` ON `cat_index`.`product_id`=`main_visibility`.`product_id` and `cat_index`.`store_id`=1 AND `cat_index`.`visibility` IN(2, 4) group by `main_visibility`.`product_id` order by downloaded desc $limit_filter
        ";

        if ($query)
            return $sql;

        $result = $read->fetchAll($sql);

        $final_result = "";
        if (is_array($result) && count($result) > 0)
        {
            $final_result = $result;
        }
        else if (!$recursive_off)
            $final_result = $this->mostDownloadByDate($llimit, $category_id, "", true);

        return $final_result;
    }

    public function downloadCheckPerCustomerSubscription($customer_id, $from_date)
    {
        $from_date = date("Y-m-d H:i:s", $from_date);

        $resource = $this->_resourceConnection;
        $read = $resource->getConnection('core_read');

        $sql = "
        SELECT count( customer_id ) AS 'total_downloaded'
        FROM (

        SELECT *
        FROM (

        SELECT * , count( product_id )
        FROM productdownload_history_log
        WHERE customer_id = '$customer_id'
        AND download_date > '$from_date'
        GROUP BY `product_id`
        ) AS `main` 
        LEFT JOIN `catalog_product_entity_int` AS `catalog_int` ON `catalog_int`.`entity_id` = `main`.`product_id`
        WHERE `catalog_int`.`attribute_id` = '126'
        AND `catalog_int`.`store_id` = '0'
        AND `catalog_int`.`value` = '0'
        ) AS `outer`
        GROUP BY outer.customer_id;
        ";

        $result = $read->fetchOne($sql);

        return $result ? $result : 0;
    }

    public function productAllowedToDownload($customer_id, $from_date, $product_id)
    {

        if ($product_id != "" && intval($product_id) > 0)
        {
            $from_date = date("Y-m-d H:i:s", $from_date);

            $resource = $this->_resourceConnection;
            $read = $resource->getConnection('core_read');

            $sql = "
            SELECT product_id
            FROM productdownload_history_log
            WHERE customer_id = '$customer_id'
            AND download_date > '$from_date'
            GROUP BY `product_id`
            ";

            $result = $read->fetchCol($sql);

            $share_and_download_product = $this->_shareanddownloadproducts->isShareAndDownloadProduct($product_id);

            if (is_array($result) && in_array($product_id, $result))
                return true;
            else if (!is_array($result) && $result == $product_id)
                return true;
            else if (!empty($share_and_download_product))
                return true;
            else
                return false;
        }
        else
            return false;
    }

}

?>
