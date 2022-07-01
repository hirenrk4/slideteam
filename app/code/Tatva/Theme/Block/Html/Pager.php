<?php
namespace Tatva\Theme\Block\Html;

class Pager extends \Magento\Theme\Block\Html\Pager
{
    public function getPageUrl($page)
    {
        return $this->getPagerUrl([$this->getPageVarName() => $page]);
    }
}