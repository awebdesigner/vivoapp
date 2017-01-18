<?php 

	add_action('init', function(){

	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'              => _x( 'Producer groups', 'taxonomy general name', 'textdomain' ),
		'singular_name'     => _x( 'Producer group', 'taxonomy singular name', 'textdomain' ),
		'search_items'      => __( 'Search Producer groups', 'textdomain' ),
		'all_items'         => __( 'All Producer groups', 'textdomain' ),
		'parent_item'       => __( 'Parent Producer group', 'textdomain' ),
		'parent_item_colon' => __( 'Parent Producer group:', 'textdomain' ),
		'edit_item'         => __( 'Edit Producer group', 'textdomain' ),
		'update_item'       => __( 'Update Producer group', 'textdomain' ),
		'add_new_item'      => __( 'Add New Producer group', 'textdomain' ),
		'new_item_name'     => __( 'New Producer group', 'textdomain' ),
		'menu_name'         => __( 'Producer groups', 'textdomain' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'producer-group' ),
	);

	register_taxonomy( 'producer-group', array( 'vivo-producers' ), $args );
	
	$labels = array(
			'name'              => _x( 'Countries', 'taxonomy general name', 'textdomain' ),
			'singular_name'     => _x( 'Country', 'taxonomy singular name', 'textdomain' ),
			'search_items'      => __( 'Search Countries', 'textdomain' ),
			'all_items'         => __( 'All Countries', 'textdomain' ),
			'parent_item'       => __( 'Parent Country', 'textdomain' ),
			'parent_item_colon' => __( 'Parent Country:', 'textdomain' ),
			'edit_item'         => __( 'Edit Country', 'textdomain' ),
			'update_item'       => __( 'Update Country', 'textdomain' ),
			'add_new_item'      => __( 'Add New Country', 'textdomain' ),
			'new_item_name'     => __( 'New Country', 'textdomain' ),
			'menu_name'         => __( 'Countries', 'textdomain' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'vivo-country' ),
		);

		register_taxonomy( 'vivo-country', array( 'vivo-producers' ), $args );

$labels = array(
			'name'              => _x( 'Villages', 'taxonomy general name', 'textdomain' ),
			'singular_name'     => _x( 'Village', 'taxonomy singular name', 'textdomain' ),
			'search_items'      => __( 'Search Villages', 'textdomain' ),
			'all_items'         => __( 'All Villages', 'textdomain' ),
			'parent_item'       => __( 'Parent Village', 'textdomain' ),
			'parent_item_colon' => __( 'Parent Village:', 'textdomain' ),
			'edit_item'         => __( 'Edit Village', 'textdomain' ),
			'update_item'       => __( 'Update Village', 'textdomain' ),
			'add_new_item'      => __( 'Add New Village', 'textdomain' ),
			'new_item_name'     => __( 'New Village', 'textdomain' ),
			'menu_name'         => __( 'Villages', 'textdomain' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'vivo-village' ),
		);

		register_taxonomy( 'vivo-village', array( 'vivo-producers' ), $args );


$labels = array(
			'name'              => _x( 'Communities', 'taxonomy general name', 'textdomain' ),
			'singular_name'     => _x( 'Community', 'taxonomy singular name', 'textdomain' ),
			'search_items'      => __( 'Search Communities', 'textdomain' ),
			'all_items'         => __( 'All Communities', 'textdomain' ),
			'parent_item'       => __( 'Parent Community', 'textdomain' ),
			'parent_item_colon' => __( 'Parent Community:', 'textdomain' ),
			'edit_item'         => __( 'Edit Community', 'textdomain' ),
			'update_item'       => __( 'Update Community', 'textdomain' ),
			'add_new_item'      => __( 'Add New Community', 'textdomain' ),
			'new_item_name'     => __( 'New Community', 'textdomain' ),
			'menu_name'         => __( 'Communities', 'textdomain' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'vivo-community' ),
		);

		register_taxonomy( 'vivo-community', array( 'vivo-producers' ), $args );
		
		$labels = array(
			'name'              => _x( 'States/Provinces', 'taxonomy general name', 'textdomain' ),
			'singular_name'     => _x( 'States/Provinces', 'taxonomy singular name', 'textdomain' ),
			'search_items'      => __( 'Search States/Provinces', 'textdomain' ),
			'all_items'         => __( 'All States/Provinces', 'textdomain' ),
			'parent_item'       => __( 'Parent State/Province', 'textdomain' ),
			'parent_item_colon' => __( 'Parent State/Province:', 'textdomain' ),
			'edit_item'         => __( 'Edit State/Province', 'textdomain' ),
			'update_item'       => __( 'Update State/Province', 'textdomain' ),
			'add_new_item'      => __( 'Add New State/Province', 'textdomain' ),
			'new_item_name'     => __( 'New State/Province', 'textdomain' ),
			'menu_name'         => __( 'States/Provinces', 'textdomain' ),
		);

		$args = array(
			'hierarchical'      => false,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'vivo-province' ),
		);

		register_taxonomy( 'vivo-province', array( 'vivo-producers' ), $args );

},0);