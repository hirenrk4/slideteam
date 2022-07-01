<?php
namespace Tatva\Metatitle\Ui\Component\Listing\Column\TatvametatitleListing;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id = "X";
                if(isset($item["metatitle_id"]))
                {
                    $id = $item["metatitle_id"];
                }
                //echo $id; exit;
                $item[$name]["view"] = [
                "href"=>$this->getContext()->getUrl(
                    "metatitle/index/edit",["metatitle_id"=>$id]),
                "label"=>__("Edit")
                ];
            }
        }

        return $dataSource;
    }    
    
}
