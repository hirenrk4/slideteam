<?php
namespace Tatva\Customer\Block\Form;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement;
use \Magento\Directory\Block\Data;
class Edit extends \Magento\Customer\Block\Form\Edit
{

    protected $_scopeConfig;

    protected $_localeLists;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    protected $_loginCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Mageplaza\SocialLogin\Model\ResourceModel\Social\CollectionFactory $loginCollectionFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->subscriberFactory = $subscriberFactory;
        $this->customerRepository = $customerRepository;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->_scopeConfig = $scopeConfig;
        $this->_localeLists = $localeLists;
        $this->_countryFactory = $countryFactory;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_loginCollectionFactory = $loginCollectionFactory;
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
    }
    
    /**
     * Get country phone code
     *
     * @return string
     * @since 100.1.0
     */
    public function getCountryData()
    {
        $collection = $this->getCountryCollection();

        $foregroundCountries = $this->getTopDestinations();

        $sort = [];
        $options = array();
        $i=0;
        foreach ($collection as $country) {
            $name = (string)$this->_localeLists->getCountryTranslation($country->getCountryId());
            if (!empty($name)) {
                $sort[$name] = $country->getCountryId();
            }
            /*$options[$i]['country_id'] = $country->getCountryId();
            $options[$i]['country_name'] = $name;
            $options[$i]['isd_code'] = $country->getIsdCode();

            $i++;*/
        }
        ksort($sort);
        foreach (array_reverse($foregroundCountries) as $foregroundCountry) {
            $name = array_search($foregroundCountry, $sort);
            unset($sort[$name]);
            $sort = [$name => $foregroundCountry] + $sort;
        }
        foreach ($sort as $label => $value) {
            $country = $this->_countryFactory->create()->loadByCode($value);
            $isd_code = $country->getIsdCode();
            $options[$i]['country_id'] = $value;
            $options[$i]['country_name'] = $label;
            $options[$i]['isd_code'] = $isd_code;
            $i++;
        }
        return $options;
    }

    protected function getTopDestinations()
    {
        $destinations = (string)$this->_scopeConfig->getValue(
            'general/country/destinations',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }

    public function getCountryCollection()
    {
        $collection = $this->getData('country_collection');
        if ($collection === null) {
            $collection = $this->_countryCollectionFactory->create()->loadByStore();
            $this->setData('country_collection', $collection);
        }

        return $collection;
    }

    public function getCustomerLoginType($customerid)
    {
        $collection = $this->_loginCollectionFactory->create();
        $collection->addFieldToSelect('type');
        $collection->addFieldToFilter('customer_id',$customerid);
        $customertype = null;
        foreach ($collection as $data) {
            $customertype = $data['type'];
        }
        return $customertype;
    }
    
}