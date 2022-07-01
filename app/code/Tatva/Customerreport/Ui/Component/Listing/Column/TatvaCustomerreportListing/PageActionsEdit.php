<?php
namespace Tatva\Customerreport\Ui\Component\Listing\Column\TatvaCustomerreportListing;

class PageActionsEdit extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id="";
                
                if(isset($item["subscription_history_id"]))
                {
                    $id = $item["subscription_history_id"];
                }
                $item[$name]["view"] = [
                "href"=>$this->getContext()->getUrl(
                    "customerreport/customer/editreport",["subscription_history_id"=>$id]),
                "label"=>__("Edit")
                ];
            }
        }

        return $dataSource;
    }    
    
}
