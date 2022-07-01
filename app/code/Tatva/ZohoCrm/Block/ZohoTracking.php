<?php

namespace Tatva\ZohoCrm\Block;


class ZohoTracking extends \Magento\Framework\View\Element\Template {

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $currentGMTDate;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
        ) {
            $this->currentGMTDate = $dateTimeFactory;
            $this->cookieManager = $cookieManager;
            $this->cookieMetadataFactory = $cookieMetadataFactory;
            $this->customerSession = $customerSession;
            parent::__construct(
                $context, $data
            );
    }
    public function getCurrentGmtDate()
    {
        return $this->currentGMTDate->create()->gmtDate('Y-m-d H:i:s');
    }

    public function getZohoCookieData()
    {
        if ($this->cookieManager->getCookie('zoho_customer_data')) {
            return $this->cookieManager->getCookie(
                'zoho_customer_data'
            );
        }
        return false;
    }

    public function customerIsLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    public function check15MinDifference()
    {
        if($this->customerIsLoggedIn() && ($startDate = $this->getZohoCookieData())){
            $currentDate = strtotime($this->getCurrentGmtDate());
            $startDate = strtotime($startDate);

            return (($currentDate-$startDate)/60 >= 15)?1:0;
        }
        return false;
    }
}

    