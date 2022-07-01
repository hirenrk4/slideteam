<?php
namespace Tatva\Catalog\Model;


class Productdownloadhistory extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'productdownload_history';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Backend\Model\SessionFactory $adminsession,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
        ) {
        $this->customerSession = $customerSession;
        $this->adminsession = $adminsession;
            //$this->CatalogMysql4ProductdownloadhistorylogCollectionFactory = $CatalogMysql4ProductdownloadhistorylogCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
            );
    }


    public function _construct()
    {   
        $this->_init('Tatva\Catalog\Model\ResourceModel\Productdownloadhistory');
    }


    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }


    public function getcustomerID()
    {
        $admin =  $this->adminsession->create();
        $customerData=$admin->getData();
        return $customerData['customer_data']['customer_id'];
    }


}