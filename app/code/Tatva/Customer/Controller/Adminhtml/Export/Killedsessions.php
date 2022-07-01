<?php

namespace Tatva\Customer\Controller\Adminhtml\Export;

use Magento\Framework\App\Filesystem\DirectoryList;

class Killedsessions extends \Magento\Backend\App\Action
{
    /**
     * @var DirectoryList
     */
    protected $directory;
    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;
    /**
     * @var int|null
     */
    protected $pageSize = null;
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var Product
     */
    private $productHelper;
    /**
     * @var TimezoneInterface
     */
    private $timezone;
    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param int $pageSize
     * @throws FileSystemException
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ProductFactory $productCollection,
        \Tatva\Customer\Model\ResourceModel\Killedsesssions\Collection $adminCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->_fileFactory = $fileFactory;
        $this->productCollection = $productCollection;
        $this->adminCollection = $adminCollection;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }
    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute()
    {
        $adminCollection = $this->adminCollection;
        $filters = $this->getRequest()->getParams();

        //Slected Rows
        if (isset($filters['selected'])) {
            $selected  = $filters['selected'];
            if ($selected != 'false') {
                $selected = implode(',', $selected);
                $adminCollection->getSelect()->where('main_table.id IN (' . $selected . ')');
            }
        }

        $filters = $filters['filters'];
        // id
        if (isset($filters['id'])) {
            $from = $filters['id']['from'];
            if ($from) {
                $adminCollection->getSelect()->where('main_table.id >= ' . $from);
            }

            $to = $filters['id']['to'];
            if ($to) {
                $adminCollection->getSelect()->where('main_table.id <= ' . $to);
            }
        }
        //total_killed
        if (isset($filters['total_killed'])) {
            $from = $filters['total_killed']['from'];
            if ($from) {
                $adminCollection->getSelect()->where('main_table.total_killed >= ' . $from);
            }

            $to = $filters['total_killed']['to'];
            if ($to) {
                $adminCollection->getSelect()->where('main_table.total_killed <= ' . $to);
            }
        }

        //customer_id
        if (isset($filters['customer_id'])) {
            $customer_id = $filters['customer_id'];
            $adminCollection->getSelect()->where("main_table.customer_id LIKE '%" . $customer_id . "%' ");
        }
        //location
        if (isset($filters['location'])) {
            $location = $filters['location'];
            $adminCollection->getSelect()->where("main_table.location LIKE '%" . $location . "%' ");
        }
        //email
        if (isset($filters['email'])) {
            $email = $filters['email'];
            $adminCollection->getSelect()->where("main_table.email LIKE '%" . $email . "%' ");
        }
        //subscription_type
        if (isset($filters['subscription_type'])) {
            $subscription_type = $filters['subscription_type'];
            $adminCollection->getSelect()->where("main_table.subscription_type LIKE '%" . $subscription_type . "%' ");
        }
        
        $filepath = 'export/export-' . md5(microtime()) . '.csv';
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $columns = ['ID', 'Customer Id', 'Customer Email', 'Total Killed', 'Killed At', 'Location', 'Type Of Subscription', 'No Of Downloads'];
        foreach ($columns as $column) {
            $header[] = $column;
        }
        $stream->writeCsv($header);

        foreach ($adminCollection->getData() as $item) {
            $itemData = [];

            $itemData[] = $item['id'];
            $itemData[] = $item['customer_id'];
            $itemData[] = $item['email'];
            $itemData[] = $item['total_killed'];
            $location = str_replace('<br/>', " ", $item['location']);
            $itemData[] = $location;
            $timestamp = str_replace('<br/>', " ", $item['timestamp']);
            $itemData[] = $timestamp;
            $itemData[] = $item['subscription_type'];
            $itemData[] = $item['no_of_downloads'];

            $stream->writeCsv($itemData);
        }
        $stream->unlock();
        $stream->close();
        return $this->_fileFactory->create('export.csv', [
            'type' => 'filename',
            'value' => $filepath,
            'rm' => true
        ], 'var');
    }
}
