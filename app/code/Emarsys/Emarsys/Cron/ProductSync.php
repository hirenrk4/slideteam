<?php
/**
 * @category   Emarsys
 * @package    Emarsys_Emarsys
 * @copyright  Copyright (c) 2017 Emarsys. (http://www.emarsys.net/)
 */
namespace Emarsys\Emarsys\Cron;

use Emarsys\Emarsys\Model\Product as EmarsysProductModel;
use Magento\Store\Model\StoreManagerInterface;
use Emarsys\Emarsys\Model\Logs;

/**
 * Class ProductSync
 * @package Emarsys\Emarsys\Cron
 */
class ProductSync
{
    /**
     * @var EmarsysProductModel
     */
    protected $emarsysProductModel;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Logs
     */
    protected $emarsysLogs;

    /**
     * ProductSync constructor.
     * @param EmarsysProductModel $emarsysProductModel
     * @param StoreManagerInterface $storeManager
     * @param Logs $emarsysLogs
     */
    public function __construct(
        EmarsysProductModel $emarsysProductModel,
        StoreManagerInterface $storeManager,
        Logs $emarsysLogs,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->emarsysProductModel =  $emarsysProductModel;
        $this->storeManager = $storeManager;
        $this->emarsysLogs = $emarsysLogs;
        $this->configWriter = $configWriter;
        $this->_scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        try {
            
            $today_count = $this->_scopeConfig->getValue('feed_count/emarsys_feed/today_count', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $this->configWriter->save('feed_count/emarsys_feed/yesterday_count', $today_count);

            set_time_limit(0);
            $this->emarsysProductModel->consolidatedCatalogExport(\Emarsys\Emarsys\Helper\Data::ENTITY_EXPORT_MODE_AUTOMATIC);
        } catch (\Exception $e) {
            $this->emarsysLogs->addErrorLog(
                $e->getMessage(),
                $this->storeManager->getStore()->getId(),
                'ProductSync::execute()'
            );
        }
    }
}
