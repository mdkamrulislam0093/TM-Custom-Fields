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

				foreach ($fields as $field) {
					add_meta_box(
						sprintf('tmcf_%s', $field['name']),
						$field['name'],
						[$this, $field['type']],
						$location,
						'normal',
						'core',
						$field
					);
				}

			}
		}
	}

	public function text($post, $args) {
		$text = !empty(get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true )) ? get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true ) : '';
	?>
		<div class="text_wrapper">
			<input class="widefat" type="text" name="<?= $args['args']['key']; ?>" value="<?= $text; ?>">
		</div>
	<?php 
	}

	public function tel($post, $args) {
		$tel = !empty(get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true )) ? get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true ) : '';
	?>
		<div class="text_wrapper">
			<input class="widefat" type="tel" name="<?= $args['args']['key']; ?>" value="<?= $tel; ?>">
		</div>
	<?php 
	}

	public function email($post, $args) {
		$email = !empty(get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true )) ? get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true ) : '';
	?>
		<div class="text_wrapper">
			<input class="widefat" type="email" name="<?= $args['args']['key']; ?>" value="<?= $email; ?>">
		</div>
	<?php 
	}

	public function number($post, $args) {
		$number = !empty(get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true )) ? get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true ) : '';
	?>
		<div class="text_wrapper">
			<input class="widefat" type="number" name="<?= $args['args']['key']; ?>" value="<?= $number; ?>">
		</div>
	<?php 
	}

	public function color($post, $args) {
		$color = !empty(get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true )) ? get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true ) : '';
	?>
		<div class="text_wrapper">
			<input type="color" name="<?= $args['args']['key']; ?>" value="<?= $color; ?>">
		</div>
	<?php 
	}

	public function gallery($post, $args) {
		$gallery = !empty(get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true )) ? explode(',', get_post_meta( $post->ID, sprintf('tm_%s', $args['args']['key']), true )) : [];
		?>
		<div class="gallery_wrapper" data-name="<?= $args['args']['key']; ?>">
			<div class="img_box_container">
			<?php foreach ($gallery as $item): 
				$image_url = wp_get_attachment_image_src( $item );
				?>
					<div class="gallery_single_row">
						<div class="gallery_area image_container">
							<input class="meta_image_id" value="<?= $item; ?>" type="hidden" name="<?= $args['args']['key']; ?>[]" />
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
		<?php		
	}


	public function save_fields($post_id) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );
		
		if ( $is_autosave || $is_revision ) {
				return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}


		foreach ($this->get_settings_data() as $settings_id) {

			$fields = !empty(get_post_meta( $settings_id, 'tmcf_setting_fields', true)) ? json_decode(get_post_meta( $settings_id, 'tmcf_setting_fields', true), true) : [];
			$location = !empty(get_post_meta( $settings_id, 'tmcf_setting_location', true)) ? explode(',', get_post_meta( $settings_id, 'tmcf_setting_location', true)) : [];

			if ( isset($_POST['post_type']) && in_array($_POST['post_type'], $location) ) {
				foreach ($fields as $field) {
					// Gallary
					if ( isset($_POST[$field['key']]) && !empty($_POST[$field['key']]) && $field['type'] == 'gallery' ) {
						$gallery = implode(',', $_POST[$field['key']]);
						update_post_meta( $post_id, sprintf('tm_%s', $field['key']), $gallery);
					} 

					// Text, Number, Tel, Email, Color
					if ( isset($_POST[$field['key']]) && !empty($_POST[$field['key']]) && in_array($field['type'], ['text', 'number', 'tel', 'email', 'color']) ) {
						update_post_meta( $post_id, sprintf('tm_%s', $field['key']), $_POST[$field['key']]);
					} 


				}
			}

		}

	}
}


