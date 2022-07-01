<?php

namespace Tatva\Deleteaccount\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }    

    public function matchFeedback($params)
    {     
        $data='';
        switch ($params['feedback']) {
            case 'option1':
                $data='I just wanted to download free products.';
                break;
            
            case 'option2':
                if(empty($params['industry'])){
                    $data='I need more industry specific design.';
                }else{
                    $data='I need more industry specific design.-'.$params['industry'];
                }                
                break;
            
            case 'option3':
                $data='You don\'t have the designs I am looking for.';
                break;

            case 'option4':
                $data='Price is too high.';
                break;

            case 'option5':
                if(empty($params['comment'])){
                    $data='Others';
                }else{
                    $data='Others-'.$params['comment'];
                }                
                break;
            
            default:
                break;
        }
        return $data;
    }    
}
