<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Tatva\OrderReport\Block\Adminhtml\Edit;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Class GenericButton
 * @package Magento\Customer\Block\Adminhtml\Edit
 */
class Generic
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
        ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
        $this->context = $context;   
    }

    /**
     * Return the customer Id.
     *
     * @return int|null
     */
    public function getObjectId()
    {
        return $this->context->getRequest()->getParam('entity_id');
    }


    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
