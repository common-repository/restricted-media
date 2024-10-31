<?php
//if this file is called directly, abort.
if(!defined('ABSPATH')) die('please, do not call this page directly');

if(!function_exists('rsmd_check_involved_post')) {
	
	//do the check and redirect if something goes wrong
	function rsmd_check_involved_post($rsmd_involved_post_id) {
		
		$rsmd_current_site_lang = null;
		
		$rsmd_get_post_language_details = apply_filters('wpml_post_language_details', null, $rsmd_involved_post_id);
				
		if(!empty($rsmd_get_post_language_details)) {
			
			$rsmd_current_site_lang = $rsmd_get_post_language_details['language_code'];
			$rsmd_options_name = '_rsmd_options_'.$rsmd_current_site_lang;
			
		} else {
			
			$rsmd_options_name = '_rsmd_options';
			
		}

		//get and check saved options
		$rsmd_saved_options = get_option($rsmd_options_name);			
		
		if(empty($rsmd_saved_options) || empty($rsmd_saved_options['redirect_not_logged_in'])) {

				//prevent from loading frontend functions into elementor visual editor
				if(
				
					\Elementor\Plugin::$instance->editor->is_edit_mode()
					|| \Elementor\Plugin::$instance->preview->is_preview_mode()
					
				) {
					
					return;
					
				}

				wp_safe_redirect(wp_login_url());
				die;			
			
		}
		
		$rsmd_page_to_redirect = absint($rsmd_saved_options['redirect_not_logged_in']);

		//if user is not logged in, redirect to the page defined in options
		if(!is_user_logged_in()) {

			//save cookie before redirecting
			global $wp;
			$rsmd_redirect_start_value = add_query_arg($wp->query_vars, home_url($wp->request));
			$rsmd_redirect_start_value_encoded = base64_encode($rsmd_redirect_start_value);
			setcookie('rsmd_redirect_start', $rsmd_redirect_start_value_encoded, current_time( 'timestamp', 1 ) + 3600, '/');
											
			if(
			
				!empty($rsmd_page_to_redirect) 
				&& is_numeric($rsmd_page_to_redirect) 
				&& get_post_status($rsmd_page_to_redirect) === 'publish'
				
			) {
			
				$rsmd_page_to_redirect_permalink = get_permalink($rsmd_page_to_redirect);
				
				if(!empty($rsmd_current_site_lang)) {
					
					$rsmd_page_to_redirect_permalink = apply_filters('wpml_permalink', $rsmd_page_to_redirect_permalink, $rsmd_current_site_lang); 
					
				}
				
				if(
				
					!is_archive() 
					
					|| 
					
						(
				
						is_archive() 
						&& !empty($rsmd_saved_options) 
						&& !empty($rsmd_saved_options['redirect_not_logged_in_archive']) 
						&& (int)$rsmd_saved_options['redirect_not_logged_in_archive'] === 1
					
						)
					
				) {				
							
					wp_safe_redirect($rsmd_page_to_redirect_permalink);
					die;
				
				}
				
			} else {
				
				wp_safe_redirect(wp_login_url());
				die;
				
			}
			
		} else {
			
			//check if media download is allowed
			$rsmd_allowed_role_slug = get_post_meta($rsmd_involved_post_id, '_rsmd_allowed_role', true);
			
			if(!is_array($rsmd_allowed_role_slug)) {
				
				$rsmd_allowed_role_slug = array($rsmd_allowed_role_slug);
			}
			
			if(!empty($rsmd_allowed_role_slug) && !in_array('all',$rsmd_allowed_role_slug)) {
													
				$rsmd_current_user_data = get_userdata(get_current_user_id());
				$rsmd_current_user_role_slug = $rsmd_current_user_data->roles;		
				
				$rsmd_lock_user = false;

				if(is_array($rsmd_allowed_role_slug)) {
					
					if(empty(array_intersect($rsmd_allowed_role_slug, $rsmd_current_user_role_slug))){
						
						$rsmd_lock_user = true;
					}
					
				} else {
				
					if(!in_array($rsmd_allowed_role_slug, $rsmd_current_user_role_slug)) {
					
						$rsmd_lock_user = true;
						
					}
				}
				
				if($rsmd_lock_user === true) {
					
					//save cookie before redirecting
					global $wp;
					$rsmd_redirect_start_value = add_query_arg($wp->query_vars, home_url($wp->request));
					$rsmd_redirect_start_value_encoded = base64_encode($rsmd_redirect_start_value);
					setcookie('rsmd_redirect_start', $rsmd_redirect_start_value_encoded, current_time( 'timestamp', 1 ) + 3600, '/');					
					
					if(!empty($rsmd_page_to_redirect) && is_numeric($rsmd_page_to_redirect) && get_post_status($rsmd_page_to_redirect) === 'publish') {
					
						setcookie('rsmd_not_allowed_role', '1', 0, '/');
						
						$rsmd_page_to_redirect_permalink = get_permalink($rsmd_page_to_redirect);
																
						if(!empty($rsmd_current_site_lang)) {
							
							$rsmd_page_to_redirect_permalink = apply_filters('wpml_permalink', $rsmd_page_to_redirect_permalink, $rsmd_current_site_lang , true);
							
						} 
						
						if(
						
							!is_archive() 
							
							|| 
							
								(
						
								is_archive() 
								&& !empty($rsmd_saved_options) 
								&& !empty($rsmd_saved_options['redirect_not_logged_in_archive']) 
								&& (int)$rsmd_saved_options['redirect_not_logged_in_archive'] === 1
							
								)
							
						) {
					
							do_action('rsmd_user_role_not_allowed', $rsmd_current_site_lang);
																			
							wp_safe_redirect($rsmd_page_to_redirect_permalink);
							die;		

						}							
						
					} else {
						
						wp_safe_redirect(wp_login_url());
						die;
						
					}
					
				}
				
			}						
			
		}
		
	}
	
} else {
	
	error_log('function: "rsmd_check_involved_post" already exists');
	
}

if(!function_exists('rsmd_check_restrictions')) {
	
	function rsmd_check_restrictions() {
		
		//if is 404, maybe it is a restricted-media-download request
		if(is_404()) {
								
			//get current url
			global $wp;
			$rsmd_current_url = home_url($wp->request);
			
			//check if url is restricted-media-download
			if($rsmd_current_url === home_url().'/restricted-media-download') {
				
				//check if a meta_id exists
				if(!empty($_REQUEST['media'])) {
					
					//get involved media
					$rsmd_involved_meta_id = absint($_REQUEST['media']);
					
					//get post id by meta_id
					global $wpdb;
					$rsmd_involved_post_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_id = %s", $rsmd_involved_meta_id));
					
					//if post id is found
					if(!empty($rsmd_involved_post_id)) {
						
						//get post type
						$rsmd_current_post_type = get_post_type($rsmd_involved_post_id);
						
						//go on only if the requested post is an attachment
						if(empty($rsmd_current_post_type) || $rsmd_current_post_type !== 'attachment') {
														
							return;	
						
						} 
						
						//change status header
						status_header(200);
						
						//check if media is restricted						
						$rsmd_is_restricted = get_post_meta($rsmd_involved_post_id, '_rsmd_is_restricted', true);
						
						//check if this page, post, post type is restricted
						if(
						
							!empty($rsmd_is_restricted)
							&& $rsmd_is_restricted === '1'
							
						) {

							//do the check and redirect if something goes wrong
							rsmd_check_involved_post($rsmd_involved_post_id);						

							//if WordPress is at least 5.3 and involved post is an image 
							if(function_exists('wp_get_original_image_path')) {
								
								//get current file path get original image path
								$rsmd_involved_post_original_path = wp_get_original_image_path($rsmd_involved_post_id);
								//returns "/var/www/vhosts/sitename.ext/wp-content/uploads/yyyy/mm/filename.ext"	
									
							} 
							
							//otherwise get attached file
							if(empty($rsmd_involved_post_original_path)) {
								
								$rsmd_involved_post_original_path = get_attached_file($rsmd_involved_post_id);
								//returns "/var/www/vhosts/sitename.ext/wp-content/uploads/yyyy/mm/filename.ext"
								
							}
							
							$rsmd_involved_post_absolute_url = wp_get_attachment_url($rsmd_involved_post_id);
							$rsmd_involved_post_relative_url = str_replace(site_url(),'',$rsmd_involved_post_absolute_url);
							$rsmd_involved_post_mime_type = get_post_mime_type($rsmd_involved_post_id);	

							$rsmd_involved_post_file_name = basename($rsmd_involved_post_original_path);
							
							if(!empty($rsmd_involved_post_relative_url) && !empty($rsmd_involved_post_mime_type) && !empty($rsmd_involved_post_file_name)) {
							
								$rsmd_involved_post_size = filesize($rsmd_involved_post_original_path);
															
								// Force the download
								header('Content-Description: File Transfer');
								header('Content-Type: '.$rsmd_involved_post_mime_type);
								//header('Content-Type: application/octet-stream');
								header('Content-Length: '.$rsmd_involved_post_size);
								header('Content-Disposition: attachment; filename="'.$rsmd_involved_post_file_name.'"');
								header('Expires: 0');
								header('Cache-Control: must-revalidate');
								readfile($rsmd_involved_post_original_path, true);	
																
								exit();

							}								

						//media is not restricted, redirect to media
						} else {
							
							//get uploads directory object
							$rsmd_involved_upload_dir = wp_upload_dir();
							
							//get uploads base url
							$rsmd_involved_upload_baseurl = $rsmd_involved_upload_dir['baseurl'];
							//returns "sitename.ext/wp-content/uploads/"
							
							//get _wp_attached_file postmeta
							$rsmd_involved_post_attached_file = get_post_meta($rsmd_involved_post_id, '_wp_attached_file', true);
							
							//echo $rsmd_involved_upload_baseurl.'/'.$rsmd_involved_post_attached_file;
							wp_safe_redirect($rsmd_involved_upload_baseurl.'/'.$rsmd_involved_post_attached_file);
							die;
							
						}							

					} 

				} 
				
			} 
			
		} else {

			if(!get_the_ID()) return;
			
			$rsmd_involved_post_id = get_the_ID();
			
			$rsmd_is_restricted = get_post_meta($rsmd_involved_post_id, '_rsmd_is_restricted', true);
			
			//check if this page, post, post type is restricted
			if(
				
				!empty($rsmd_is_restricted)
				&& $rsmd_is_restricted === '1'
				
			) {
										
				//do the check and redirect if something goes wrong
				rsmd_check_involved_post($rsmd_involved_post_id);
	
			}
			
		}

	}
	
	add_action('template_redirect', 'rsmd_check_restrictions', 1);
	
} else {
	
	error_log('function: "rsmd_check_restrictions" already exists');
	
}