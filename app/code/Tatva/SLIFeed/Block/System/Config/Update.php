<?php

namespace Tatva\SLIFeed\Block\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Tatva\SLIFeed\FeedManager;

class Update extends Field
{
     /**
     * @var string
     */
    protected $_template = 'Tatva_SLIFeed::system/config/update.phtml';
    
    protected $feedManager;


    public function __construct(Context $context, FeedManager $feedManager, array $data = array()) {
        parent::__construct($context, $data);
        $this->feedManager = $feedManager;
    }
    
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
    
    public function getAjaxUpdateUrl()
    {
        return $this->getUrl('*/system_config_feed/update');
    }
    
    public function getAjaxStatusUpdateUrl()
    {
        return $this->getUrl('*/system_config_feed/status');
    }
    
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'update_button',
                'label' => __('Update Data'),
            ]
        );

        return $button->toHtml();
    }
    
}