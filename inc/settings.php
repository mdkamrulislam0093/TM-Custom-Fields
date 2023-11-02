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
		add_action( 'save_post', [$this, 'save_settings'] );

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
			wp_enqueue_style( 'tm_settings_style', TMG_URL . 'assets/settings/css/style.css');
			wp_enqueue_script( 'tm_settings_script', TMG_URL . 'assets/settings/js/settings.js', [ 'jquery' ], '1.0', true );
			wp_localize_script( 'tm_settings_script', 'tm_settings_object', [
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
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
			'show_in_rest'		=> false
		]);
	}

	public function settings_metabox() {
		add_meta_box(
			'tmcf_location_rules',
			'Location Rules',
			[$this, 'location_rules'],
			'tmcf_settings', 
			'side',
			'core'
		);	

		add_meta_box(
			'tmcf_settings_fields',
			'Fields',
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
				]
			]
		];

		return $default;
	}

	public function location_rules($post) {
		$setting_location = !empty(get_post_meta( $post->ID, 'tmcf_setting_location', true)) ? explode(',', get_post_meta( $post->ID, 'tmcf_setting_location', true)) : [];
		?>
			<div class="location-rules">
				<ul>
					<?php foreach ($this->getPostTypes() as $key => $value):
						?>
						<li><label><input type="checkbox" id="post_type_<?= $key ?>" name="location[]" value="<?= $value ?>"
						<?php checked( in_array($value, $setting_location), 1, true); ?>><?= $value ?></label></li>
					<?php endforeach ?>
				</ul>
			</div>
		<?php 
	}

	public function setting_fields($post) {

		$setting_fields = !empty(get_post_meta( $post->ID, 'tmcf_setting_fields', true)) ? json_decode( get_post_meta( $post->ID, 'tmcf_setting_fields', true), true) : $this->set_default_data();
		?>
		<div id="TMCF_settings_fields_wrap" data-post_id="<?= $post->ID; ?>">
			<div class="fields-wrap">
				<div class="fields-item-contents">
						<?php 
							if ( !empty($setting_fields) ) {
								foreach ($setting_fields as $key => $item) {
						?>					
					<div class="fields-item-wrap <?= $item['type']; ?>" data-index="<?= $key; ?>">
						<div class="field-heading">
							<div class="tmcf-row">
								<div class="tmcf-col">
									<span class="name"><?= $item['name']; ?></span>
								</div>
								<div class="tmcf-col key-field">
									<span class="copy-key">
										<input type="text" value='[tmcf key="<?= $item['key']; ?>"]' readonly>
									</span>
								</div>
								<div class="tmcf-col type-field">
									<span class="type"><?= $item['type']; ?></span>
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
									<label>Label</label>
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,</p>							
								</div>
								<div class="field-control">
									<input type="text" name="tmcf_fields[<?= $key; ?>][name]" value="<?= $item['name']; ?>" placeholder="Name" class="name">
								</div>
							</div>
							<div class="field-component field-key">
								<div class="field-meta">
									<label>Name/Key</label>
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,</p>							
								</div>
								<div class="field-control">
									<input type="text" class="key" name="tmcf_fields[<?= $key; ?>][key]" value="<?= $item['key']; ?>" placeholder="Key">
									<p class="error">Key is already exist.</p>									
								</div>
							</div>	

							<div class="field-component field-type">
								<div class="field-meta">
									<label>Field Type</label>
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,</p>							
								</div>
								<div class="field-control">
									<select name="tmcf_fields[<?= $key; ?>][type]">
										<?php foreach ($this->fields_type() as $field_key => $field): ?>
											<option value="<?= $field_key ?>" <?= selected( $item['type'], $field_key ); ?>><?= $field; ?></option>						
										<?php endforeach ?>
									</select>
								</div>
							</div>

							<div class="field-component field-option" data-type="<?= $item['type']; ?>">
								<div class="field-meta">
									<label>Field Options</label>
									<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,</p>							
								</div>
								<div class="field-control">
									<div class="field-control-option">
										<table>
											<thead>
												<tr>
													<th>Name</th>
													<th>Value</th>
												</tr>
											</thead>
											<tbody>
												<?php if ( !empty($item['option']) ): ?>
													<?php foreach ($item['option'] as $option_key => $option): ?>
													<tr>
														<td><input type="text" data-name="name" name="tmcf_fields[<?= $key; ?>][option][<?= $option_key; ?>][name]" placeholder="Option Name" value="<?= $option['name']; ?>" ></td>
														<td><input type="text" data-name="value" name="tmcf_fields[<?= $key; ?>][option][<?= $option_key; ?>][value]" placeholder="Option Value" value="<?= $option['value']; ?>"></td>
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
														<td><input type="text" data-name="name" name="tmcf_fields[<?= $key; ?>][option][<?= $option_key; ?>][name]" placeholder="Option Name" value="<?= $option['name']; ?>" ></td>
														<td><input type="text" data-name="value" name="tmcf_fields[<?= $key; ?>][option][0][value]" placeholder="Option Value" value="<?= $option['value']; ?>"></td>
														<td class="remove-option"></td>
													</tr>		
												<?php endif ?>
											</tbody>
											<tfoot>
												<tr>
													<td><button class="button-primary add_option">New Field Option</button></td>
												</tr>
											</tfoot>
										</table>
									</div>
								</div>								
							</div>
						</div>						
					</div>
					<?php 
						}
					}
					 ?>
				</div>
			</div>

			<div class="btn-wrap">
				<button class="add button-primary">Add Field</button>				
			</div>
		</div>
		<?php 
	}


	public function save_settings($post_id) {
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

		if ( isset($_POST['post_type']) && 'tmcf_settings' != $_POST['post_type'] ) {
			return;
		}

		if ( isset($_POST['location']) ) {
			update_post_meta( $post_id, 'tmcf_setting_location', implode(',', $_POST['location']));
		}

		if ( isset($_POST['tmcf_fields']) ) {
			update_post_meta( $post_id, 'tmcf_setting_fields', json_encode($_POST['tmcf_fields']));
		}
	}

}

