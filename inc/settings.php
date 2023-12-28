<?php 

//Avoiding Direct File Access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class TM_Settings {

	private static $instance;

	public static function get_instance(){
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct(){
		add_action( 'init', [$this, 'init'] );
		add_action( 'add_meta_boxes', [$this, 'settings_metabox']);
		add_action( 'save_post', [$this, 'save_settings'], 10, 2 );

		add_action( 'admin_print_scripts-post-new.php', [$this, 'post_enqueue']);	
		add_action( 'admin_print_scripts-post.php', [$this, 'post_enqueue']);	

		// Checking Field Key Exist
		add_action( 'wp_ajax_checking_field_key', [$this, 'checking_field_key']);
	}


	/**
	* 
	* Checking Field Key Exist.
	* return existing_key;
	*/
	public function checking_field_key() {

	     // Check for nonce security      
	     if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
	         wp_die();
	     }

		$posts = get_posts([
			'post_type'		=> 'tmcf_settings',
			'numberposts' 	=> -1,
			'fields' 		=> 'ids',
			'exclude'		=> !empty($_POST['post_id']) ? $_POST['post_id'] : []
		]);


		if ( empty($_POST['field_key']) ) {
			return;
		}

		$existing_key = $_POST['field_key'];

		if ( !empty($posts) ) {
			foreach ($posts as $post_id) {
				$setting_fields = !empty(get_post_meta( $post_id, 'tmcf_setting_fields', true)) ? json_decode( get_post_meta( $post_id, 'tmcf_setting_fields', true), true) : [];
				
				if ( !empty($setting_fields) ) {
					foreach ($setting_fields as $field) {
						if ( $field['key'] == $_POST['field_key'] ) {
							$existing_key = $_POST['field_key'] . '_copy';
						}
					}
				}
			}
		}

		echo $existing_key;

		wp_die();
	}

	public function post_enqueue() {

		if ( !empty(get_current_screen()) && get_current_screen()->post_type == 'tmcf_settings' ) {
			wp_enqueue_style( 'tm_settings_style', TMG_URL . 'assets/settings/css/style.css', [], wp_rand(1, 100));
			wp_enqueue_script( 'tm_settings_script', TMG_URL . 'assets/settings/js/settings.js', [ 'jquery' ], wp_rand(1, 100), true );
			wp_localize_script( 'tm_settings_script', 'tm_settings_object', [
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce('ajax-nonce')
			]);
		}

	}


	public function init() {
		register_post_type( 'tmcf_settings', [
			'label' 			=> 'TMCF',
			'public' 			=> false,
			'publicly_queryable' => false,
			'show_in_menu'		=> true,
			'show_ui'			=> true,
			'menu_position'		=> 99,
			'supports'			=> array( 'title' ),
			'show_in_rest'		=> false,
			'menu_icon'			=> 'dashicons-list-view'
		]);
	}

	public function settings_metabox() {
		add_meta_box(
			'tmcf_location_rules',
			__('Location Rules', 'tmcf_lite' ),
			[$this, 'location_rules'],
			'tmcf_settings', 
			'side',
			'core'
		);	

		add_meta_box(
			'tmcf_settings_fields',
			__( 'Fields', 'tmcf_lite' ),
			[$this, 'setting_fields'],
			'tmcf_settings', 
			'normal',
			'core'
		);

	}

	public function fields_type() {
		return [
			'text' => 'Text',
			'number' => 'Number',
			'tel' => 'Tel',
			'email' => 'Email',
			'select' => 'Select',
			'checkbox' => 'Checkbox',
			'radio' => 'Radio',
			'gallery' => 'Gallery',
			'color' => 'Color',
		];
	}


	public function getPostTypes() {
		$all_post_types = get_post_types([
		  'public'   => true,
		], 'names');

		return array_diff( $all_post_types, [ 'attachment' ] );		
	}

	public function set_default_data() {
		$default = [
			[ 
				'name' => 'name', 
				'key' => 'key', 
				'type' => 'type',
				'option' => [
					[
						'name' => '',
						'value' => ''
					]
				],
				'placeholder' => ''
			]
		];

		return $default;
	}

	public function location_rules($post) {
		wp_nonce_field( basename(__FILE__), 'tmcf_location_rules' );
		$setting_location = !empty(get_post_meta( $post->ID, 'tmcf_setting_location', true)) ? explode(',', get_post_meta( $post->ID, 'tmcf_setting_location', true)) : [];
		?>
			<div class="location-rules">
				<ul>
					<?php foreach ($this->getPostTypes() as $key => $value):
						?>
						<li><label><input type="checkbox" id="post_type_<?php echo $key ?>" name="location[]" value="<?php echo $value ?>"
						<?php checked( in_array($value, $setting_location), 1, true); ?>><?php echo $value ?></label></li>
					<?php endforeach ?>
				</ul>
			</div>
		<?php 
	}

	public function setting_fields($post) {
		wp_nonce_field( basename(__FILE__), 'tmcf_setting_fields' );
		$setting_fields = !empty(get_post_meta( $post->ID, 'tmcf_setting_fields', true)) ? json_decode( get_post_meta( $post->ID, 'tmcf_setting_fields', true), true) : $this->set_default_data();
		?>
		<div id="TMCF_settings_fields_wrap" data-post_id="<?php echo $post->ID; ?>">
			<div class="fields-wrap">
				<div class="fields-item-contents">
						<?php 
							if ( !empty($setting_fields) ) {
								foreach ($setting_fields as $key => $item) {
									if ( !empty($item['type']) && !empty($item['name']) && !empty($item['key']) ) {

						?>					
					<div class="fields-item-wrap <?php echo $item['type']; ?>" data-index="<?php echo $key; ?>">
						<div class="field-heading">
							<div class="tmcf-row">
								<div class="tmcf-col">
									<span class="name"><?php echo $item['name']; ?></span>
								</div>
								<div class="tmcf-col key-field">
									<span class="copy-key">
										<input type="text" value='[tmcf key="<?php echo $item['key']; ?>"]' readonly>
									</span>
								</div>
								<div class="tmcf-col type-field">
									<span class="type"><?php echo $item['type']; ?></span>
								</div>
								<div class="tmcf-col trash">
									<span class="dashicons dashicons-trash"></span>
									<span class="dashicons dashicons-arrow-right-alt2"></span>
								</div>
							</div>
						</div>
						<div class="field-content">
							<div class="field-component field-label">
								<div class="field-meta">
									<label><?php _e( 'Label', 'tmcf_lite' ); ?> <span style="color: red;">*</span></label>
									<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,', 'tmcf_lite' ); ?></p>							
								</div>
								<div class="field-control">
									<input type="text" name="tmcf_fields[<?php echo $key; ?>][name]" value="<?php echo $item['name']; ?>" placeholder="<?php _e( 'Name', 'tmcf_lite' ); ?>" class="name" required>
								</div>
							</div>
							<div class="field-component field-key">
								<div class="field-meta">
									<label><?php _e( 'Name/Key', 'tmcf_lite' ); ?> <span style="color: red;">*</span></label>
									<p><?php _e( 'Name/Key field will be stored in the database and will help to display on your website. Should contain only Latin letters, numbers, "-" or "_" chars.', 'tmcf_lite' ); ?></p>
								</div>
								<div class="field-control">
									<input type="text" class="key" name="tmcf_fields[<?php echo $key; ?>][key]" value="<?php echo $item['key']; ?>" placeholder="<?php _e( 'Key', 'tmcf_lite' ); ?>" required>
									<p class="error"><?php _e( 'Key is already exist.', 'tmcf_lite' ); ?></p>
								</div>
							</div>	

							<div class="field-component field-type">
								<div class="field-meta">
									<label><?php _e( 'Field Type', 'tmcf_lite' ); ?> <span style="color: red;">*</span></label>
									<p><?php _e( 'Field type defines the way field to be displayed on Post edit page.', 'tmcf_lite' ); ?></p>
								</div>
								<div class="field-control">
									<select name="tmcf_fields[<?php echo $key; ?>][type]" required>
										<option value=""><?php _e( 'Select Field Type', 'tmcf_lite' ); ?></option>
										<?php foreach ($this->fields_type() as $field_key => $field): ?>
											<option value="<?php echo $field_key ?>" <?php echo selected( $item['type'], $field_key ); ?>><?php echo $field; ?></option>						
										<?php endforeach ?>
									</select>
								</div>
							</div>

							<div class="field-component field-option" data-type="<?php echo $item['type']; ?>">
								<div class="field-meta">
									<label><?php _e( 'Field Options', 'tmcf_lite' ); ?></label>
									<p><?php _e( 'Field Options will be show in field type option.', 'tmcf_lite' ); ?></p>							
								</div>
								<div class="field-control">
									<div class="field-control-option">
										<table>
											<thead>
												<tr>
													<th><?php _e( 'Name', 'tmcf_lite' ); ?></th>
													<th><?php _e( 'Value', 'tmcf_lite' ); ?></th>
													<th></th>
													<th></th>
												</tr>
											</thead>
											<tbody>
												<?php if ( !empty($item['option']) ): ?>
													<?php foreach ($item['option'] as $option_key => $option): ?>
													<tr>
														<td><input type="text" class="field_option_name" data-name="name" name="tmcf_fields[<?php echo $key; ?>][option][<?php echo $option_key; ?>][name]" placeholder="<?php _e( 'Option Name', 'tmcf_lite' ); ?>" value="<?php echo $option['name']; ?>" ></td>
														<td>
															<input type="text" class="field_option_value" data-name="value" name="tmcf_fields[<?php echo $key; ?>][option][<?php echo $option_key; ?>][value]" placeholder="<?php _e( 'Option Value', 'tmcf_lite' ); ?>" value="<?php echo $option['value']; ?>">
														</td>
														<td class="remove-option">
															<?php 
																if ( $option_key > 0 ) {
																	echo '<span class="dashicons dashicons-trash"></span>';
																}
															 ?>
														</td>
													</tr>
												<?php endforeach ?>
												<?php else: ?>
													<tr>
														<td>
															<input type="text" class="field_option_name"  data-name="name" name="tmcf_fields[<?php echo $key; ?>][option][<?php echo $option_key; ?>][name]" placeholder="<?php _e( 'Option Name', 'tmcf_lite' ); ?>" value="<?php echo $option['name']; ?>" />
														</td>
														<td>
															<input type="text" class="field_option_value" data-name="value" name="tmcf_fields[<?php echo $key; ?>][option][0][value]" placeholder="<?php _e( 'Option Value', 'tmcf_lite' ); ?>" value="<?php echo $option['value']; ?>" />
														</td>
														<td class="remove-option"></td>
													</tr>		
												<?php endif ?>
											</tbody>
											<tfoot>
												<tr>
													<td><button class="button-primary add_option"><?php _e( 'New Field Option', 'tmcf_lite' ); ?></button></td>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>								
							</div>

							<div class="field-component field-placeholder <?php echo in_array($item['type'], ['text', 'number', 'tel', 'email' ]) ? 'active' : ''; ?>">
								<div class="field-meta">
									<label><?php _e( 'Placeholder', 'tmcf_lite' ); ?></label>
									<p><?php _e( 'Placeholder text', 'tmcf_lite' ); ?></p>							
								</div>
								<div class="field-control">
									<input type="text" class="placeholder" name="tmcf_fields[<?php echo $key; ?>][placeholder]" value="<?php echo !empty($item['placeholder']) ? $item['placeholder'] : ''; ?>" placeholder="<?php _e( 'Placeholder', 'tmcf_lite' ); ?>">
								</div>
							</div>								
						</div>						
					</div>
					<?php 
							}					
						}
					}
					 ?>
				</div>
			</div>

			<div class="btn-wrap">
				<button class="add button-primary"><?php _e( 'Add Field', 'tmcf_lite' ); ?></button>				
			</div>
		</div>
		<?php 
	}


	public function save_settings($post_id, $post) {
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

		if ( 'tmcf_settings' !== $post->post_type ) {
			return;
		}


		if ( isset($_POST['tmcf_location_rules']) &&  wp_verify_nonce ( $_POST['tmcf_location_rules'], basename(__FILE__)) ) {
			if ( isset($_POST['location']) && !empty($_POST['location']) ) {
				update_post_meta( $post_id, 'tmcf_setting_location', implode(',', $_POST['location']));
			} else {
				update_post_meta( $post_id, 'tmcf_setting_location', '');
			}

		}

		if ( isset($_POST['tmcf_fields']) && !empty($_POST['tmcf_fields']) && isset($_POST['tmcf_setting_fields']) &&  wp_verify_nonce ( $_POST['tmcf_setting_fields'], basename(__FILE__)) ) {	
			update_post_meta( $post_id, 'tmcf_setting_fields', wp_json_encode($_POST['tmcf_fields']));
		}
	}

}

