<?php 

//Avoiding Direct File Access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// include_once TMG_PATH .'/fields/gallery.php';

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

		add_action( 'add_meta_boxes', [$this, 'meta_boxes']);
		add_action( 'save_post', [$this, 'save_fields'] );
	}

	public function post_enqueue() {
		global $pagenow;

		if ( ( $pagenow == 'post.php' || $pagenow == 'page.php' ) && isset($_GET['post']) && !empty($_GET['post']) ) {

			if ( in_array(get_post_type($_GET['post']), $this->postTypes) ) {
				wp_enqueue_style( 'tm_gallery_style', TMG_URL . 'assets/admin/css/style.css');
				wp_enqueue_script( 'tm_gallery_script', TMG_URL . 'assets/admin/js/main.js', [ 'jquery', 'jquery-ui-sortable' ], '1.0', true );			}
			
		}
	}

	public function get_settings_data() {
		return get_posts([
			'post_type'		=> 'tmcf_settings',
			'numberposts' 	=> -1,
			'fields' 		=> 'ids'
		]);
	}

	public function meta_boxes() {
		foreach ($this->get_settings_data() as $post_id) {
			$location = !empty(get_post_meta( $post_id, 'tmcf_setting_location', true)) ? explode(',', get_post_meta( $post_id, 'tmcf_setting_location', true)) : [];
			$fields = !empty(get_post_meta( $post_id, 'tmcf_setting_fields', true)) ? json_decode(get_post_meta( $post_id, 'tmcf_setting_fields', true), true) : [];

			if ( isset($_GET['post']) && in_array(get_post_type($_GET['post']), $location) ) {
				$meta_id = sprintf('tmcf_%s', $post_id);

				add_meta_box(
					$meta_id,
					get_the_title( $post_id ),
					[$this, 'display_fields'],
					$location,
					'normal',
					'core',
					$fields
				);
			}
		}
	}

	public function display_fields($post, $args) { 
		wp_nonce_field( basename(__FILE__), 'tmsf_nonce' );
		$result = empty(get_post_meta( $post->ID, 'tmsf', true )) ? [] : json_decode(get_post_meta( $post->ID, 'tmsf', true ), true);

	 	if ( !empty($args['args']) ): ?>
			<div class="tmcf_field_wrapper">
				<?php foreach ($args['args'] as $key => $field) {
					$field_name = sprintf('%s[%s]', $args['id'], $field['key']);

					if ( in_array($field['type'], ['text', 'number', 'tel', 'email', 'color']) ) {
						$val = empty($result[$field['key']]) ? '' : $result[$field['key']];
					?>
						<div class="tmcf_field text">
							<label><?= $field['name']; ?></label>
							<input type="<?= $field['type'] ?>" class="widefat" name="<?= $field_name; ?>" value="<?= $val; ?>">
						</div>
					<?php 
					}

					if ( $field['type'] == 'gallery' ) { 
						$gallery = empty($result[$field['key']]) ? [] : $result[$field['key']];
						?>
						<div class="tmcf_field gallery">
							<label><?= $field['name']; ?></label>
							<div class="gallery_wrapper" data-name="<?= $field_name; ?>">
								<div class="img_box_container">
								<?php foreach ($gallery as $item): 
									$image_url = wp_get_attachment_image_src( $item );
									?>
										<div class="gallery_single_row">
											<div class="gallery_area image_container">
												<input class="meta_image_id" value="<?= $item; ?>" type="hidden" name="<?= $field_name; ?>[]" />
												<img class="gallery_img_url" src="<?= $image_url[0]; ?>" height="55" width="55"/>
											</div>
											<span class="button remove" title="Remove">
												<svg width="16" height="16">
												  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/>
												  <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
												</svg>
											</span>
											<div class="clear"></div>
										</div>	
								<?php endforeach ?>
								</div>

								<div class="master_box">
									<div class="img_box_container"></div>
								</div>
								<div class="add_gallery_single_row">
								  <input class="button add" type="button" value="+" title="Add image"/>
								</div>
							</div>
						</div>
					<?php }
				} ?>			
			</div>
		<?php endif ?>
		<?php 
	}



	public function save_fields($post_id) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST[ 'tmsf_nonce' ] ) && wp_verify_nonce( $_POST[ 'tmsf_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
		
		if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
				return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ($this->get_settings_data() as $settings_id) {

			$fields = !empty(get_post_meta( $settings_id, 'tmcf_setting_fields', true)) ? json_decode(get_post_meta( $settings_id, 'tmcf_setting_fields', true), true) : [];
			$location = !empty(get_post_meta( $settings_id, 'tmcf_setting_location', true)) ? explode(',', get_post_meta( $settings_id, 'tmcf_setting_location', true)) : [];

			if ( isset($_POST['post_type']) && in_array($_POST['post_type'], $location) ) {
				$meta_id = sprintf('tmcf_%s', $settings_id);

				if ( isset($_POST[$meta_id]) && !empty($_POST[$meta_id]) ) {
					update_post_meta( $post_id, 'tmsf', json_encode($_POST[$meta_id]));
				}
			}

		}

	}
}


