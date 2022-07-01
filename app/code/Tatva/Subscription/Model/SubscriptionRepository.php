<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Subscription\Model;
use Tatva\Subscription\Api\SubscriptionInterface;
use Tatva\Subscription\Api\Data\SubscriptionInterface as DataInterface;
use Tatva\Subscription\Model\ResourceModel\Subscription as ResourceSubscription;
use Tatva\Subscription\Model\ResourceModel\Subscription\CollectionSubReportFactory as SubscriptionCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class SubscriptionRepository  implements SubscriptionInterface
{


   protected $resource;


   protected $subscriptionFactory;


   protected $subscriptionCollectionFactory;


   protected $searchResultsFactory;

   protected $dataObjectHelper;

   protected $dataObjectProcessor;

   protected $dataBlockFactory;


   private $storeManager;

   private $collectionProcessor;

    /**
     * @param ResourceBlock $resource
     * @param BlockFactory $blockFactory
     * @param Data\BlockInterfaceFactory $dataBlockFactory
     * @param BlockCollectionFactory $blockCollectionFactory
     * @param Data\BlockSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceSubscription $resource,
        \Tatva\Subscription\Model\SubscriptionFactory $subscriptionFactory,
        \Tatva\Subscription\Api\Data\SubscriptionInterfaceFactory $dataBlockFactory,
        SubscriptionCollectionFactory $subscriptionCollectionFactory,
        \Tatva\Subscription\Api\Data\SubscriptionSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null
        ) {
       $this->resource = $resource;
       $this->subscriptionFactory = $subscriptionFactory;
       $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
       $this->dataObjectHelper = $dataObjectHelper;
       $this->dataBlockFactory = $dataBlockFactory;
       $this->dataObjectProcessor = $dataObjectProcessor;
       $this->storeManager = $storeManager;
        //$this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
   }

   public function save(DataInterface $subscription)
   {
    if (empty($subscription->getStoreId())) {
        $subscription->setStoreId($this->storeManager->getStore()->getId());
    }

    try {
        $this->resource->save($subscription);
    } catch (\Exception $exception) {
        throw new CouldNotSaveException(__($exception->getMessage()));
    }
    return $subscription;
}

    /**
     * Load Block data by given Block Identity
     *
     * @param string $blockId
     * @return Block
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($subscriptionId)
    {

        /*$subscription = $this->subscriptionFactory->create();
        $this->resource->load($subscription, $subscriptionId);
        echo "<pre>";
        print_r($subscription->getId());
        die("hii");
        if (!$subscription->getId()) {
            throw new NoSuchEntityException(__('CMS Block with id "%1" does not exist.', $blockId));
        }
        return $subscription;*/

        $subscription = $this->subscriptionFactory->create()->load($subscriptionId);
        // echo "<pre>";
        // print_r(get_class_methods($subscription));
        // die("<br> in".__FILE__." : ".__LINE__);
        

        // if (!$subscription->getId()) {
        //     throw new NoSuchEntityException(__('CMS Block with id "%1" does not exist.', $blockId));
        // }

        return $subscription;
    }

    /**
     * Load Block data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Magento\Cms\Api\Data\BlockSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var \Magento\Cms\Model\ResourceModel\Block\Collection $collection */
        $collection = $this->subscriptionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /* @var Data\BlockSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete Block
     *
     * @param \Magento\Cms\Api\Data\BlockInterface $block
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(DataInterface $subscription)
    {
        try {
            $this->resource->delete($subscription);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Block by given Block Identity
     *
     * @param string $blockId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($subscriptionId)
    {
        return $this->delete($this->getById($subscriptionId));
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 101.1.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Magento\Cms\Model\Api\SearchCriteria\BlockCollectionProcessor'
                );
        }
        return $this->collectionProcessor;
    }
}