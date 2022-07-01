<?php
namespace Tatva\Metadescription\Ui\Component\Listing\Column\TatvametadescListing;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id = "X";
                if(isset($item["metadescription_id"]))
                {
                    $id = $item["metadescription_id"];
                }
                //echo $id; exit;
                $item[$name]["view"] = [
                "href"=>$this->getContext()->getUrl(
                    "metadescription/index/edit",["metadescription_id"=>$id]),
                "label"=>__("Edit")
                ];
            }
        }

        return $dataSource;
    }    
    
}
