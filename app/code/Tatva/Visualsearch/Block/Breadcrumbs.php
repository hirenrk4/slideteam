<?php
namespace Tatva\Visualsearch\Block;

class Breadcrumbs extends \Magento\Catalog\Block\Breadcrumbs
{
    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Catalog\Helper\Data $catalogData, 
            array $data = array()
            ){
        parent::__construct($context, $catalogData, $data);
    }

    /**
     * Preparing layout
     *
     * @return \Magento\Catalog\Block\Breadcrumbs
     */
    protected function _prepareLayout()
    {
        if($this->_storeManager->getStore()->getName() == "Visual Search")
            $label = "Back to Main Page";
        else
            $label = "Home";
        
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __($label),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );

            $title = [];
            $path = $this->_catalogData->getBreadcrumbPath();

            foreach ($path as $name => $breadcrumb) {
                $breadcrumbsBlock->addCrumb($name, $breadcrumb);
                $title[] = $breadcrumb['label'];
            }

            $this->pageConfig->getTitle()->set(join($this->getTitleSeparator(), array_reverse($title)));
        }
        return parent::_prepareLayout();
    }
}