<?php
namespace Tatva\Formbuild\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;
    protected $postFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Tatva\Formbuild\Model\PostFactory $postFactory
    )
    {
        $this->_urlRewriteFactory = $urlRewriteFactory->create();
        $this->resultPageFactory = $resultPageFactory;
        $this->postFactory = $postFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = (array) $this->getRequest()->getPostValue();
        if (isset($data['image'][0]['name']) && isset($data['image'][0]['tmp_name'])) {
                $data['image'] = $data['image'][0]['name'];
                $this->imageUploader = \Magento\Framework\App\ObjectManager::getInstance()->get('Tatva\Formbuild\ReferenceImageUpload');
                $this->imageUploader->moveFileFromTmp($data['image']);
        } elseif (isset($data['image'][0]['name']) && !isset($data['image'][0]['tmp_name'])) {
                $data['image'] = $data['image'][0]['name'];
        } else {
                $data['image'] = null;
        }
        
        if($data)
        {
            try{ 
                    $id = $data['form_id'];

                    $post = $this->postFactory->create()->load($id);

                    if($data['image'] == null)
                    {
                        $data = array_filter($data, function($value) {return $value !== ''; });
                    }
                    /*var_dump($data);
                    exit();*/

                    $post->setData($data);
                    $post->save();

                    $form_id = $post->getFormId();
                    /*echo $form_id;
                    exit();*/
                    $form = $post->getUrlKey();
                    /*echo $form;
                    exit();*/
                    $form = preg_replace('/[^A-Za-z0-9\-\s]/', '', $form);
                    $form = strtolower(str_replace(' ', '-', trim($form)));
                    $form = preg_replace('/-+/', '-', $form);

                    $urlRewriteData = $this->_urlRewriteFactory->getCollection()->addFieldToFilter(
                        'target_path',
                        'formurl/index/index/form_id/' . $form_id
                    );
                    foreach ($urlRewriteData->getItems() as $rewrite) {
                        $this->deleteItem($rewrite);
                    }

                    $this->_urlRewriteFactory->setStoreId('1');
                    $this->_urlRewriteFactory->setIsSystem(0);
                    $this->_urlRewriteFactory->setRequestPath($form);
                    $this->_urlRewriteFactory->setTargetPath('formurl/index/index/form_id/' . $form_id);
                    $this->_urlRewriteFactory->save();

                    $this->messageManager->addSuccess(__('Successfully saved the item.'));
                    $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                    return $resultRedirect->setPath('*/*/');
            }
            catch(\Exception $e)
            {
                $this->messageManager->addError($e->getMessage());
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', ['form_id' => $post->getFormId()]);
            }
        }

         return $resultRedirect->setPath('*/*/');
    }
    public function deleteItem($item)
    {
        $item->delete();
    }

}