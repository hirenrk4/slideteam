<?php
namespace Tatva\Loginpopup\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerSignout implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    public function __construct(
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->generic = $generic;
    }
    /**
      * Run couple of 'php' codes after customer logs in
      *
      * @param \Magento\Framework\Event\Observer $observer
      */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->generic->getRegister()){
            $this->generic->unsRegister();
        };
    }

    public function customerLoggedin($observer){
        $myValue =  "Currently Loggedin";
        $this->generic->setCurrentLogin($myValue);
        return $this;
    }
    
}