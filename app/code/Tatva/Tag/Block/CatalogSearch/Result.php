<?php
namespace Tatva\Tag\Block\CatalogSearch;

class Result extends \Magento\CatalogSearch\Block\Result
{
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        $title = $this->getSearchQueryText();
        $this->pageConfig->getTitle()->set($title.' PowerPoint Presentation and Slides | SlideTeam');
        $this->pageConfig->setKeywords($title.', '.$title.' PPT Slides, '.$title.' PowerPoint Templates');
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'popularcategory',
                [
                    'label' => __('Popular Categories'),
                    'title' => __('Popular Categories'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl().'all-powerpoint-categories'
                ]
            );
            $searchKey = preg_replace("/[^a-zA-Z0-9]+/", "", $this->catalogSearchData->getEscapedQueryText());
            if (is_numeric($searchKey[0])) {
                $breadcrumbs->addCrumb(
                    'popularcategoryQueryNum',
                    [
                        'label' => __('0-9'),
                        'title' => __('0-9'),
                        'link' => $this->_storeManager->getStore()->getBaseUrl().'all-powerpoint-categories?t=0-9'
                    ]
                );
            }else{
                $breadcrumbs->addCrumb(
                    'popularcategoryQueryAlpha',
                    [
                        'label' => __(strtoupper($searchKey[0])),
                        'title' => __(strtoupper($searchKey[0])),
                        'link' => $this->_storeManager->getStore()->getBaseUrl().'all-powerpoint-categories?t='.$searchKey[0]
                    ]
                );
            }
            $breadcrumbs->addCrumb(
                'search',
                ['label' => $title, 'title' => $title]
            );
        }
        return $this;
    }
    public function getSearchQueryText()
    {
        return __(ucfirst($this->catalogSearchData->getEscapedQueryText()));
    }
}