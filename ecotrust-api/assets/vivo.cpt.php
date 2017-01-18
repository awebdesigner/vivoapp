<?php
add_action('init',function(){
	
	//Producer Post type
	register_post_type( 'vivo-producers', array(

		'labels' => array(
  
		'name' => '	Producers',
	
		'singular_name' => 'Producer',
	
		'add_new_item' => 'Add New Producer',
	
		),
   
		'description' => 'Manage Producers',
  
		'public' => true,
  
		'supports' => array( 'title','thumbnail' )
  
		));
		
	//Techicians Post Type 
	register_post_type( 'vivo-techicians', array(

		'labels' => array(
  
		'name' => '	Techicians',
	
		'singular_name' => 'Techician',
	
		'add_new_item' => 'Add New Techician',
	
		),
   
		'description' => 'Manage Techicians',
  
		'public' => true,
  
		'supports' => array( 'title','thumbnail' )
  
		));
		
	//Buyers Post Type 
	register_post_type( 'vivo-buyers', array(

		'labels' => array(
  
		'name' => '	Buyers',
	
		'singular_name' => 'Buyer',
	
		'add_new_item' => 'Add New Buyer',
	
		),
   
		'description' => 'Manage Buyers',
  
		'public' => true,
  
		'supports' => array( 'title','thumbnail' )
  
		));
		
	//Buyers Sales Agreement Post Type 
	register_post_type( 'vivo-buyers-sales-agreements', array(

		'labels' => array(
  
		'name' => '	Buyers Sales Agreements',
	
		'singular_name' => 'Buyers Sales Agreement',
	
		'add_new_item' => 'Add New Buyers Sales Agreement',
	
		),
   
		'description' => 'Manage Buyers Sales Agreements',
  
		'public' => true,
  
		'supports' => array( 'title','thumbnail' )
  
		));		

	//Plan Vivo Information
	register_post_type( 'vivo-plan-information', array(

		'labels' => array(
  
		'name' => '	Plan Vivo Information',
	
		'singular_name' => 'Plan Vivo Information',
	
		'add_new_item' => 'Add New Plan Vivo Information',
	
		),
   
		'description' => 'Manage Plan Vivo Information',
  
		'public' => true,
  
		'supports' => array( 'title','thumbnail' )
  
		));

	//Technical Specifications
	register_post_type( 'vivo-tech-specifics', array(

		'labels' => array(
  
		'name' => '	Technical Specifications',
	
		'singular_name' => 'Technical Specifications',
	
		'add_new_item' => 'Add Technical Specifications',
	
		),
   
		'description' => 'Manage Technical Specifications',
  
		'public' => true,
  
		'supports' => array( 'title','thumbnail' )
  
		));
});