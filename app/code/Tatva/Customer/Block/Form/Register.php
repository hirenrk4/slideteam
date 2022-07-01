<?php
namespace Tatva\Customer\Block\Form;

use Magento\Customer\Model\AccountManagement;

class Register extends \Magento\Customer\Block\Form\Register
{

    protected $_scopeConfig;

    protected $_localeLists;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_localeLists = $localeLists;
        $this->_countryFactory = $countryFactory;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $moduleManager,
            $customerSession,
            $customerUrl,
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
    
}