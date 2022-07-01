<?php
namespace Tatva\Downloadable\Block;
class Download extends \Magento\Framework\View\Element\Template
{
  protected $_registry;
  protected $_catalogSession;

  public function __construct
  (
    \Magento\Framework\Registry $registry,
    \Magento\Catalog\Model\Session $catalogSession,
    \Magento\Backend\Block\Template\Context $context,
    array $data = [])
  {
    $this->_registry = $registry;
    $this->_catalogSession = $catalogSession;

    $this->setCurrentProduct();

    parent::__construct($context, $data);
  }

  public function _prepareLayout()
  {
    return parent::_prepareLayout();
  }

  public function setCurrentProduct()
  {
    $currentProduct = null;
    if ($currentProduct == null) {
      return;
    }
    $this->_catalogSession->setData('productId',$currentProduct->getId());
  }
}
