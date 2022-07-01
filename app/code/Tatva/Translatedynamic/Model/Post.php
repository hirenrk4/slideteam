<?php
namespace Tatva\Translatedynamic\Model;

class Post extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'multilanguage_data';

    protected function _construct()
    {
        $this->_init('Tatva\Translatedynamic\Model\ResourceModel\Post');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}