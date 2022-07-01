<?php
namespace Tatva\Unsubscribetco\Ui\Component\Listing\Column\TatvaUnsubscribeListing;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id = "X";
                if(isset($item["customer_id"]))
                {
                    $id = $item["customer_id"];
                }
                //echo $id; exit;
                $item[$name] = [
                'view' => [
                'href' => $this->getContext()->getUrl(
                    "unsubscribetco/index/unsubscribe",["customer_id"=>$id]),
                'label' => __('Unsubscribe'),
                'confirm' => [
                'title' => __('Unsubscribe'),
                'message' => __('Are you sure?')
                ]
                ]
                ];
            }
        }

        return $dataSource;
    }    
    
}
