<?php
namespace Tatva\Sentence\Ui\Component\Listing\Column\TatvaSentenceListing;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource["data"]["items"])) {
            foreach ($dataSource["data"]["items"] as & $item) {
                $name = $this->getData("name");
                $id="";

                if(isset($item["sentence_id"]))
                {
                    $id = $item["sentence_id"];
                }
                $item[$name]["view"] = [
                "href"=>$this->getContext()->getUrl(
                    "sentence/index/edit",["sentence_id"=>$id]),
                "label"=>__("Edit")
                ];
            }
        }

        return $dataSource;
    }    
    
}
