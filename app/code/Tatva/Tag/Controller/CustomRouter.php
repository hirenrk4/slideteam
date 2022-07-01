<?php
namespace Tatva\Tag\Controller;

class CustomRouter implements \Magento\Framework\App\RouterInterface
{
  const URL_SUFFIX = '-powerpoint-templates-ppt-slides-images-graphics-and-themes';
   protected $actionFactory;
   protected $_response;
   public function __construct(
       \Magento\Framework\App\ActionFactory $actionFactory,
       \Magento\Framework\App\ResponseInterface $response,
       \Tatva\Tag\Helper\Tag $taghelper,
       \Tatva\Tag\Model\Tag $tagModel,
       \Magento\Framework\Registry $registry
   ) {
       $this->actionFactory = $actionFactory;
       $this->_response = $response;
       $this->_taghelper = $taghelper;
       $this->_tagModel = $tagModel;
       $this->_registry = $registry;
   }
   public function match(\Magento\Framework\App\RequestInterface $request)
   {
        // Extract the tag name from the url
        $arr = explode('/',trim($request->getPathInfo(), '/'));
        $arrCount = count($arr);

        $urlArr = current($arr);
        if($arrCount>1){
          $arr[1] = strtolower($arr[1]);
        }
        //$url = current(explode('-',end($arr)));
        $url = current(explode(self::URL_SUFFIX,end($arr)));

        $checkTagUrl = $url.self::URL_SUFFIX;

        if(($arrCount>1) && (strpos($arr[1],self::URL_SUFFIX) !== false) && ($checkTagUrl != $arr[1])){
          return false;
        }
       
        $identifier = $urlArr.'/'.$url;

            $parts = explode('/', $identifier);

            if (count($parts)!=2)
            {
              return false;
            }
            
            if (trim(strtolower($parts[0]))!='tag')
            {
              return false;
            }
            $tagName = $this->_taghelper->urlIdentifierToTagName($parts[1]);
       $tag = $this->_tagModel->load($tagName,'name');

    
              if(!$tag->getId())
              {
                return false;
              }
                
              // The loadbyname method does not populate store visibility, so load again using the load method
              $tag = $this->_tagModel->load($tag->getId());
              
              // isAvailableInStore is not available in Mage 1.3.*
              if(!$tag->getId() /*|| !$tag->isAvailableInStore()*/)
              {
                return false;
              }

           if(strpos($identifier, 'tag') !== false) {
              $request->setModuleName('tag')-> //module name
              setControllerName('index')-> //controller name
              setActionName('index')-> //action name
              setParam('tagId', $tag->getId()); //custom parameters
              if(!$this->_registry->registry('current_tag'))
              {
               $this->_registry->register('current_tag', $tag);
              }
           } else {
               return false;
           }
              
       return $this->actionFactory->create(
           'Magento\Framework\App\Action\Forward',
           ['request' => $request]
       );
   }
}
?>