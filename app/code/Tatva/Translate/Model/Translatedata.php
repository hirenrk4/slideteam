<?php
namespace Tatva\Translate\Model;

class Translatedata
{

    public function __construct(
        \Tatva\Translate\Model\PostFactory $postFactory,
        \Tatva\Translate\Model\LanguageFactory $langFactory
    ){
        $this->_postFactory = $postFactory;
        $this->_langFactory = $langFactory;
    }

    public function getTraslatedata($product_id,$lang){

        $collection1 = $this->_langFactory->create()->getCollection()->addFieldToFilter('laguage', $lang);
            foreach ($collection1->getData() as $data) 
            {
                $lang_id = $data['entity_id'];
            }

            $collection2 = $this->_postFactory->create()->getCollection()->addFieldToFilter('lang_id', $lang_id)->addFieldToFilter('product_id', $product_id);

            $translatetxt = array();
            $i = 0;
            foreach ($collection2 as $key => $value) 
            {
                $translatetxt[$i]['attribute_id'] = $value->getAttributeId();
                $translatetxt[$i]['value'] = $value->getValue();
                $i++;
            }

            return $translatetxt;
    }
}