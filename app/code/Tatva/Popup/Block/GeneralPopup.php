<?php
namespace Tatva\Popup\Block;

class GeneralPopup extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Tatva\Popup\Model\Popup $popupModel,
        array $data = array()   
    ) {     
        $this->popupModel = $popupModel;
        parent::__construct($context, $data);
    }

   
    public function getPopupContent($identifier)
    {
        $popupData = null;
        $popupCollection = $this->popupModel->getCollection()->addFieldToFilter('identifier',$identifier)->addFieldToFilter('status','1');        
        if($popupCollection->getSize() > 0) 
        {
            foreach ($popupCollection as $item) {
                $popupData = $item->getData();
            }
        }
        return $popupData;
    }
}