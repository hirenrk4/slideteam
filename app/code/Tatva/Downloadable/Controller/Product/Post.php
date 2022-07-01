<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Tatva\Downloadable\Controller\Product;

use Magento\Review\Controller\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Model\Review;

class Post extends ProductController
{
   private $transportBuilder;
   protected $scopeConfig;

   public function __construct(
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\Registry $coreRegistry,
      \Magento\Customer\Model\Session $customerSession,
      \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
      \Psr\Log\LoggerInterface $logger,
      \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
      \Magento\Review\Model\ReviewFactory $reviewFactory,
      \Magento\Review\Model\RatingFactory $ratingFactory,
      \Magento\Catalog\Model\Design $catalogDesign,
      \Magento\Framework\Session\Generic $reviewSession,
      \Magento\Store\Model\StoreManagerInterface $storeManager,
      \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
      \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Framework\App\ResourceConnection $resource
      
   ) {
      $this->transportBuilder = $transportBuilder;
      $this->scopeConfig = $scopeConfig;
      $this->_resource = $resource;
      $this->_customerSession = $customerSession;
      parent::__construct($context, $coreRegistry, $customerSession, $categoryRepository,$logger,
       $productRepository, $reviewFactory, $ratingFactory,$catalogDesign,
       $reviewSession, $storeManager, $formKeyValidator) ;
   }

   /**
    * Submit new review action
    *
    * @return \Magento\Framework\Controller\Result\Redirect
    * @SuppressWarnings(PHPMD.CyclomaticComplexity)
    * @SuppressWarnings(PHPMD.NPathComplexity)
    */
   public function execute()
   {
      /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
      $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
      if (!$this->formKeyValidator->validate($this->getRequest())) {
         $resultRedirect->setUrl($this->_redirect->getRefererUrl());
         return $resultRedirect;
      }

      $data = $this->reviewSession->getFormData(true);

      if ($data) {
         $rating = [];
         if (isset($data['ratings']) && is_array($data['ratings'])) {
            $rating = $data['ratings'];
         }
      } else {
         $data = $this->getRequest()->getPostValue();
         $rating = $this->getRequest()->getParam('ratings', []);
      }
      $captcha_data = $this->_customerSession->getData('review-form_word')['data'];
      
      if(empty($this->customerSession->getCustomerId()))
      {
         $nickname = $data['nickname'];
         $customeremail = $data['cusemail'];
      }
      else
      {
         $nickname = $this->customerSession->getCustomer()->getName();
         $customeremail = $this->customerSession->getCustomer()->getEmail();
      }

      if (($product = $this->initProduct()) && !empty($data)) {
         /** @var \Magento\Review\Model\Review $review */
         $review = $this->reviewFactory->create()->setData($data);
         $review->unsetData('review_id');
         $review->setData('nickname', $nickname);
         $review->setData('title', $data['detail']);
         $validate = $review->validate();
         if ($validate === true && $captcha_data == $data['captcha']['review-form']) {
            try {
               $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                  ->setEntityPkValue($product->getId())
                  ->setStatusId(Review::STATUS_PENDING)
                  ->setCustomerId($this->customerSession->getCustomerId())
                  ->setStoreId($this->storeManager->getStore()->getId())
                  ->setStores([$this->storeManager->getStore()->getId()])
                  ->save();
               
               foreach ($rating as $ratingId => $optionId) {
                  $this->ratingFactory->create()
                     ->setRatingId($ratingId)
                     ->setReviewId($review->getId())
                     ->setCustomerId($this->customerSession->getCustomerId())
                     ->addOptionVote($optionId, $product->getId());
               }

               $review->aggregate();


               $resource = $this->_resource;
               $tableName = $resource->getTableName('review_detail');
               $connection = $resource->getConnection();
               
               $sql = "Update " . $tableName . " Set customer_email = '".$customeremail."' where review_id = ".$review->getId()."";
               $connection->query($sql);


               $this->messageManager->addSuccess(__('Your review has been submitted for moderation. It will be displayed as soon as it is approved.'));
               $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
               $backendHelper =$objectManager->create('Magento\Backend\Helper\Data');
               $name = $product->getName();
               $ratingvalue = $this->ratingFactory->create()->setRatingId(array_keys($review->getData()['ratings']))->getOptions()[$review->getData()['ratings'][array_keys($review->getData()['ratings'])[0]]]->getData('value');
               $report = [
                  "name" => $name, "review" => $review->getData('detail'), "rating" => $ratingvalue,"productlink" => $product->getProductUrl() , "siteurl" => $backendHelper->getUrl()  . $backendHelper->getAreaFrontName(),"reviewid" => $review->getId()
               ];
               $postObject = new \Magento\Framework\DataObject();
               $postObject->setData($report);

               $transport = $this->transportBuilder->setTemplateIdentifier($this->scopeConfig->getValue('button/review/email_template', 'default'))
                  ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
                  ->setTemplateVars(['data' => $postObject])
                  ->setFrom(['name' => "SlideTeam Support", 'email' => 'support@slideteam.net'])
                  ->addto('support@slideteam.net');
                  $cc_array = ['ron@slideteam.net','geetika.gosain@slideteam.net','sumit.kumar@slidetech.in','ajmohan@gmail.com','uminfy@yahoo.com'];
                  foreach ($cc_array as $cc) {
                    $this->transportBuilder->addCc($cc);
                  }
                  $transport = $this->transportBuilder->addBcc('krunal.vakharia@tatvasoft.com')->getTransport();
               try {
                  $transport->sendMessage();
               } catch (\Exception $e) {
                  $this->reviewSession->setFormData($data);
                  $this->messageManager->addError(__('Unable to send email for your review.'));
               }
               
            } catch (\Exception $e) {
               $this->reviewSession->setFormData($data);
               // $this->messageManager->addError(__('We can\'t post your review right now.'));
            }
         } else {
            $this->reviewSession->setFormData($data);

            if($captcha_data != $data['captcha']['review-form'])
            {
               $this->messageManager->addError(__('Please enter correct captcha.'));
            }

            if (is_array($validate)) {
               foreach ($validate as $errorMessage) {
                  $this->messageManager->addError($errorMessage);
               }
            } else {
               $this->messageManager->addError(__('We can\'t post your review right now.'));
            }
         }
      }
      $redirectUrl = $this->reviewSession->getRedirectUrl(true);
      $resultRedirect->setPath($redirectUrl ?: $this->_redirect->getRedirectUrl());
      return $resultRedirect;
   }
}
