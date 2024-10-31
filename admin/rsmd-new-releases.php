<?php
//if this file is called directly, abort.
if(!defined('ABSPATH')) die('please, do not call this page directly');

//NEW RELEASES

if(!function_exists('rsmd_release_15')){
	
	function rsmd_release_15 () {
				
		//get and check saved options
		global $rsmd_saved_options;
		global $rsmd_options_name;
				
		//we need to update method only if plugin version is less than 1.5
		if(empty($rsmd_saved_options['plugin_version']) || (int)$rsmd_saved_options['plugin_version'] < 15) {
			
			$rsmd_posts_to_deal_with = new WP_Query(

				//post arguments
				array(
				
					'post_type' => 'attachment',
					'posts_per_page' => -1,
					'orderby' => 'ID',
					'order' => 'DESC',						
					'suppress_filters' => true, //load WPML duplicates media too
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
			$rsmd_restricted_attachments_ids = $rsmd_posts_to_deal_with->posts;

			wp_reset_postdata();				

			//if restricted media are found
			if(!empty($rsmd_restricted_attachments_ids)) {

				//loop into post id array
				foreach($rsmd_restricted_attachments_ids as $rsmd_restricted_attachments_id) {			

					//check original and renamed path
					$rsmd_restricted_post_original_path = get_post_meta($rsmd_restricted_attachments_id, '_rsmd_before_rename', true);
					$rsmd_restricted_post_scrambled_path = get_post_meta($rsmd_restricted_attachments_id, '_rsmd_after_rename', true);
					
					//if media was previously renamed
					if(!empty($rsmd_restricted_post_scrambled_path) && !empty($rsmd_restricted_post_original_path)) {

						if(file_exists($rsmd_restricted_post_scrambled_path)) {
					
							//rename back file
							rename($rsmd_restricted_post_scrambled_path,$rsmd_restricted_post_original_path);
							
						}
						
						delete_post_meta($rsmd_restricted_attachments_id, '_rsmd_before_rename');
						delete_post_meta($rsmd_restricted_attachments_id, '_rsmd_after_rename');
						
					}
					
					rsmd_add_mod_rewrite_rule($rsmd_restricted_attachments_id);
					
					$rsmd_allowed_role = get_post_meta($rsmd_restricted_attachments_id, '_rsmd_allowed_role', true);
					
					//deal with attachment duplication created by WPML
					$rsmd_get_wpml_active_languages = apply_filters('wpml_active_languages', false);
					
					//if WPML has active languages
					if(!empty($rsmd_get_wpml_active_languages)) {
					  
						//loop into languages
						foreach($rsmd_get_wpml_active_languages as $rsmd_wpml_language) {
							
							$rsmd_wpml_language_code = $rsmd_wpml_language['language_code'];
							
							$rsmd_restricted_attachments_translation_id = apply_filters('wpml_object_id', $rsmd_restricted_attachments_id, 'attachment', false, $rsmd_wpml_language_code);
							
							if(!empty($rsmd_restricted_attachments_translation_id)) {
																	
								update_post_meta($rsmd_restricted_attachments_translation_id, '_rsmd_allowed_role', $rsmd_allowed_role);
																	
								//define current post as restricted
								update_post_meta($rsmd_restricted_attachments_translation_id, '_rsmd_is_restricted', '1');	
								
							}
							
						}
						
					}					
					
				}
				
				//rewrite rules
				save_mod_rewrite_rules();
				
			}
			
			error_log('RESTRICTED MEDIA: rsmd_release_15 function executed');

		}	

		if((int)$rsmd_saved_options['plugin_version'] !== RSMD_PLUGIN_VERSION) {		
		
			//update plugin version
			$rsmd_saved_options['plugin_version'] = RSMD_PLUGIN_VERSION;
			
			update_option($rsmd_options_name, $rsmd_saved_options, false);
					
			error_log('RESTRICTED MEDIA: updated to version '.RSMD_PLUGIN_VERSION);
			
		}
			
	}
		
	add_action('admin_init', 'rsmd_release_15');

} else {
	
	error_log('function: "rsmd_release_15" already exists');
	
}