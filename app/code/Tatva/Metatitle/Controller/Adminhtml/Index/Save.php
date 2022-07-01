<?php

namespace Tatva\Metatitle\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Magento\Framework\App\Action\Action {

    protected $resultPageFactory = false;

    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Tatva\Metatitle\Model\Metatitle $metatitle, \Magento\Framework\Message\ManagerInterface $messageManager, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->metatitle = $metatitle;
        $this->messageManager = $messageManager;
        $this->timezone = $timezone;
    }

    public function execute() {
        $max_count = '';
        $max_count_product = '';
        $temp = '';
        $temp_product = '';
        $metatitle_collection_count_product = $this->metatitle->getCollection();

        // Get Maximum Number of Count from column 'number_of_usage_product'
        $metatitle_collection_count_product->getSelect()->reset(\Zend_Db_Select::COLUMNS)->columns('MAX(number_of_usage_product) as number_of_usage_product');

        foreach ($metatitle_collection_count_product as $c) {
            $max_count_product = $c['number_of_usage_product'];
        }

        $post = (array) $this->getRequest()->getPost('Metatitle');
        $resultRedirect = $this->resultRedirectFactory->create();
        $count = 0;
        if (!empty($post)) {
            $modelCollection = $this->metatitle->getCollection();

            try {
                if ($this->metatitle->getCreatedTime() == NULL || $this->metatitle->getUpdateTime() == NULL) {
                    $this->metatitle->setCreatedTime(time())
                            ->setUpdateTime(time());
                } else {
                    $this->metatitle->setUpdateTime(time());
                }
                $id = $this->getRequest()->getParam('metatitle_id');

                if (!$id) {
                    $count = 0;

                    $lineLower = strtolower($post['metatitle']);

                    foreach ($modelCollection as $m) {
                        $metatitle = $m->getMetatitle();
                        $metatitle = strtolower($metatitle);

                        if (strcmp($metatitle, $lineLower) == '0') {
                            $count++;
                            break;
                        }
                    }

                    if ($count == '0') {
                        if ($max_count_product) {
                            $temp_product = $max_count_product - 1;
                            $this->metatitle->setNumberOfUsageProduct($temp_product);
                        } else {
                            $this->metatitle->setNumberOfUsageProduct('0');
                        }

                        $this->metatitle->load($id);
                        $this->metatitle->addData($post);
                        $this->metatitle->save();
                        // $this->metatitle->setMetatitle($post['metatitle']);
                        // $this->metatitle->save();
                    } else {
                        $this->messageManager->addError(__('Cannot save, metatitle already exists.'));
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                } else {

                    $lineLower = strtolower($post['metatitle']);
                    foreach ($modelCollection as $m) {
                        if ($m->getId() != $id) {
                            $metatitle = $m->getMetatitle();
                            $metatitle = strtolower($metatitle);

                            if (strcmp($metatitle, $lineLower) == '0') {
                                $count++;
                                break;
                            }
                        }
                    }
                    if ($count == '0') {
                        if ($max_count_product) {
                            $temp_product = $max_count_product - 1;
                            $this->metatitle->setNumberOfUsageProduct($temp_product);
                        } else {
                            $this->metatitle->setNumberOfUsageProduct('0');
                        }
                        $this->metatitle->setMetatitle($data['metatitle']);
                        $this->metatitle->save();
                    } else {
                        $this->messageManager->addError(__('Cannot save, metatitle already exists.'));
                        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                        return;
                    }
                }
                $this->messageManager->addSuccess(__('Metatitle was successfully saved'));
                //$this->session->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $this->metatitle->getId()));
                    return;
                }
                $this->_redirect('metatitle/index/index');
                return;
            } catch (Exception $e) {
                $this->messageManager->addError(
                        __('Cannot save, metatitle already exists.')
                );
            }
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('metatitle_id')]);
        }


        return $resultRedirect->setPath('metatitle/index/index');
    }

}
