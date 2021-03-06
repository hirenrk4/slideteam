<?php
/**
 *
 * DO NOT EDIT THIS FILE
 * Any changes you make to this file will be lost
 * To customise things, create a file at wp-content/themes/fishpig/local.php
 * This file will not be deleted or overwritten and is automatically included at the end of this file
 *
 */
class FishPig_Theme
{
	/*
	 * @var FishPig_Theme
	 */
	static protected $instance;
	
	/*
	 * @var array
	 */
	protected $data = array();
	
	/*
	 *
	 *
	 */
	protected function __construct()
	{
		$this->setupDataFromMagento();

		add_action('after_setup_theme',  array($this, 'onActionAfterSetupTheme'));
		add_action('wp_loaded',          array($this, 'onActionWpLoaded'));
		add_action('widgets_init',       array($this, 'onActionWidgetsInit'));
		add_action('init',               array($this, 'onActionInit'));
		add_filter('redirect_canonical', array($this, 'onFilterRedirectCanonical'));
#		add_filter('preview_post_link',  array($this, 'onFilterPreviewPostLink'), 10, 2);
		add_filter('rest_url',           array($this, 'onFilterRestUrl'));
		add_filter('status_header',      array($this, 'onFilterStatusHeader'), 10, 4);
    add_filter('wp_headers',         array($this, 'onFilterWPHeaders'), 10, 4);
    add_action('wp_footer',          array($this, 'onActionWPFooter'), 12);
		add_action('save_post',          array($this, 'preRenderPostContent'));
		add_action('admin_init',         array($this, 'onAdminInit'));
		
		if ($this->isMagento2()) {
			add_action('save_post', array($this, 'invalidateMagento2FPC'));
			
			$this->initRelatedProducts();
		}
		
		$this->cleanOldFiles();
		$this->includeLocalPhpFile();

		// We have Yoast so lets disable some redirects
    if (isset($GLOBALS['wpseo_rewrite'])) {
      remove_filter('request', array($GLOBALS['wpseo_rewrite'], 'request'));
    }
	}

	/*
	 *
	 *
	 *
	 */
	static public function getInstance()
	{
		if (!self::$instance) {
			($className = __CLASS__) && self::$instance = new $className;
		}

		return self::$instance;
	}

	/*
	 *
	 *
	 *
	 */
	protected function setupDataFromMagento()
	{
		if ($data = get_option('fishpig_magento')) {
			
			if (strlen($data) > 2 && substr($data, 0, 1) === '{' && substr($data, -1) === '}') {
				$this->data = json_decode($data, true);
			}			
		}
	}

	/*
	 *
	 *
	 *
	 */
	public function getMagentoData($key = null, $default = null)
	{
		if ($key === null) {
			return $this->data;
		}
		
		return isset($this->data[$key]) ? $this->data[$key] : $default;
	}
	
	/*
	 *
	 *
	 *
	 */
	public function onActionAfterSetupTheme()
	{
		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		set_post_thumbnail_size(9999, 9999);
		
		add_theme_support('post-formats', array('aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat'));
		
		if (function_exists('show_admin_bar')) {
			show_admin_bar(false);
		}
		
		/* Remove the Emoji JS */
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 ); 
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' ); 
		remove_action( 'wp_print_styles', 'print_emoji_styles' ); 
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		
		remove_filter('template_redirect', 'redirect_canonical');
		remove_action('template_redirect', 'wp_old_slug_redirect');
		
		/* Remove wptexturize to fix shortcodes */
		remove_filter('the_content', 'wptexturize');
	}

	/*
	 *
	 *
	 *
	 */
	public function onActionWpLoaded()
	{
		if ($post_types = get_post_types(array('public' => true, '_builtin' => false))) {
			foreach ( $post_types as $post_type) {
				add_filter("theme_{$post_type}_templates", array($this, 'onFilterThemeTemplates'), 10, 4);
			}
		}
	}

	/*
	 *
	 *
	 *
	 */
	public function onFilterThemeTemplates($page_templates, $wp_theme, $post)
	{
		return array(
			'template-1column' => '1 Column',
			'template-2columns-left' => '2 Columns Left',
			'template-2columns-right' => '2 Columns Right',
			'template-3columns' => '3 Columns',		
			'template-full-width' => 'Full Width',
		) + $page_templates;
	}

	/*
	 *
	 *
	 *
	 */
	public function onActionWidgetsInit()
	{
		if ($this->isMagento1()) {
			register_sidebar(array(
				'name' => __( 'Main Sidebar', 'fishpig' ),
				'id' => 'sidebar-1',
				'description' => 'Add widgets here to appear in your left Magento sidebar.',
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<h2 class="widget-title">',
				'after_title' => '</h2>',
			));
			
			register_sidebar(array(
				'name' => __( 'Right Sidebar', 'fishpig' ),
				'id' => 'sidebar-2',
				'description' => 'Add widgets here to appear in your right Magento sidebar.',
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<h2 class="widget-title">',
				'after_title' => '</h2>',
			));
		}
		else {
			register_sidebar(array(
				'name' => __( 'Main Sidebar', 'fishpig' ),
				'id' => 'sidebar-main',
				'description' => 'Add widgets here to appear in your left Magento sidebar.',
				'before_widget' => '<aside id="%1$s" class="widget %2$s">',
				'after_widget' => '</aside>',
				'before_title' => '<h2 class="widget-title">',
				'after_title' => '</h2>',
			));
		}
		

    global $wp_widget_factory;
    
    remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
	}

	/*
	 *
	 *
	 *
	 */
	public function onActionInit()
	{
		add_rewrite_rule('^wordpress/post/preview/?$', 'index.php', 'top');
	}

	/*
	 *
	 *
	 *
	 */
	public function onFilterRedirectCanonical($redirect_url)
	{
		return is_404() ? false : $redirect_url;
	}

	/*
	 *
	 *
	 *
	 */
	public function preRenderPostContent($post_id)
	{
  	try {
    	$post    = get_post($post_id);
      $content = apply_filters('the_content', $post->post_content);

#    	if (function_exists('do_blocks')) {
#      	$content = do_blocks($content);
#    	}

    	if (!empty($GLOBALS['wp_embed'])) {
        $content = $GLOBALS['wp_embed']->autoembed($content);
      }
      
      // Auto include the related products shortcode
      if (class_exists('FishPig_RelatedProducts')) {
        if ((int)get_option('fprp_autoinclude', 1) === 1) {
          $content .= '[related_products]';
        }
      }
      
      update_post_meta($post_id, '_post_content_rendered', $content);
    }
    catch (Exception $e) {

    }
	}
	
	/*
	 *
	 *
	 *
	 */
	public function invalidateMagento2FPC($post_id)
	{
		// If this is just a revision, don't do anything
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
	
		// Make an invalidation call to Magento
		$salt = get_option( 'fishpig_salt' );
		
		if (!$salt) {
			$salt = wp_generate_password( 64, true, true );
			update_option( 'fishpig_salt', $salt );
		}
	
		$nonce_tick = ceil(time() / ( 86400 / 2 ));
	
		$action = 'invalidate_' . $post_id;
	
		$nonce = substr( hash_hmac( 'sha256', $nonce_tick . '|fishpig|' . $action, $salt ), -12, 10 );
	
		wp_remote_get( home_url( '/wordpress/post/invalidate?id=' . $post_id . '&nonce=' . $nonce ) );
	}

	/*
	 *
	 *
	 *
	 */
	public function onFilterPreviewPostLink($pl, $post)
	{
		if (strpos($pl, 'nonce') !== false) {
			if (preg_match('/nonce=([a-z0-9]{10})/', $pl, $matches)) {
				$pl = str_replace($matches[1], substr(wp_hash(wp_nonce_tick()."|post_preview_{$post->ID}|0|", 'nonce'), -12, 10), $pl);
			}
		}
		return $pl . '&fishpig=' . time();
	}

	/*
	 *
	 *
	 *
	 */
	public function onFilterRestUrl($rest)
	{
		$find   = '/wp-json/';
		$pos    = strpos($rest, $find);
		$extra  = '';
	
		if ($pos !== false && strlen($rest) > $pos+strlen($find)) {
			$extra = substr($rest, $pos+strlen($find));
		}
	
		return get_option('siteurl') . '/index.php?rest_route=/' . ltrim($extra, '/');
		return get_option('siteurl') . '/index.php/wp-json/' . $extra;
	}

	/*
	 *
	 *
	 *
	 */
	public function onFilterStatusHeader($status_header, $code, $description, $protocol)
	{
		if ((int)$code === 404) {
			return '';
		}
		
		return $status_header;
	}

  /*
   *
   *
   * @param  $headers array
   * @return array
   */	
	public function onFilterWPHeaders($headers)
	{
  	if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'text/html') !== false) {
    	unset($headers['Content-Type']);
    }
    
    return $headers;
	}

	/*
	 *
	 *
	 *
	 */
	public function includeLocalPhpFile()
	{
		$localFile = __DIR__ . DIRECTORY_SEPARATOR . 'local.php';

		if (is_file($localFile)) {
			include($localFile);
		}
		
		return $this;
	}

	/*
	 *
	 *
	 *
	 */
	protected function cleanOldFiles()
	{
		$files = array(
			__DIR__ . '/cpt.php',
		);
		
		foreach($files as $file) {
			if (is_file($file)) {
				@unlink($file);
			}
		}
	}

	/*
	 *
	 *
	 *
	 */
	protected function initRelatedProducts()
	{
		// Related Products
		if (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'related-products.php')) {
			include(__DIR__ . DIRECTORY_SEPARATOR . 'related-products.php');	
		}
		else {
			add_action('add_meta_boxes', array($this, 'onActionAddMetaBoxesRelatedProducts'));
		}
	}

	/*
	 *
	 *
	 *
	 */
	public function onActionAddMetaBoxesRelatedProducts()
	{
		add_meta_box(
			'fishpig',
			'Related Products',
			function() {
				?>
				<p>Link your WordPress posts (any post type) with Magento products using the new <a href="https://fishpig.co.uk/magento/wordpress-integration/related-products/" target="_blank">Related Products</a> module by FishPig.</p>
				<button onclick="window.open('https://fishpig.co.uk/magento/wordpress-integration/related-products/');" type="button" class="components-button is-button is-default is-primary is-large">View Module</button>
				<?php
			}
		);
	}

	/*
   *
   *
   *
   */
	public function onActionWPFooter()
	{
    wp_deregister_script('wp-embed');
    
  	// Divi
    if (isset($_GET['et_fb'])) {
      wp_dequeue_style('wp-auth-check');
      wp_dequeue_script('wp-auth-check');
      remove_action('wp_print_footer_scripts', 'et_fb_output_wp_auth_check_html', 5);
    }
	}

	/*
	 *
	 *
	 * @return bool
	 */
	public function isMagento1()
	{
		return !$this->isMagento2();
	}

	/*
	 *
	 *
	 * @return bool
	 */
	public function isMagento2()
	{
		return (int)$this->getMagentoData('version') === 2;
	}
	
	/*
	 *
	 *
	 * @return $this
	 */
	public function onAdminInit()
	{
  	/*
		register_setting( 'reading', 'custom_404_page_id', 'esc_attr' );
		
		add_settings_field(
			'custom_404_page_id',
			'<label for="custom_404_page_id">' . __( 'Custom Magento 404 Page' , 'custom_404_page_id' ) . '</label>',
			(function() {
        echo wp_dropdown_pages(array(
  				'name' => 'custom_404_page_id',
  				'echo' => 0,
  				'show_option_none' => __( '&mdash; Select &mdash;' ),
  				'option_none_value' => '0',
  				'selected' => get_option( 'custom_404_page_id' ),
        ));       
#        echo '<p class="description">It is up to search engines to honor this request.</p>';
			}),
			'reading'
		);
		*/
		
		return $this;
	}
}

/*
 * Create the object
 * This will setup the actions automatically
 *
 */
if (basename(__DIR__) !== 'wptheme') {
	FishPig_Theme::getInstance();
}
else {
	/*
	 * If here, this is probably the Magento compiler
	 * We don't want this to run in Magento as it's a WordPress file
	 */
}
