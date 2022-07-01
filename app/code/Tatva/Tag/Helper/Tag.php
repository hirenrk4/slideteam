<?php

namespace Tatva\Tag\Helper;

class Tag extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct(
            $context
        );
    }

	/**
	 * Convert from a tag name into a string we can use in a clean url
	 * @param string $tagName
	 */
    public function tagNameToUrlIdentifier($tagName)
    {
		$identifier = $tagName;

		$identifier = str_replace('-', '@', $identifier);

		$identifier = urldecode($identifier);
		
		return $identifier;
    }
    
    /**
     * Do the reverse of tagNameToUrlIdentifier(...)
     * @param string $identifier
     */
    public function urlIdentifierToTagName($identifier)
    {
		$tagName = $identifier;
		
		// Url decode first
		$tagName = urldecode($tagName);
		
		// Temporarily replace all "--" with a dummy value
		$tagName = str_replace('@', 'XXDUMMYSTRINGXX', $tagName);
		
		// Replace all "-" to spaces
		$tagName = str_replace('-', ' ', $tagName);
		
		//$tagName = str_replace('@', '-', $tagName);
		// Replace dummy value with a "-"
		$tagName = str_replace('XXDUMMYSTRINGXX', '-', $tagName);

		return $tagName;
    }
}
