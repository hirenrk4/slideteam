<?php

namespace Tatva\Customer\Framework\View\Element\Html\Link;

class Current extends \Magento\Framework\View\Element\Html\Link\Current {

    /**
     * Default path
     *
     * @var \Magento\Framework\App\DefaultPathInterface
     */
    protected $_defaultPath;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\DefaultPathInterface $defaultPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        array $data = array()) {
        parent::__construct($context, $defaultPath, $data);
    }

    private function getMca() {
        if ($this->_request->getModuleName() == 'wishlist') {
            $routeParts = [
                'module' => 'favourites'
            ];
        } else {
            $routeParts = [
                'module' => $this->_request->getModuleName(),
                'controller' => $this->_request->getControllerName(),
                'action' => $this->_request->getActionName(),
            ];
        }
        $parts = [];
        foreach ($routeParts as $key => $value) {
            if ($this->_request->getModuleName() == 'subscription') {
                $parts[] = $value;
            } else {
                if (!empty($value) && $value != $this->_defaultPath->getPart($key)) {
                    $parts[] = $value;
                }
            }
        }
        return implode('/', $parts);
    }

    public function isCurrent() {
        return $this->getCurrent() || $this->getUrl($this->getPath()) == $this->getUrl($this->getMca());
    }

}
