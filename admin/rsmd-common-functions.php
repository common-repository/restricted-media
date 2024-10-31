<?php
//if this file is called directly, abort.
if(!defined('ABSPATH')) die('please, do not call this page directly');

if(!function_exists('rsmd_get_restricted_attachments_ids')) {
	
	//custom checkbox function
	function rsmd_get_restricted_attachments_ids() {

		$rsmd_posts_to_deal_with = new WP_Query(

			//post arguments
			array(
			
				'post_type' => 'attachment',
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',						
				'suppress_filters' => false, //otherwise it loads WPML duplicates media
				'offset' => 0,
				'post_status' => 'inherit',
				'ignore_sticky_posts' => true,
				'no_found_rows' => true,
				'meta_key' => '_rsmd_is_restricted', 
				'meta_value' => '1',
				'fields' => 'ids'
				
			)
			
		);

		//get image post ids array
		$rsmd_posts_ids_to_deal_with = $rsmd_posts_to_deal_with->posts;

		wp_reset_postdata();	
		
		return $rsmd_posts_ids_to_deal_with;
		
	}
		
} else {
	
	error_log('function: "rsmd_get_restricted_attachments_ids" already exists');
	
}

if(!function_exists('rsmd_get_restricted_posts_ids')) {
	
	//custom checkbox function
	function rsmd_get_restricted_posts_ids() {

		$rsmd_post_types = get_post_types( 
		
			array(
			
				'public'   => true,
				
			),	
			
			'names' 
			
		);
		
		$rsmd_post_types_list = null;
		
		foreach($rsmd_post_types as $rsmd_post_type) {
			
			if($rsmd_post_type === 'attachment') {
				
				continue;
			}
			
			$rsmd_post_types_list .= '"'.$rsmd_post_type.'",';
			
		}
		
		$rsmd_post_types_list = rtrim($rsmd_post_types_list, ',');

		$rsmd_posts_to_deal_with = new WP_Query(

			//post arguments
			array(
			
				'post_type' => array('post', 'page', $rsmd_post_types_list),
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC',						
				'suppress_filters' => false, //otherwise it loads WPML duplicates media
				'offset' => 0,
				'post_status' => 'publish',
				'ignore_sticky_posts' => true,
				'no_found_rows' => true,
				'meta_key' => '_rsmd_is_restricted', 
				'meta_value' => '1',
				'fields' => 'ids'
				
			)
			
		);

		//get image post ids array
		$rsmd_posts_ids_to_deal_with = $rsmd_posts_to_deal_with->posts;

		wp_reset_postdata();	
		
		return $rsmd_posts_ids_to_deal_with;
		
	}
		
} else {
	
	error_log('function: "rsmd_get_restricted_posts_ids" already exists');
	
}

if(!function_exists('rsmd_add_mod_rewrite_rule')) {
	
	//custom checkbox function
	function rsmd_add_mod_rewrite_rule($rsmd_restricted_attachments_id) {

		$rsmd_current_post_absolute_url = wp_get_attachment_url($rsmd_restricted_attachments_id);			
		$rsmd_current_post_realtive_url = str_replace(site_url(),'',$rsmd_current_post_absolute_url);
		
		add_filter(
		
			'mod_rewrite_rules', 
			function($rsmd_current_rewrite_rules) use ($rsmd_current_post_realtive_url) {
				
			$rsmd_current_rewrite_rules .= '
				
				<files "'.basename($rsmd_current_post_realtive_url).'">
				  deny from all
				</files>'.
				"\r\n"
				
			;
			

			return $rsmd_current_rewrite_rules;
			}
			
		);

	}
		
} else {
	
	error_log('function: "rsmd_add_mod_rewrite_rule" already exists');
	
}