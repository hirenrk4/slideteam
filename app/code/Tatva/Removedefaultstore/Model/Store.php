<?php 

namespace Tatva\Removedefaultstore\Model;

class Store extends \Magento\Store\Model\Store 
{
    public function __construct(
        \Magento\Framework\Model\Context $context, 
        \Magento\Framework\Registry $registry, 
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory, 
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, 
        \Magento\Store\Model\ResourceModel\Store $resource,
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase, 
        \Magento\Framework\App\Cache\Type\Config $configCacheType, 
        \Magento\Framework\UrlInterface $url, 
        \Magento\Framework\App\RequestInterface $request, 
        \Magento\Config\Model\ResourceModel\Config\Data $configDataResource, 
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config, 
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Framework\Session\SidResolverInterface $sidResolver, 
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Session\SessionManagerInterface $session, 
        \Magento\Directory\Model\CurrencyFactory $currencyFactory, 
        \Magento\Store\Model\Information $information, 
        $currencyInstalled, 
        \Magento\Store\Api\GroupRepositoryInterface $groupRepository, 
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository, 
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, 
        $isCustomEntryPoint = false, 
        array $data = []
    ) {
        $this->_coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->_config = $config;
        $this->_url = $url;
        $this->_configCacheType = $configCacheType;
        $this->_request = $request;
        $this->_configDataResource = $configDataResource;
        $this->_isCustomEntryPoint = $isCustomEntryPoint;
        $this->filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->_sidResolver = $sidResolver;
        $this->_httpContext = $httpContext;
        $this->_session = $session;
        $this->currencyFactory = $currencyFactory;
        $this->information = $information;
        $this->_currencyInstalled = $currencyInstalled;
        $this->groupRepository = $groupRepository;
        $this->websiteRepository = $websiteRepository;
        parent::__construct(
            $context, 
            $registry, 
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $coreFileStorageDatabase, $configCacheType, $url, $request, $configDataResource,
            $filesystem, $config, $storeManager, $sidResolver, $httpContext, $session, $currencyFactory, $information,
            $currencyInstalled, $groupRepository, $websiteRepository, $resourceCollection, $isCustomEntryPoint,
            $data
        );
    }
    
    /**
     * Add store code to url in case if it is enabled in configuration
     *
     * @param   string $url
     * @return  string
     */

    protected function _updatePathUseStoreView($url)
    {
        $defaultStoreId = $this->_storeManager->getWebsite()->getDefaultGroup()->getDefaultStoreId();
        $currentStoreId = $this->getStoreId();
                
        if ($this->isUseStoreInUrl()) {
            if($this->getCode() == 'default'){
                $url .= '/';
            }else{
                $url .= $this->getCode() . '/';
            }

        }
        return $url;
    }
}

?>