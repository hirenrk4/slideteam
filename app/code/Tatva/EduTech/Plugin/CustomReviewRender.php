<?php
namespace Tatva\EduTech\Plugin;

class CustomReviewRender {

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
        $this->view = $view;
    }

    public function beforeSetTemplate(
        \Magento\Review\Block\Product\ReviewRenderer $subject,
        $result
    ) {

        if(in_array('catalog_product_view_type_edutech', $this->view->getLayout()->getUpdate()->getHandles())) {
            return 'Tatva_EduTech::product/summary.phtml';
        }
        
        return $result;
    }

}