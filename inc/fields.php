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

		if ( !empty(get_current_screen()) && in_array(get_current_screen()->post_type, $this->postTypes) ) {
			wp_enqueue_style( 'tm_gallery_style', TMG_URL . 'assets/admin/css/style.css');
			wp_enqueue_script( 'tm_gallery_script', TMG_URL . 'assets/admin/js/main.js', [ 'jquery', 'jquery-ui-sortable' ], '1.0', true );
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

			if ( !empty( $location ) && !empty( $fields) ) {
				$meta_id = sprintf('tmcf_%s', $post_id);

				add_meta_box(
					$meta_id,
					get_the_title( $post_id ),
					[$this, 'display_fields'],
					$location,
					'normal',
					'high',
					$fields
				);
			}
		}
	}

	public function display_fields($post, $args) { 
		wp_nonce_field( basename(__FILE__), 'tmcf_nonce' );

		$result = empty(get_post_meta( $post->ID, 'display_tmcf', true )) ? [] : json_decode(get_post_meta( $post->ID, 'display_tmcf', true ), true);

	 	if ( !empty($args['args']) ): ?>
			<div class="tmcf_field_wrapper">
				<?php foreach ($args['args'] as $key => $field) {
					$field_name = sprintf('%s[%s]', 'tmcf', $field['key']);
					$placeholder = empty($field['placeholder']) ? '' : $field['placeholder'];					
					$copy_desc = sprintf('<div class="copy-key-wrap"><span class="copy-key">[tmcf key="%s" id="%s"]</span><span class="dashicons dashicons-admin-page"></span></div>', $field['key'], $post->ID);

					/**
					* 
					* Text, Number, Tel, Email, Color Field Layout
					* 
					**/

					if ( in_array($field['type'], ['text', 'number', 'tel', 'email', 'color']) ) {
						$val = empty($result[$field['key']]) ? '' : $result[$field['key']];
					?>
						<div class="tmcf_field <?php echo $field['type'] ?>">
							<label><?php echo $field['name']; ?></label>
							<input type="<?php echo $field['type'] ?>" class="widefat" name="<?php echo $field_name; ?>" value="<?php echo $val; ?>" placeholder="<?php echo $placeholder; ?>">
							<?php echo $copy_desc; ?>
						</div>
					<?php 
					}


					/**
					* 
					* Select Field Layout
					* 
					**/

					if ( $field['type'] == 'select' ) {
						$select_val = empty($result[$field['key']]) ? '' : $result[$field['key']];
					?>
						<div class="tmcf_field <?php echo $field['type'] ?>">
							<label><?php echo $field['name']; ?></label>
							<div>
								<?php if ( !empty($field['option']) ): ?>
									<select name="<?php echo $field_name ?>">
										<option value="">Select</option>
									<?php foreach ($field['option'] as $option): ?>
										<option value="<?php echo $option['value']; ?>" <?php echo selected( $select_val, $option['value'] ); ?>><?php echo $option['name']; ?></option>
									<?php endforeach ?>
									</select>
								<?php else: ?>

								<?php endif ?>
							</div>
							<?php echo $copy_desc; ?>
						</div>
					<?php 
					}


					/**
					* 
					* Radio Field Layout
					* 
					**/

					if ( $field['type'] == 'radio' ) {
						$radio_val = empty($result[$field['key']]) ? '' : $result[$field['key']];
					?>
						<div class="tmcf_field <?php echo $field['type'] ?>">
							<label><?php echo $field['name']; ?></label>
							<ul class="radio_val">
								<?php if ( !empty($field['option']) ): 
									foreach ($field['option'] as $radio) { ?>
										<li>
											<label>
												<input type="radio" name="<?php echo $field_name ?>" value="<?php echo $radio['value']; ?>" <?php echo checked( $radio_val, $radio['value'] ); ?>>
												<?php echo $radio['name']; ?>
											</label>
										</li>
									<?php }
									?>
								<?php endif ?>
							</ul>
							<?php echo $copy_desc; ?>
						</div>
					<?php 
					}


					/**
					* 
					* Checkbox Field Layout
					* 
					**/

					if ( $field['type'] == 'checkbox' ) {
						$checkbox_val = empty($result[$field['key']]) ? [] : $result[$field['key']];
					?>
						<div class="tmcf_field <?php echo $field['type'] ?>">
							<label><?php echo $field['name']; ?></label>
							<ul class="checkbox_val">
								<?php if ( !empty($field['option']) ): 
									foreach ($field['option'] as $checkbox) { ?>
										<li>
											<label>
												<input type="checkbox" name="<?php echo $field_name ?>[]" value="<?php echo $checkbox['value']; ?>" <?php echo checked( in_array($checkbox['value'], $checkbox_val), 1 ); ?>>
												<?php echo $checkbox['name']; ?>
											</label>
										</li>
									<?php }
									?>
								<?php endif ?>
							</ul>
							<?php echo $copy_desc; ?>
						</div>
					<?php 
					}


					/**
					* 
					* Gallery Field Layout
					* 
					**/

					if ( $field['type'] == 'gallery' ) { 
						$gallery = empty($result[$field['key']]) ? [] : $result[$field['key']];
						?>
						<div class="tmcf_field gallery">
							<label><?php echo $field['name']; ?></label>
							<div class="gallery_wrapper" data-name="<?php echo $field_name; ?>">
								<div class="img_box_container">
								<?php foreach ($gallery as $item): 
									$image_url = wp_get_attachment_image_src( $item );
									?>
										<div class="gallery_single_row">
											<div class="gallery_area image_container">
												<input class="meta_image_id" value="<?php echo $item; ?>" type="hidden" name="<?php echo $field_name; ?>[]" />
												<img class="gallery_img_url" src="<?php echo $image_url[0]; ?>" height="55" width="55"/>
											</div>
											<span class="button remove" title="Remove">
												<span class="dashicons dashicons-trash"></span>
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
							<?php echo $copy_desc; ?>
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
		$is_valid_nonce = ( isset( $_POST[ 'tmcf_nonce' ] ) && wp_verify_nonce( $_POST[ 'tmcf_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';
		
		if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset($_POST['tmcf']) && !empty($_POST['tmcf']) ) {
			update_post_meta( $post_id, 'display_tmcf', wp_json_encode($_POST['tmcf']));
		}	
	}
}


