<?php 
/*
-------------------------------------------------------------------------------------------------------------------------
-
*** This function will create custom posttype named lam-store-locations
-
-------------------------------------------------------------------------------------------------------------------------
*/
if( is_super_admin() ){
	

	add_action('init', function(){
	    // Register Custom Post Type

	    $labels = array(
	        'name'                  => _x( 'Store Locator', 'Post Type General Name', 'text_domain' ),
	        'singular_name'         => _x( 'Store Locations', 'Post Type Singular Name', 'text_domain' ),
	        'menu_name'             => __( 'Store Locations', 'text_domain' ),
	        'name_admin_bar'        => __( 'Store Locations', 'text_domain' ),
	        'archives'              => __( 'Store Location Archives', 'text_domain' ),
	        'attributes'            => __( 'Store Locations Attributes', 'text_domain' ),
	        'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
	        'all_items'             => __( 'All Locations', 'text_domain' ),
	        'add_new_item'          => __( 'Add New Location', 'text_domain' ),
	        'add_new'               => __( 'Add New Location', 'text_domain' ),
	        'new_item'              => __( 'New Item', 'text_domain' ),
	        'edit_item'             => __( 'Edit Item', 'text_domain' ),
	        'update_item'           => __( 'Update Item', 'text_domain' ),
	        'view_item'             => __( 'View Item', 'text_domain' ),
	        'view_items'            => __( 'View Items', 'text_domain' ),
	        'search_items'          => __( 'Search Item', 'text_domain' ),
	        'not_found'             => __( 'Not found', 'text_domain' ),
	        'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
	        'featured_image'        => __( 'Featured Image', 'text_domain' ),
	        'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
	        'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
	        'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
	        'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
	        'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
	        'items_list'            => __( 'Items list', 'text_domain' ),
	        'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
	        'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
	    );
	    $args = array(
	        'label'                 => __( 'Store Locations', 'text_domain' ),
	        'description'           => __( 'You can add you store locations or address here. You can add as many as you hava.', 'text_domain' ),
	        'labels'                => $labels,
	        'supports'              => array( 'title', 'thumbnail', ),
	        'taxonomies'            => array( 'category', 'post_tag' ),
	        'hierarchical'          => false,
	        'public'                => true,
	        'show_ui'               => true,
	        'show_in_menu'          => true,
	        'menu_position'         => 5,
	        'show_in_admin_bar'     => true,
	        'show_in_nav_menus'     => true,
	        'can_export'            => true,
	        'has_archive'           => true,   
	        'menu_icon'   			=> 'dashicons-location',     
	        'exclude_from_search'   => false,
	        'publicly_queryable'    => true,
	        'capability_type'       => 'post',
	    );
	    register_post_type( 'lam-store-locations', $args );

	});
}//END IF;


/*
-------------------------------------------------------------------------------------------------------------------------
-
*** This function will add a meta to the custom posttype name lam-store-location
-
-------------------------------------------------------------------------------------------------------------------------
*/


function store_locator_get_meta( $value ) {
	global $post;

	$field = get_post_meta( $post->ID, $value, true );
	if ( ! empty( $field ) ) {
		return is_array( $field ) ? stripslashes_deep( $field ) : stripslashes( wp_kses_decode_entities( $field ) );
	} else {
		return false;
	}
}

function store_locator_add_meta_box() {
	add_meta_box(
		'store_locator-store-locator',
		__( 'Store Location details', 'store_locator' ),
		'store_locator_html',
		'lam-store-locations',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'store_locator_add_meta_box' );

function store_locator_html( $post) {
	wp_nonce_field( '_store_locator_nonce', 'store_locator_nonce' ); ?>

	<p>
		<label for="store_locator_latitude"><?php _e( 'Latitude', 'store_locator' ); ?></label><br>
		<input type="text" name="store_locator_latitude" id="store_locator_latitude" value="<?php echo store_locator_get_meta( 'store_locator_latitude' ); ?>">
	</p>	<p>
		<label for="store_locator_longitude"><?php _e( 'Longitude', 'store_locator' ); ?></label><br>
		<input type="text" name="store_locator_longitude" id="store_locator_longitude" value="<?php echo store_locator_get_meta( 'store_locator_longitude' ); ?>">
	</p>	<p>
		<label for="store_locator_address"><?php _e( 'Address', 'store_locator' ); ?></label><br>
		<textarea name="store_locator_address" id="store_locator_address" cols="100" rows="10"><?php echo store_locator_get_meta( 'store_locator_address' ); ?></textarea>
	
	</p>	
	
	<p>
		<label for="store_locator_tell_number"><?php _e( 'Tell number', 'store_locator' ); ?></label><br>
		<input type="text" name="store_locator_tell_number" id="store_locator_tell_number" value="<?php echo store_locator_get_meta( 'store_locator_tell_number' ); ?>">
	</p>	
	<p>
		<label for="store_locator_cell_phone_number"><?php _e( 'Cell Phone Number', 'store_locator' ); ?></label><br>
		<input type="text" name="store_locator_cell_phone_number" id="store_locator_cell_phone_number" value="<?php echo store_locator_get_meta( 'store_locator_cell_phone_number' ); ?>">
	</p>

<?php
}

function store_locator_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! isset( $_POST['store_locator_nonce'] ) || ! wp_verify_nonce( $_POST['store_locator_nonce'], '_store_locator_nonce' ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( isset( $_POST['store_locator_latitude'] ) )
		update_post_meta( $post_id, 'store_locator_latitude', esc_attr( $_POST['store_locator_latitude'] ) );
	if ( isset( $_POST['store_locator_longitude'] ) )
		update_post_meta( $post_id, 'store_locator_longitude', esc_attr( $_POST['store_locator_longitude'] ) );
	if ( isset( $_POST['store_locator_address'] ) )
		update_post_meta( $post_id, 'store_locator_address', $_POST['store_locator_address'] );
	
	
	if ( isset( $_POST['store_locator_tell_number'] ) )
		update_post_meta( $post_id, 'store_locator_tell_number', esc_attr( $_POST['store_locator_tell_number'] ) );
	if ( isset( $_POST['store_locator_cell_phone_number'] ) )
		update_post_meta( $post_id, 'store_locator_cell_phone_number', esc_attr( $_POST['store_locator_cell_phone_number'] ) );
	
}
add_action( 'save_post', 'store_locator_save' );

/*
	Usage: store_locator_get_meta( 'store_locator_latitude' )
	Usage: store_locator_get_meta( 'store_locator_longitude' )
	Usage: store_locator_get_meta( 'store_locator_address' )
	Usage: store_locator_get_meta( 'store_locator_tell_number' )
	Usage: store_locator_get_meta( 'store_locator_cell_phone_number' )
*/