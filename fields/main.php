<?php 

//Avoiding Direct File Access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


include_once TMG_PATH .'/fields/gallery.php';

class TMCF_Fields {

	private static $instance;

	public static function get_instance(...$args){
		if (null === self::$instance) {
			self::$instance = new self(...$args);
		}

		return self::$instance;
	}

	public function __construct($postTypes = ['post'] ){
		$this->postTypes = $postTypes;

		add_action( 'admin_print_scripts-post-new.php', [$this, 'post_enqueue']);	
		add_action( 'admin_print_scripts-post.php', [$this, 'post_enqueue']);

		add_action( 'plugins_loaded', [$this, 'get_all_metaboxes'], 99);		
	}



	public function post_enqueue() {
		global $pagenow;

		if ( ( $pagenow == 'post.php' || $pagenow == 'page.php' ) && isset($_GET['post']) && !empty($_GET['post']) ) {

			if ( in_array(get_post_type($_GET['post']), $this->postTypes) ) {
				wp_enqueue_style( 'tm_gallery_style', TMG_URL . '/assets/admin/css/style.css');
				wp_enqueue_script( 'tm_gallery_script', TMG_URL . '/assets/admin/js/main.js', [ 'jquery', 'jquery-ui-sortable' ], '1.0', true );
			}
			
		}
	}

	public function get_settings_data() {
		return get_posts([
			'post_type'		=> 'tmcf_settings',
			'numberposts' 	=> -1,
			'fields' 		=> 'ids'
		]);
	}


	public function get_all_metaboxes() {
		global $pagenow;

		if ( ( $pagenow == 'post.php' || $pagenow == 'page.php' ) && isset($_GET['post']) && !empty($_GET['post']) ) {

			foreach ($this->get_settings_data() as $post_id) {

				$location = !empty(get_post_meta( $post_id, 'tmcf_setting_location', true)) ? explode(',', get_post_meta( $post_id, 'tmcf_setting_location', true)) : [];
				$fields = !empty(get_post_meta( $post_id, 'tmcf_setting_fields', true)) ? explode(',', get_post_meta( $post_id, 'tmcf_setting_fields', true)) : [];

				if ( in_array(get_post_type($_GET['post']), $location) ) {
					new TM_Gallery($post_id, 'Gallery', $location);
				}
			}
		}
	}
}


