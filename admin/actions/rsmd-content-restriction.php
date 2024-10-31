<?php
//if this file is called directly, abort.
if(!defined('ABSPATH')) die('please, do not call this page directly');

//add restricted metabox to post, page and public custom post types
if(!function_exists('rsmd_add_content_metabox')) {
	
	function rsmd_add_content_metabox() {
					
		//show post meta for all kinds of post
		$rsmd_all_post_types = get_post_types(array('public' => true));
		
		add_meta_box( 
		'rsmd-restricted', 
		__('Restricted Content','rsmdlang'), 
		'rsmd_add_content_metabox_content', 
		$rsmd_all_post_types,
		'side',
		'high'
		);
		
	}
	
	add_action('add_meta_boxes', 'rsmd_add_content_metabox');
	
} else {
	
	error_log('function: "rsmd_add_content_metabox" already exists');
	
}

//custom metabox callback
if(!function_exists('rsmd_add_content_metabox_content')) {
	
	function rsmd_add_content_metabox_content() {
		
		if(!get_the_ID()) return;
		
		$rsmd_is_restricted_role = get_post_meta(get_the_ID(), '_rsmd_allowed_role', true);
		$rsmd_is_restricted = get_post_meta(get_the_ID(), '_rsmd_is_restricted', true);
		
		if(!is_array($rsmd_is_restricted_role)) {
						
			$rsmd_is_restricted_role = array($rsmd_is_restricted_role);
						
		}
		
		if(!empty($rsmd_is_restricted) && $rsmd_is_restricted === '1') {
			
			$rsmd_is_restricted_checked = 'checked';
			
		} else {
			
			$rsmd_is_restricted_checked = null;
		}
		
		
		?>
		
		<input type="hidden" value="<?php echo wp_create_nonce('rsmd-restricted-tag-nonce'); ?>" id="rsmd-restricted-tag-nonce" name="rsmd-restricted-tag-nonce">	
		
		<p>
		
			<?php echo __('Is this content restricted','rsmdlang'); ?>?<br><br>
			<input type="checkbox" name="rsmd-restricted" class="rsmd-switch" id="rsmd-restricted-checkbox" value="1" <?php echo $rsmd_is_restricted_checked; ?> />
			<label for="rsmd-restricted-checkbox">&nbsp;</label>
			
		</p>
		
		<p>
		
			<?php echo __('Which role is allowed to view it','rsmdlang'); ?>?<br><br>
			<select name="rsmd_allowed_role[]" class="rsmd-allowed-role" id="rsmd_allowed_role" multiple>

				<?php
				if(!empty($rsmd_is_restricted_role) && in_array('all', $rsmd_is_restricted_role)) {
				
					?>
				
					<option value="all" selected><?php echo __('All','rsmdlang'); ?></option>
		
					<?php
					
				} else {
					
					?>
				
					<option value="all"><?php echo __('All','rsmdlang'); ?></option>
		
					<?php
					
				}
				
				//get all current WordPress roles
				$rsmd_get_all_role_names = wp_roles()->get_names();
				
				foreach($rsmd_get_all_role_names as $rsmd_role_slug => $rsmd_role_name) {
																	
					if(!empty($rsmd_is_restricted_role) && in_array($rsmd_role_slug, $rsmd_is_restricted_role)) {
						
						?>
					
						<option value="<?php echo $rsmd_role_slug; ?>" selected><?php echo translate_user_role($rsmd_role_name); ?></option>
						
						<?php
					
					} else {
						
						?>
					
						<option value="<?php echo $rsmd_role_slug; ?>"><?php echo translate_user_role($rsmd_role_name); ?></option>
						
						<?php
						
					}
				}
				
				?>
				
			</select>	
		
		</p>
		
		<?php
	}

} else {
	
	error_log('function: "rsmd_add_content_metabox_content" already exists');
	
}

//custom metabox save
if(!function_exists('rsmd_save_content_meta_box')) {
	
	function rsmd_save_content_meta_box($rsmd_post_id_to_save) {
			
		if(!empty($_POST['rsmd-restricted-tag-nonce']) && wp_verify_nonce($_POST['rsmd-restricted-tag-nonce'], 'rsmd-restricted-tag-nonce')) {
		
			if((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || (defined('DOING_AJAX') && DOING_AJAX) || (!current_user_can('edit_post', get_the_ID())) || (wp_is_post_revision( get_the_ID()) !== false)) return;	
						
			if(!empty($_POST['rsmd-restricted']) && $_POST['rsmd-restricted'] === '1') {
				
				if(empty($_POST['rsmd_allowed_role'])) {
					
					update_post_meta($rsmd_post_id_to_save, '_rsmd_allowed_role', array('all'));
					
				} else {
				
					$rsmd_posted_roles = $_POST['rsmd_allowed_role'];
					$rsmd_posted_roles_count = count($rsmd_posted_roles);
					
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
								
								$rsmd_matching_roles = 0;
								
								foreach($rsmd_posted_roles as $rsmd_posted_role) {
									
									if(array_key_exists($rsmd_posted_role,$rsmd_get_all_role_names)) {

										$rsmd_matching_roles++;
									
									}
									
								}
								
								//check if dropdown select contains a valid role 
								if((int)$rsmd_posted_roles_count === (int)$rsmd_matching_roles) {
																
									update_post_meta($rsmd_post_translation_id_to_save, '_rsmd_allowed_role', $rsmd_posted_roles);
									
								} else {
									
									update_post_meta($rsmd_post_translation_id_to_save, '_rsmd_allowed_role', array('all'));
									
								}				
								
								//define current post as restricted
								update_post_meta($rsmd_post_translation_id_to_save, '_rsmd_is_restricted', '1');	
								
							}
							
						}
						
					} else {		
					
						$rsmd_matching_roles = 0;
						
						foreach($rsmd_posted_roles as $rsmd_posted_role) {
							
							if(array_key_exists($rsmd_posted_role,$rsmd_get_all_role_names)) {

								$rsmd_matching_roles++;
							
							}
							
						}
						
						//check if dropdown select contains a valid role 
						if((int)$rsmd_posted_roles_count === (int)$rsmd_matching_roles) {
							
							update_post_meta($rsmd_post_id_to_save, '_rsmd_allowed_role', $rsmd_posted_roles);
							
						} else {
							
							update_post_meta($rsmd_post_id_to_save, '_rsmd_allowed_role', array('all'));
							
						}	
						
					}	
					
				}
				
				update_post_meta($rsmd_post_id_to_save, '_rsmd_is_restricted', '1');			
				
			} else {
				
				delete_post_meta($rsmd_post_id_to_save, '_rsmd_is_restricted');
				delete_post_meta($rsmd_post_id_to_save, '_rsmd_allowed_role');
				
			}
		
		}
			
	}
	
	add_action('save_post', 'rsmd_save_content_meta_box', 10, 3);
	
} else {
	
	error_log('function: "rsmd_save_content_meta_box" already exists');
	
}