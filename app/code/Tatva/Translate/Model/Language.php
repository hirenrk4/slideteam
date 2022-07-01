<?php
namespace Tatva\Translate\Model;

class Language extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'languages';

    protected function _construct()
    {
        $this->_init('Tatva\Translate\Model\ResourceModel\Language');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}