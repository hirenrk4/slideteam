<?php
namespace Tatva\Customerreport\Ui\Component\Listing\Column\TatvaCustomerreportListing;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id="";

                if(isset($item["entity_id"]))
                {
                    $id = $item["entity_id"];
                }
                $item[$name]["view"] = [
                "href"=>$this->getContext()->getUrl(
                    "customerreport/customer/edit",["entity_id"=>$id]),
                "label"=>__("Edit")
                ];
            }
        }

        return $dataSource;
    }    
    
}
