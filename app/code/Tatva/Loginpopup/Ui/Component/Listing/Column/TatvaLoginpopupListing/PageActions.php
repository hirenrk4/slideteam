<?php
namespace Tatva\Loginpopup\Ui\Component\Listing\Column\TatvaLoginpopupListing;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id = "X";
                if(isset($item["id"]))
                {
                    $id = $item["id"];
                }
              //  echo $id; exit;
                $item[$name]["view"] = [
                    "href"=>$this->getContext()->getUrl(
                        "loginpopup/index/edit",["id"=>$id]),
                    "label"=>__("Edit")
                ];
            }
        }

        return $dataSource;
    }    
    
}
