<?php
namespace Tatva\Formbuild\Block;
 
class Form extends \Magento\Framework\View\Element\Template
{
    public function __construct
    (
        \Tatva\Formbuild\Model\PostFactory $postFactory,
        //\Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        $this->_postFactory = $postFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function getFormId()
    {   
        return $this->getRequest()->getParam('form_id');
    }
    public function getForm()
    {
        $collection = $this->_postFactory->create()->getCollection()->addFieldToFilter('form_id', $this->getFormId());
        return $collection;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl;
    }
    public function getLinkUrl()
    {
        //$url = preg_replace('/[^A-Za-z0-9\-\s]/', '', $string);
        //$url = strtolower(str_replace(' ', '-', trim($url)));
        //$url = preg_replace('/-+/', '-', $url);
        return $this->getBaseUrl();
    }
}
