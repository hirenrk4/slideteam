<?php
namespace Tatva\Visualsearch\Plugin\Framework\View\Element\Html;

class Link
{
	protected $headerHelper;

	/**
     * @var array
     */
    protected $allowedAttributes = [
        'href',
        'title',
        'charset',
        'name',
        'target',
        'hreflang',
        'rel',
        'rev',
        'accesskey',
        'shape',
        'coords',
        'tabindex',
        'onfocus',
        'onblur', // %attrs
        'id',
        'class',
        'style', // %coreattrs
        'lang',
        'dir', // %i18n
        'onclick',
        'ondblclick',
        'onmousedown',
        'onmouseup',
        'onmouseover',
        'onmousemove',
        'onmouseout',
        'onkeypress',
        'onkeydown',
        'onkeyup', // %events
    ];

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tatva\Theme\Helper\Header $headerHelper,
        array $data = []
    ) {
        $this->headerHelper = $headerHelper;
    }

    public function aftergetLinkAttributes(\Magento\Framework\View\Element\Html\Link $link)
    {
    	$vs_store_key = $this->headerHelper->getConfig('button/config/text') . '/';
        $attributes = [];
        foreach ($this->allowedAttributes as $attribute) {
            $value = $link->getDataUsingMethod($attribute);
            if ($value !== null) {
            	$value = str_replace($vs_store_key, '', $value);
                $attributes[$attribute] = $link->escapeHtml($value);
            }
        }

        if (!empty($attributes)) {
            return $link->serialize($attributes);
        }

        return '';
    }

}