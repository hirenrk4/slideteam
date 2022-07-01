<?php
namespace Tatva\Captcha\Model;

class DefaultModel extends \Magento\Captcha\Model\DefaultModel
{
    public function __construct
    (
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Captcha\Helper\Data $captchaData,
        \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory,
        $formId
    )
    {
        $this->setDotNoiseLevel(0);
        $this->setLineNoiseLevel(0);
        $this->session = $session;
        $this->captchaData = $captchaData;
        $this->resLogFactory = $resLogFactory;
        $this->formId = $formId;
        parent::__construct($this->session,$this->captchaData,$this->resLogFactory,$this->formId);
    }
}
