<?php
namespace Tatva\Translate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;

class Productsaveafter implements ObserverInterface
{    
	protected $scopeConfig;
    protected $curl;
    protected $messageManager;
    private $attributeRepository;
	public function __construct(
    	Curl $curl,
    	AttributeRepositoryInterface $attributeRepository,
        \Tatva\Translate\Model\PostFactory $postFactory,
        \Tatva\Translate\Model\LanguageFactory $langFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    	$this->curl = $curl;
    	$this->attributeRepository = $attributeRepository;
    	$this->messageManager = $messageManager;
    	$this->langFactory = $langFactory;
        $this->postFactory = $postFactory;
        $this->scopeConfig = $scopeConfig;
    }

   public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $_product = $observer->getProduct();
        $attribute = $this->attributeRepository->get(Product::ENTITY, 'name');
        $title_code = $attribute->getAttributeId();
        $attribute = $this->attributeRepository->get(Product::ENTITY, 'description');
        $description_code = $attribute->getAttributeId();
        $attribute = $this->attributeRepository->get(Product::ENTITY, 'sentence1');
        $sentence1_code = $attribute->getAttributeId();
        $attribute = $this->attributeRepository->get(Product::ENTITY, 'content_powerpoint');
        $content_code = $attribute->getAttributeId();
        $attribute = $this->attributeRepository->get(Product::ENTITY, 'sentence2');
        $sentence2_code = $attribute->getAttributeId();
       
        $productId = $_product->getId();
   		$key = $this->scopeConfig->getValue('button/laguage_config/google_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
   		$collection = $this->langFactory->create()->getCollection();
   		$size = sizeof($collection);
   		if($size == 0)
   		{
   			$lang_arr = array('Spanish','German','French','Portuguese');
   			$lang_code = array('es','de','fr','pt');
   			foreach($lang_arr as $index => $col)
   			{
   				$model = $this->langFactory->create();
		        $model->setData(array('laguage'=>$lang_arr[$index],'laguage_code'=>$lang_code[$index]));
		        $saveData = $model->save();
			}
   		}
   		
   		if(!empty($key))
   		{	
			$name = $_product->getData('name');
			$pro_description = $_product->getData('description');
			$sentence1 = $_product->getData('sentence1');
			$content_powerpoint = $_product->getData('content_powerpoint');
            $sentence2 = $_product->getData('sentence2');
            $images = $_product->getData('media_gallery');
            $imgcount = count($images['images']);
            $sentence2txt = $name.' with all '.$imgcount.' slides:';
			$language_status = $_product->getLanguagestatus();
				
			if($language_status == 1)
			{	
				$collection = $this->langFactory->create()->getCollection();
				foreach ($collection as $kay=>$value) 
				{
		    		$langId = $value['entity_id'];
		    		
		    		$lang_code = $value['laguage_code'];
					
					$URL = 'https://google-translate1.p.rapidapi.com/language/translate/v2';
					$headers = ["x-rapidapi-host" => "google-translate1.p.rapidapi.com","x-rapidapi-key" => $key,"Content-Type" => "application/x-www-form-urlencoded"];
					$params = [
						'source' => 'en',
						'q' => $name.'|'.$sentence1.'|'.'Features of these PowerPoint presentation slides :'.'|'.$pro_description.'|'.$sentence2.'|'.$sentence2txt.'|'.$content_powerpoint.'|'.'<div>Content of this Powerpoint Presentation<div>',
						'target' => $lang_code,
					];
				 
					$this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
					//set curl header
					$this->curl->setHeaders($headers);
					//post request with url and data
					$this->curl->post($URL, $params);
					//read response
					$response = $this->curl->getBody();
				    $data = json_decode($response, true);
				    $comp = explode("|", $data['data']['translations'][0]['translatedText']);
				    $title = $comp[0];
					$subtitle = $comp[1];
					$text = $comp[2];
					$description = $comp[3];
					$subtitle2 = $comp[4];
                    $text2 = $comp[5];
                    $slide_content = $comp[6];
                    $text_pp = $comp[7];
						
					$collection = $this->postFactory->create()->getCollection()->addFieldToFilter('product_id', $productId)->addFieldToFilter('lang_id', $langId)->addFieldToFilter('attribute_id', array('in' => array($title_code,$sentence1_code,$description_code,$content_code,$sentence2_code)));
						
					$size = sizeof($collection);
					if(!empty($size))
					{
						$attributesIds = array($title_code,$sentence1_code,$description_code,$content_code,$sentence2_code);

		                $existingAttr = array();
		                $i = 0;
		                foreach ($collection as $curData) 
		                {
		                    $existingAttr[$i] = $curData->getAttributeId();
		                    $i++;
		                }
		                $result=array_diff($attributesIds,$existingAttr);

		                if(!empty($result))
		                {
		                    foreach($result as $attrid)
		                    {
		                        $model = $this->postFactory->create();
		                        $data = array();
		                        $data['product_id'] = $productId;
		                        $data['lang_id'] = $langId;
		                        $data['attribute_id'] = $attrid;

		                        if($attrid == $title_code)
		                            {  
		                                $data['value'] = $title; 
		                            }
		                            if($attrid == $sentence1_code)
		                            {
		                                $data['value'] = $subtitle; 
		                            }
		                            if($attrid == $description_code)
		                            {
		                                $data['value'] = $description.'<html>'.$text.'<html>';
		                            }
		                            if($attrid == $sentence2_code)
		                            { 
		                                $data['value'] = $subtitle2.'<html>'.$text2;
		                            }
		                            if($attrid == $content_code)
		                            {   
		                                $data['value'] = $slide_content.$text_pp;
		                            }
		                            
		                            $model->setData($data);
		                            $saveData = $model->save();
		                    }
		                }

		               	foreach ($collection as $curData) 
		                {
		                    $curr_attr = $curData->getAttributeId();   
		                    if($curr_attr == $title_code)
		                    {
		                        $curData->setValue($title)->save();
		                    }
		                    if($curr_attr == $sentence1_code)
		                    {
		                        $curData->setValue($subtitle)->save();
		                    }
		                    if($curr_attr == $description_code)
		                    {
		                        $finalDesc = $description.'<html>'.$text.'<html>';
		                        $curData->setValue($finalDesc)->save();
		                    }
		                    if($curr_attr == $sentence2_code)
		                    {
		                        $finaltxt = $subtitle2.'<html>'.$text2;
		                        $curData->setValue($finaltxt)->save();
		                    }
		                    if($curr_attr == $content_code)
		                    {
		                        $finalcontent = $slide_content.$text_pp;
		                        $curData->setValue($finalcontent)->save();
		                    }
		                }
		            }
		            else
					{
						$model = $this->postFactory->create();
		                $data = array('product_id' => $productId, 'lang_id'=>$langId,'attribute_id'=>$title_code,'value'=>$title);
		                $model->setData($data);
		                $saveData = $model->save(); 
		                        
		                $model = $this->postFactory->create();
		                $data = array('product_id' => $productId, 'lang_id'=>$langId,'attribute_id'=>$sentence1_code,'value'=>$subtitle);
		                $model->setData($data);
		                $saveData = $model->save();  

		                $model = $this->postFactory->create();
		                $data = array('product_id' => $productId, 'lang_id'=>$langId,'attribute_id'=>$description_code,'value'=>$description.'<html>'.$text.'<html>');
		                $model->setData($data);
		                $saveData = $model->save();

		                $model = $this->postFactory->create();
	                    $data = array('product_id' => $productId, 'lang_id'=>$langId,'attribute_id'=>$sentence2_code,'value'=>$subtitle2.'<html>'.$text2);
	                    $model->setData($data);
	                    $saveData = $model->save();

		                $model = $this->postFactory->create();
	                    $data = array('product_id' => $productId, 'lang_id'=>$langId,'attribute_id'=>$content_code,'value'=>$slide_content.$text_pp);
	                    $model->setData($data);
	                    $saveData = $model->save(); 
					}
				}   	
			}    
		}
    } 
}