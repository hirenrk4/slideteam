<?php
namespace Tatva\Deleteaccount\Ui\Component\Listing\Column\subscriptionListing;

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
                $item[$this->getData('name')] = [
                    'subsciptions' => [
                        'href' => $this->getContext()->getUrl(
                        "deleteaccount/index/subsciptions",["del_customer_id"=>$id]),
                        'label' => __('Subsciptions')
                    ],
                    'downloads' => [
                        'href' => $this->getContext()->getUrl(
                        "deleteaccount/index/downloads",["customer_id"=>$id]),
                        'label' => __('Downloads')
                    ],
                ];
            }
        }

        return $dataSource;
    }    
    
}
