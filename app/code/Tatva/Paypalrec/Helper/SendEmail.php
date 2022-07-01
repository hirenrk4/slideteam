<?php

namespace Tatva\Paypalrec\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;

class SendEmail extends AbstractHelper {

    //construct params
    protected $_transportBuilder;
    
    public function __construct(Context $context, TransportBuilder $transportBuilder) {
        $this->_transportBuilder = $transportBuilder;
        parent::__construct($context);
    }

    public function sendEmail($templateId,$storeid,$vars=array(),$identity=array(),$tomail,$toname) {

        if ($templateId && $identity) {
            $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeid])
                    ->setTemplateVars($vars)
                    ->setFrom($identity)
                    ->addTo($tomail, $toname)
                    ->getTransport();
                    try{
                         return $transport->sendMessage();
                    }
                    catch(Exception $e){
                        return $e->getMessage();
                    }
                   }
    }

}
?>