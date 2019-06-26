<?php
/*
 * Plugin Name: Events
 * Plugin URI: 
 * Description: 
 * Version: 1.1.1
 * Author: Roman Didenko
 * Author URI: 
 * License: GPLv2
 */
 
// Register Custom Post Type
function custom_post_type() {

	$labels = array(
		'name'                  => _x( 'events', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'events', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Події', 'text_domain' ),
		'name_admin_bar'        => __( 'Події', 'text_domain' ),
		'archives'              => __( 'Item Archives', 'text_domain' ),
		'attributes'            => __( 'Item Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
		'all_items'             => __( 'All Items', 'text_domain' ),
		'add_new_item'          => __( 'Add New Item', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
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
		'label'                 => __( 'Події', 'text_domain' ),
		'description'           => __( 'Події', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'custom-fields' ),
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
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'events', $args );

}
add_action( 'init', 'custom_post_type', 0 );

/**
 * Register meta boxes.
 */
function events_register_meta_boxes() {
    add_meta_box( 'events', 'Дати події', 'events_display_callback', 'events', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'events_register_meta_boxes' );

/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function events_display_callback( $post ) {
	?>
	<div class="events_box">
		<p class="meta-options events_field">
			<label for="events_date_from">Дата початку події</label>
			<input id="events_date_from" type="date" name="events_date_from" value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'events_date_from', true ) ); ?>">
		</p>
		<p class="meta-options events_field">
			<label for="events_date_to">Дата завершення події</label>
			<input id="events_date_to" type="date" name="events_date_to" value="<?php echo esc_attr( get_post_meta( get_the_ID(), 'events_date_to', true ) ); ?>">
		</p>

	</div>
	<?php
}

/**
 * Save meta box content.
 *
 * @param int $post_id Post ID
 */
function events_save_meta_box( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( $parent_id = wp_is_post_revision( $post_id ) ) {
        $post_id = $parent_id;
    }
    $fields = [
        'events_date_from',
        'events_date_to',
    ];
    foreach ( $fields as $field ) {
        if ( array_key_exists( $field, $_POST ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ) );
        }
     }
}
add_action( 'save_post', 'events_save_meta_box' );

/* Add columns dates for events
------------------------------------------------------------------------ */
// add new columns
add_filter('manage_events_posts_columns', 'add_date_from_column', 4);
function add_date_from_column( $columns ){

	// 3-4 columns
	$out = array();
	foreach($columns as $col=>$name){
		if(++$i==3) {
			$out['date_from'] = 'Дата початку';
			$out['date_to'] = 'Дата завершення';
		}
		$out[$col] = $name;
	}
	return $out;
}


// fill columns dates -  wp-admin/includes/class-wp-posts-list-table.php
add_filter('manage_events_posts_custom_column', 'fill_events_date_from_column', 5, 2); 
function fill_events_date_from_column( $colname, $post_id ){
	if( $colname === 'date_from' ){
		echo get_post_meta($post_id, 'events_date_from', 1);
	}
	if( $colname === 'date_to' ){
		echo get_post_meta($post_id, 'events_date_to', 1);
	}
}

// width columns if need
// add_action('admin_head', 'add_events_column_css');
function add_events_column_css(){
	if( get_current_screen()->base == 'edit')
		echo '<style type="text/css">.column-date_from, .column-date_to{width:10%;}</style>';
}



// Add Shortcode
function events_shortcode( $atts ) {

	// Attributes
	$atts = shortcode_atts(
		array(
			'amount' => '8',
		),
		$atts
	);
	
	// WP_Query arguments
	$args = array(
		'post_type'              => array( 'events' ),
		'post_status'            => array( 'publish' ),
		'posts_per_page'         => $atts[ 'amount' ],
	);

	// The Query
	$query = new WP_Query( $args );
	
	if ( $query->have_posts() ) {
		$output = '<ul>';
		while ( $query->have_posts() ) {
			$query->the_post();
			$output .= '<li>' . get_the_title() . '</li>';
		}
		$output .= '</ul>';
	}
	
	return $output;

}
add_shortcode( 'events', 'events_shortcode' );
