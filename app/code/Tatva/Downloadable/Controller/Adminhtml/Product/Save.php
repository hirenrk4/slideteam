<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\Downloadable\Controller\Adminhtml\Product;

use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends ProductController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (($data = $this->getRequest()->getPostValue()) && ($reviewId = $this->getRequest()->getParam('id'))) {

            

            $review = $this->reviewFactory->create()->load($reviewId);
            if (!$review->getId()) {
                $this->messageManager->addError(__('The review was removed by another user or does not exist.'));
            } else {
                try {
                    $error = false;
                    if(!empty(trim($data['customer_email'])))
                    {
                        if (!\Zend_Validate::is(trim($data['customer_email']), 'EmailAddress')) {
                            $error = true;
                        }
                    }
                    
                    if ($error) {
                        throw new \Exception();
                    }
                    $review->addData($data)->save();

                    $arrRatingId = $this->getRequest()->getParam('ratings', []);
                    /** @var \Magento\Review\Model\Rating\Option\Vote $votes */
                    $votes = $this->_objectManager->create(\Magento\Review\Model\Rating\Option\Vote::class)
                        ->getResourceCollection()
                        ->setReviewFilter($reviewId)
                        ->addOptionInfo()
                        ->load()
                        ->addRatingOptions();
                    foreach ($arrRatingId as $ratingId => $optionId) {
                        if ($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            $this->ratingFactory->create()
                                ->setVoteId($vote->getId())
                                ->setReviewId($review->getId())
                                ->updateOptionVote($optionId);
                        } else {
                            $this->ratingFactory->create()
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->addOptionVote($optionId, $review->getEntityPkValue());
                        }
                    }

                    $review->aggregate();
					
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
					$tableName = $connection->getTableName('review_detail');
					$detail = [
	                  'customer_email' => $data['customer_email'],
	               	];
					$condition = ["detail_id = ?" => $review->getId()];               
               		$connection->getConnection()->update($tableName, $detail, $condition);	
					
                    $this->messageManager->addSuccess(__('You saved the review.'));
                } catch (LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving this review.'));
                }
            }

            $nextId = (int)$this->getRequest()->getParam('next_item');
            if ($nextId) {
                $resultRedirect->setPath('review/*/edit', ['id' => $nextId]);
            } elseif ($this->getRequest()->getParam('ret') == 'pending') {
                $resultRedirect->setPath('*/*/pending');
            } else {
                $resultRedirect->setPath('*/*/');
            }
            return $resultRedirect;
        }
        $resultRedirect->setPath('review/*/');
        return $resultRedirect;
    }
}
