<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Post\View;

class Language extends \FishPig\WordPress\Block\Post
{
	public function getLanguageURL()
	{
		$post = $this->getPost();
		$language_url = array();
		if (!empty($post->getMetaValue('url_english_language'))) {
			$language_url['english']=$post->getMetaValue('url_english_language');
		}
		if (!empty($post->getMetaValue('url_german_language'))) {
			$language_url['german']=$post->getMetaValue('url_german_language');
		}
		if (!empty($post->getMetaValue('url_french_language'))) {
			$language_url['french']=$post->getMetaValue('url_french_language');
		}
		if (!empty($post->getMetaValue('url_portuguese_language'))) {
			$language_url['portuguese']=$post->getMetaValue('url_portuguese_language');
		}
		if (!empty($post->getMetaValue('url_spanish_language'))) {
			$language_url['spanish']=$post->getMetaValue('url_spanish_language');
		}
		return $language_url;
	}
}
