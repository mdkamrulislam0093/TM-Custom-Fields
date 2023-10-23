<?php 

//Avoiding Direct File Access
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class TM_Gallery {

	public function __construct($id = 0, $name = 'Gallery', $location = 'post'){
		$this->id = $id;
		$this->name = $name;
		$this->location = $location;

		add_action( 'add_meta_boxes', [$this, 'admin_meta_gallery']);	
		
	}

	public function admin_meta_gallery() {
		add_meta_box(
			'tmcf_gallery_' . $this->id,
			$this->name,
			[$this, 'gallery_callback'],
			$this->location,
			'normal',
			'core'
		);
	}

	public function gallery_callback($post) {
		wp_nonce_field( basename(__FILE__), 'tm_gallery_nonce' );

		$gallery = !empty(get_post_meta( $post->ID, 'tm_galleries', true )) ? explode(',', get_post_meta( $post->ID, 'tm_galleries', true )) : [];
		?>
		<div id="gallery_wrapper">
			<div class="img_box_container">
			<?php foreach ($gallery as $item): 
				$image_url = wp_get_attachment_image_src( $item );
				?>
					<div class="gallery_single_row">
						<div class="gallery_area image_container">
							<input class="meta_image_id" value="<?= $item; ?>" type="hidden" name="galleries[]" />
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

			<div id="master_box">
				<div class="img_box_container"></div>
			</div>
			<div id="add_gallery_single_row">
			  <input class="button add" type="button" value="+" title="Add image"/>
			</div>
		</div>
		<?php		
	}

			
}