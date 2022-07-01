<?php

namespace Tatva\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Address\Mapper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Customer;

class PersonalInfo extends \Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo
{
	
	/**
     * Interval in minutes that shows how long customer will be marked 'Online'
     * since his last activity. Used only if it's impossible to get such setting
     * from configuration.
     */
    const DEFAULT_ONLINE_MINUTES_INTERVAL = 15;

    /**
     * Customer
     *
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $customer;

    /**
     * Customer log
     *
     * @var \Magento\Customer\Model\Log
     */
    protected $customerLog;

    /**
     * Customer logger
     *
     * @var \Magento\Customer\Model\Logger
     */
    protected $customerLogger;

    /**
     * Customer registry
     *
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * Account management
     *
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * Customer group repository
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * Customer data factory
     *
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * Address helper
     *
     * @var \Magento\Customer\Helper\Address
     */
    protected $addressHelper;

    /**
     * Date time
     *
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Address mapper
     *
     * @var Mapper
     */
    protected $addressMapper;

    /**
     * Data object helper
     *
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;
    protected $_resource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Registry $registry
     * @param Mapper $addressMapper
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Logger $customerLogger
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
	

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
        AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Registry $registry,
        Mapper $addressMapper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Logger $customerLogger,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
	)
	{
		$this->_resource = $resource;
		$this->coreRegistry = $registry;
        $this->accountManagement = $accountManagement;
        $this->groupRepository = $groupRepository;
        $this->customerDataFactory = $customerDataFactory;
        $this->addressHelper = $addressHelper;
        $this->dateTime = $dateTime;
        $this->addressMapper = $addressMapper;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerLogger = $customerLogger;

        parent::__construct($context, $accountManagement,$groupRepository,$customerDataFactory,$addressHelper,$dateTime,$registry,$addressMapper,$dataObjectHelper,$customerLogger,$data);
	}

	

	public function getDownloadCount()
    {
        $customerid = $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $connection = $this->_resource->getConnection();
        $sql = "Select count(*) as total_download from productdownload_history_log where customer_id = ".$customerid;
        $result = $connection->fetchAll($sql);
        return $result[0]['total_download'];        
    }
}