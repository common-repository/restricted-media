<?php
//if this file is called directly, abort.
if(!defined('ABSPATH')) die('please, do not call this page directly');

if(!function_exists('rsmd_sanitize_entries')) {
		
	function rsmd_sanitize_entries($rsmd_posted_values) {
		
		//prepare array for saving sanitized data
		$rsmd_values_to_save = array();
		
		//introduce a variable to control errors
		$rsmd_erros_found = 0;
		
		//loop into posted data
		foreach($rsmd_posted_values as $rsmd_posted_key => $rsmd_posted_value){
			
			//filter by key
			switch($rsmd_posted_key) {							
				
				//deal with redirect_not_logged_in
				case 'redirect_not_logged_in':
				
				//if the posted value is not numeric or is not and existing page and value is different than zero
				if(!is_numeric($rsmd_posted_value) || (get_post_status($rsmd_posted_value) !== 'publish' && $rsmd_posted_value !== '0')){
					
					$rsmd_posted_value = 0;
					$rsmd_erros_found++;
					$rsmd_message_text = __('One or more values entered were not accepted, so they were set to a default value. Please check it out','rsmdlang').'!';
					
				} else {
					
					$rsmd_posted_value = absint($rsmd_posted_value);
					
					//check if page is restricted
					$rsmd_check_if_page_is_restricted = get_post_meta($rsmd_posted_value, '_rsmd_is_restricted', true);

					//if page is restricted
					if(!empty($rsmd_check_if_page_is_restricted) && $rsmd_check_if_page_is_restricted === '1') {
						
						delete_post_meta($rsmd_posted_value, '_rsmd_is_restricted');
													
						$rsmd_erros_found++;
						$rsmd_message_text = __('The redirect page that you have selected is now no more restricted','rsmdlang').'.';								
						
					} 
				}
				
				//add sanitized value to array to save
				$rsmd_values_to_save['redirect_not_logged_in'] = $rsmd_posted_value;
										
				break;	

				//deal with redirect_not_logged_in_archive
				case 'redirect_not_logged_in_archive':
				
				//if the posted value is not numeric or is not and existing page and value is different than zero
				if((int)$rsmd_posted_value === 1){
					
					$rsmd_posted_value = 1;
					
				} else {
					
					$rsmd_posted_value = 0;
				}
				
				//add sanitized value to array to save
				$rsmd_values_to_save['redirect_not_logged_in_archive'] = $rsmd_posted_value;
										
				break;				
			
			}

		}
		
		if($rsmd_erros_found > 0) {
		
			//info message
			
			$rsmd_message_type = 'warning';
						
			add_settings_error(
				'rsmd-info',
				'rsmd-info',
				$rsmd_message_text,
				$rsmd_message_type				
			);
		
		}
		
		//add plugin version
		$rsmd_values_to_save['plugin_version'] = RSMD_PLUGIN_VERSION;
		
		//save international value
		$rsmd_current_site_lang = apply_filters( 'wpml_current_language', NULL );
		
		if(!empty($rsmd_current_site_lang) && !empty($rsmd_values_to_save)){		
			
			$rsmd_options_int_name = '_rsmd_options_'.$rsmd_current_site_lang;
			update_option($rsmd_options_int_name, $rsmd_values_to_save, false);
			
		}

		//return array to save		
		return $rsmd_values_to_save;
		
	}

}





if(!function_exists('rsmd_register_settings_action')) {
	
	function rsmd_register_settings_action() {

		if(!empty($_POST['rsmd-save-options'])) {
			
			if(!current_user_can('activate_plugins')) {return;}
			
			//can't find out if nonce is checkd on register_setting, so let's check it "manually"
			if(!empty($_POST['rsmd-options-nonce']) && wp_verify_nonce($_POST['rsmd-options-nonce'], 'rsmd-options-nonce')) {
							
				//create an empty option first, otherwise register_setting acts twice
				update_option('_rsmd_options', '', false);
				
				//register settings
				$rsmd_register_options_args = array(
					'type' => 'string', 
					'sanitize_callback' => 'rsmd_sanitize_entries',
					);
					
				register_setting('rsmd-section', '_rsmd_options', $rsmd_register_options_args); 

				//update message
				$rsmd_message_text = __( 'Settings saved', 'rsmdlang' ).'!';
				$rsmd_message_type = 'updated';
						
				add_settings_error(
					'rsmd-message',
					'rsmd-message',
					$rsmd_message_text,
					$rsmd_message_type
				);
				
			}
			

		}

	}
	
	add_action('admin_init', 'rsmd_register_settings_action');

} else {
	
	error_log('function: "rsmd_register_settings_action" already exists');
	
}