<?php
//if this file is called directly, abort.
if(!defined('ABSPATH')) die('please, do not call this page directly');

if(!function_exists('rsmd_add_media_checkbox')) {
	
	//custom checkbox function
	function rsmd_add_media_checkbox($rsmd_attachment_form_fileds_array, $rsmd_post_object) {
		
		//get involved post id
		$rsmd_current_post_id = $rsmd_post_object->ID;
		
		//if WordPress is at least 5.3 and involved post is an image 
		if(function_exists('wp_get_original_image_path')) {
			
			//get current file path get original image path
			$rsmd_current_post_original_path = wp_get_original_image_path($rsmd_current_post_id);
			//returns "/var/www/vhosts/sitename.ext/wp-content/uploads/yyyy/mm/filename.ext"	
				
		} 
		
		//otherwise get attached file
		if(empty($rsmd_current_post_original_path)) {
			
			$rsmd_current_post_original_path = get_attached_file($rsmd_current_post_id);
			//returns "/var/www/vhosts/sitename.ext/wp-content/uploads/yyyy/mm/filename.ext"
			
		}			
		
		//get uploads directory object
		$rsmd_current_upload_dir = wp_upload_dir();
		
		//get uploads base url
		$rsmd_current_upload_dir_basename = wp_basename($rsmd_current_upload_dir['baseurl']);
		//returns "uploads/"
				
		//get uploads base subdir
		$rsmd_current_upload_subdir = $rsmd_current_upload_dir['subdir'];
		//returns "/yyyy/mm"
		
		//curent file name with subdir and baseurl
		$rsmd_current_post_attached_file = '/'.$rsmd_current_upload_dir_basename.$rsmd_current_upload_subdir.'/'.wp_basename($rsmd_current_post_original_path);
		//returns "/uploads/yyyy/mm/filename.ext"
		
		//check if current file is used into posts content
		global $wpdb;
		$rsmd_get_posts_with_this_media = $wpdb->get_results("SELECT post_title FROM $wpdb->posts WHERE post_status='publish' AND post_content LIKE '%".$rsmd_current_post_attached_file."%'", ARRAY_A );
		$rsmd_count_posts_with_this_media = $wpdb->num_rows;

		$rsmd_custom_field_help_addition = null;

		//if post or pages are found
		if(!empty($rsmd_count_posts_with_this_media) && $rsmd_count_posts_with_this_media >= 1) {
			
			$rsmd_posts_checked = array();
			
			//loop into involved posts or pages
			foreach($rsmd_get_posts_with_this_media as $rsmd_get_post_with_this_media) {
				
				//build an array with post titles
				$rsmd_posts_checked[] = $rsmd_get_post_with_this_media['post_title'];
				
			}
			
			//implode obtained array
			$rsmd_posts_checked_imploded = implode(', ', $rsmd_posts_checked);
			
			//print an addition to helps,
			$rsmd_custom_field_help_addition = '
				<p style="margin-top:-10px">
					<small>
						<span class="dashicons dashicons-warning"></span>&nbsp
						<span>'.__('This element is currently included into','rsmdlang').': '.$rsmd_posts_checked_imploded.'</span>
					</small>
				</p>';
				
		}	
		
		//get post meta value
		$rsmd_restricted_media_checkbox_value = get_post_meta($rsmd_current_post_id, '_rsmd_is_restricted', true);
		$rsmd_restricted_media_select_value = get_post_meta($rsmd_current_post_id, '_rsmd_allowed_role', true);
		
		//define if checkbox have to be checked or not
		if(!empty($rsmd_restricted_media_checkbox_value) && $rsmd_restricted_media_checkbox_value === '1') {
			
			$rsmd_restricted_media_checkbox_checked = 'checked';
			
		} else {
			
			$rsmd_restricted_media_checkbox_checked = null;
			
		}
		
		$rsmd_allowed_roles_options = null;
		$rsmd_get_all_role_names = wp_roles()->get_names();
		
		foreach($rsmd_get_all_role_names as $rsmd_role_slug => $rsmd_role_name) {
					
			if($rsmd_role_slug === $rsmd_restricted_media_select_value) {
			
				$rsmd_allowed_roles_options .= '<option value="'.$rsmd_role_slug.'" selected>'.translate_user_role($rsmd_role_name).'</option>';
			
			} else {
				
				$rsmd_allowed_roles_options .= '<option value="'.$rsmd_role_slug.'">'.translate_user_role($rsmd_role_name).'</option>';
				
			}
		}
		
		//add custom checkbox for media restriction
		$rsmd_attachment_form_fileds_array['rsmd_restricted_media_checkbox_value'] = array(
			'label' => __('Is restricted', 'rsmdlang'),
			'input' => 'html',
			'html'  => "<input type='checkbox' ".$rsmd_restricted_media_checkbox_checked." name='attachments[".$rsmd_current_post_id."][rsmd-is-restricted]' class='rsmd-switch' id='attachments[".$rsmd_current_post_id."][rsmd-is-restricted]' /><label for='attachments[".$rsmd_current_post_id."][rsmd-is-restricted]'>&nbsp;</label>",		
			'value' => $rsmd_restricted_media_checkbox_value,
			'helps' => __('Allow download from the URL below only to logged in users', 'rsmdlang').$rsmd_custom_field_help_addition
		);
				
		//get meta id of _wp_attached_file in order to use it as a query parameter in the next custom field
		$rsmd_meta_id = $wpdb->get_var($wpdb->prepare("SELECT meta_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND post_id = %s", $rsmd_current_post_id));
		
		//add custom field for restricted media link
		$rsmd_attachment_form_fileds_array['rsmd_restricted_media_link'] = array(
			'label' => __('URL for downloading', 'rsmdlang'),
			'input' => 'html',
			'html'  => "<input type='text' readonly='readonly' name='attachments[".$rsmd_current_post_id."][rsmd-url]' id='attachments[".$rsmd_current_post_id."][rsmd-url]' value='".get_site_url().'/restricted-media-download/?media='.$rsmd_meta_id."' />",
			'helps' => __('Use this URL to let logged in users download this media', 'rsmdlang')
		);
  
		//add dropdown select for allowed role
		$rsmd_attachment_form_fileds_array['rsmd_restricted_media_allowed_role'] = array(
			'label' => __('Allowed Role', 'rsmdlang'),
			'input' => 'html',
			'html'  => "
				<select name='attachments[".$rsmd_current_post_id."][rsmd-allowed-role]' class='rsmd-allowed-role' id='attachments[".$rsmd_current_post_id."][rsmd-allowed-role]'>
				<option value='all' selected>".__('All','rsmdlang')."</option>".$rsmd_allowed_roles_options."
				</select>
			",
			'helps' => __('Allow download to this role only', 'rsmdlang')
		);  
	
		return $rsmd_attachment_form_fileds_array;

	}
	
	add_filter('attachment_fields_to_edit', 'rsmd_add_media_checkbox', 10, 2);

} else {
	
	error_log('function: "rsmd_add_media_checkbox" already exists');
	
}


if(!function_exists('rsmd_save_media_checkbox')) {
	
	//save custom checkbox function
	function rsmd_save_media_checkbox($rsmd_post_data_array, $rsmd_attachment_array_metatada) {
		
		//get involved post id
		$rsmd_post_id_to_save = $rsmd_post_data_array['ID'];
		
		//if checkox is checked 
		if(!empty($rsmd_attachment_array_metatada['rsmd-is-restricted']) && $rsmd_attachment_array_metatada['rsmd-is-restricted'] === 'on') {
			
			$rsmd_restricted_media_checkbox_value = '1';
			
			//get all role names
			$rsmd_get_all_role_names = wp_roles()->get_names();

			//deal with attachment duplication created by WPML
			$rsmd_get_wpml_active_languages = apply_filters('wpml_active_languages', false);
			
			//if WPML has active languages
			if(!empty($rsmd_get_wpml_active_languages)) {
			  
				//loop into languages
				foreach($rsmd_get_wpml_active_languages as $rsmd_wpml_language) {
					
					$rsmd_wpml_language_code = $rsmd_wpml_language['language_code'];
					
					$rsmd_post_translation_id_to_save = apply_filters('wpml_object_id', $rsmd_post_id_to_save, 'attachment', false, $rsmd_wpml_language_code);
					
					if(!empty($rsmd_post_translation_id_to_save)) {
						
						//check if dropdown select contains a valid role 
						if(isset($rsmd_get_all_role_names[$rsmd_attachment_array_metatada['rsmd-allowed-role']])) {
							
							update_post_meta($rsmd_post_translation_id_to_save, '_rsmd_allowed_role', $rsmd_attachment_array_metatada['rsmd-allowed-role']);
							
						} else {
							
							update_post_meta($rsmd_post_translation_id_to_save, '_rsmd_allowed_role', 'all');
							
						}				
						
						//define current post as restricted
						update_post_meta($rsmd_post_translation_id_to_save, '_rsmd_is_restricted', $rsmd_restricted_media_checkbox_value);	
						
					}
					
				}
				
			} else {			
			
				//check if dropdown select contains a valid role 
				if(isset($rsmd_get_all_role_names[$rsmd_attachment_array_metatada['rsmd-allowed-role']])) {
					
					update_post_meta($rsmd_post_id_to_save, '_rsmd_allowed_role', $rsmd_attachment_array_metatada['rsmd-allowed-role']);
					
				} else {
					
					update_post_meta($rsmd_post_id_to_save, '_rsmd_allowed_role', 'all');
					
				}				
				
				//define current post as restricted
				update_post_meta($rsmd_post_id_to_save, '_rsmd_is_restricted', $rsmd_restricted_media_checkbox_value);	
				
			}
			
			/*failover start*/
			//check original and renamed path
			$rsmd_restricted_post_original_path = get_post_meta($rsmd_post_id_to_save, '_rsmd_before_rename', true);
			$rsmd_restricted_post_scrambled_path = get_post_meta($rsmd_post_id_to_save, '_rsmd_after_rename', true);
			
			//if media was previously renamed
			if(!empty($rsmd_restricted_post_scrambled_path) && !empty($rsmd_restricted_post_original_path)) {

				if(file_exists($rsmd_restricted_post_scrambled_path)) {
			
					//rename back file
					rename($rsmd_restricted_post_scrambled_path,$rsmd_restricted_post_original_path);
					
				}
				
				delete_post_meta($rsmd_post_id_to_save, '_rsmd_before_rename');
				delete_post_meta($rsmd_post_id_to_save, '_rsmd_after_rename');
				
			}	
			/*failover end*/			
			
			$rsmd_restricted_attachments_ids = rsmd_get_restricted_attachments_ids();			
			
			//loop into post id array
			foreach($rsmd_restricted_attachments_ids as $rsmd_restricted_attachments_id) {			
			
				rsmd_add_mod_rewrite_rule($rsmd_restricted_attachments_id);
			
			}
			
			//update rules
			save_mod_rewrite_rules();
			
		} else {
			
			//if checkox is not checked
			$rsmd_restricted_media_checkbox_value = '0';

			//deal with attachment duplication created by WPML
			$rsmd_get_wpml_active_languages = apply_filters('wpml_active_languages', false);
			
			//if WPML has active languages
			if(!empty($rsmd_get_wpml_active_languages)) {
			  
				//loop into languages
				foreach($rsmd_get_wpml_active_languages as $rsmd_wpml_language) {
					
					$rsmd_wpml_language_code = $rsmd_wpml_language['language_code'];
					
					$rsmd_post_translation_id_to_save = apply_filters('wpml_object_id', $rsmd_post_id_to_save, 'attachment', false, $rsmd_wpml_language_code);
					
					if(!empty($rsmd_post_translation_id_to_save)) {
									
						//define current post as not restricted
						update_post_meta($rsmd_post_translation_id_to_save, '_rsmd_is_restricted', $rsmd_restricted_media_checkbox_value);
						
					}
										
				}
				
			} else {			
				
				//define current post as not restricted
				update_post_meta($rsmd_post_id_to_save, '_rsmd_is_restricted', $rsmd_restricted_media_checkbox_value);		
				
			}
			
			/*failover start*/
			//check original and renamed path
			$rsmd_restricted_post_original_path = get_post_meta($rsmd_post_id_to_save, '_rsmd_before_rename', true);
			$rsmd_restricted_post_scrambled_path = get_post_meta($rsmd_post_id_to_save, '_rsmd_after_rename', true);
			
			//if media was previously renamed
			if(!empty($rsmd_restricted_post_scrambled_path) && !empty($rsmd_restricted_post_original_path)) {

				if(file_exists($rsmd_restricted_post_scrambled_path)) {
			
					//rename back file
					rename($rsmd_restricted_post_scrambled_path,$rsmd_restricted_post_original_path);
					
				}
				
				delete_post_meta($rsmd_post_id_to_save, '_rsmd_before_rename');
				delete_post_meta($rsmd_post_id_to_save, '_rsmd_after_rename');
				
			}	
			/*failover end*/
			
			$rsmd_restricted_attachments_ids = rsmd_get_restricted_attachments_ids();			
			
			//loop into post id array
			foreach($rsmd_restricted_attachments_ids as $rsmd_restricted_attachments_id) {			
			
				rsmd_add_mod_rewrite_rule($rsmd_restricted_attachments_id);
			
			}

			//reset rules
			save_mod_rewrite_rules();			
						
		}		
	
		return $rsmd_post_data_array;  
			
	}
	
	add_filter('attachment_fields_to_save', 'rsmd_save_media_checkbox', 10, 2);

} else {
	
	error_log('function: "rsmd_save_media_checkbox" already exists');
	
}