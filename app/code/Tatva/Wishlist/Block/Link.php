<?php

namespace Tatva\Wishlist\Block;

use Magento\Customer\Block\Account\SortLinkInterface;

class Link extends \Magento\Wishlist\Block\Link
{
    protected $_customerSession;
    /**
     * Template name
     *
     * @var string
     */
    protected $_template = 'Magento_Wishlist::link.phtml';

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishlistHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        $this->_wishlistHelper = $wishlistHelper;
         $this->_customerSession = $session;
        parent::__construct($context,$wishlistHelper,$data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_wishlistHelper->isAllow()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->getUrl('favourites');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('My Favourites');
    }

    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    public function isCustomerLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }
}
