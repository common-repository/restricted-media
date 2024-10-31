<?php 
//if this file is called directly, abort.
if(!defined('ABSPATH')) die('please, do not call this page directly');

add_settings_section(
'rsmd_redirection_section',
__('Redirect if not logged in','rsmdlang'),
'rsmd_redirection_section_comment',
'rsmd-section'
);

if(!function_exists('rsmd_redirection_section_comment')) {
	
	function rsmd_redirection_section_comment(){
		echo '<span class="rsmd-section-comment">'.__('Define where to redirect not logged in users trying to access restricted contents', 'rsmdlang').'</span>';
	}
	
} else {
	
	error_log('function: "rsmd_redirection_section_comment" already exists');
	
}


add_settings_field(
'rsmd-redirect-not-logged-in',  
__('Redirect not logged in users to this page','rsmdlang'),
'rsmd_redirect_not_logged_in',
'rsmd-section',
'rsmd_redirection_section',
array('rsmd_saved_options' => $rsmd_saved_options, 'class' => 'rsmd-redirect-not-logged-in')
);

if(!function_exists('rsmd_redirect_not_logged_in')) {

	function rsmd_redirect_not_logged_in($rsmd_arguments){
					
		if(!empty($rsmd_arguments['rsmd_saved_options']['redirect_not_logged_in'])) {
			
			$rsmd_saved_option = $rsmd_arguments['rsmd_saved_options']['redirect_not_logged_in'];
			
		} else {
			
			$rsmd_saved_option = null;
			
		}	
		
		$rsmd_page_id_dropdown_args = array(
			'post_type'			=> 'page',
			'post_status'		=> ['publish'],
			'name'				=> '_rsmd_options[redirect_not_logged_in]',
			'id'				=> 'rsmd-redirect-not-logged-in',
			'sort_column'		=> 'menu_order, post_title',
			'echo'				=> 1,
			'show_option_none'  => __('Homepage'),
			'option_none_value' => '0',
			'selected'			=> $rsmd_saved_option,
		);
		
		wp_dropdown_pages($rsmd_page_id_dropdown_args);
		?>

		<p><small><?php echo __('Select a page from the list above','rsmdlang'); ?></small></p>
		
		<?php
		
		$rsmd_get_wpml_active_languages = apply_filters('wpml_active_languages', false);
		
		if(!empty($rsmd_get_wpml_active_languages)) {
		
			?>

			<p>
				<small><b style="color:#CA4A1F"><?php echo __('It only applies to the current WPML language','rsmdlang'); ?></b></small>
			</p>		

			<?php
			
		}	
	}

} else {
	
	error_log('function: "rsmd_redirect_not_logged_in" already exists');
	
}

add_settings_field(
'rsmd-redirect-not-logged-in-archive-archive',  
__('Redirect not logged in users to the above page also if an archive page contains a restricted content','rsmdlang'),
'rsmd_redirect_not_logged_in_archive',
'rsmd-section',
'rsmd_redirection_section',
array('rsmd_saved_options' => $rsmd_saved_options, 'class' => 'rsmd-redirect-not-logged-in-archive')
);

if(!function_exists('rsmd_redirect_not_logged_in_archive')) {

	function rsmd_redirect_not_logged_in_archive($rsmd_arguments){
					
		if(!empty($rsmd_arguments['rsmd_saved_options']['redirect_not_logged_in_archive'])) {
			
			$rsmd_saved_option = $rsmd_arguments['rsmd_saved_options']['redirect_not_logged_in_archive'];
			
		} else {
			
			$rsmd_saved_option = null;
			
		}	
		
		if((int)$rsmd_saved_option === 1) {
			
			$redirect_not_logged_in_archive_checked = 'checked';
			
		} else {
			
			$redirect_not_logged_in_archive_checked = null;
			
		}	
		
		?>
		
		<input type="checkbox" name="_rsmd_options[redirect_not_logged_in_archive]" id="redirect-not-logged-in" value="1" <?php echo $redirect_not_logged_in_archive_checked; ?>>

		<p><small><?php echo __('Prevent from browsing archive page containing restricted elements','rsmdlang'); ?></small></p>
		
		<?php
		
		$rsmd_get_wpml_active_languages = apply_filters('wpml_active_languages', false);
		
		if(!empty($rsmd_get_wpml_active_languages)) {
		
			?>

			<p>
				<small><b style="color:#CA4A1F"><?php echo __('It only applies to the current WPML language','rsmdlang'); ?></b></small>
			</p>		

			<?php
			
		}	
	}

} else {
	
	error_log('function: "rsmd_redirect_not_logged_in_archive" already exists');
	
}

settings_fields("rsmd-section");
do_settings_sections("rsmd-section");

submit_button('Save Settings', 'primary', 'rsmd-save-options');
