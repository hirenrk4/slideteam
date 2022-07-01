<?php
namespace Tatva\Generalconfiguration\Block;

use Magento\Framework\View\Element\Template\Context;
use Tatva\Generalconfiguration\Helper\Data as GeneralHelper;

class GeneralBlock extends \Magento\Framework\View\Element\Template
{
    /**
     * GeneralHelper
     *
     * @var generalHelper
     */
    public $generalHelper;
    public function __construct(
        Context $context,
        GeneralHelper $generalHelper,
        array $data = array()
    ) {
        $this->generalHelper = $generalHelper;
        parent::__construct($context,$data);
    }
}