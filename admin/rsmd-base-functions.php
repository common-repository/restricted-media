<?php
 //if this file is called directly, abort.
if(!defined('ABSPATH')) die('please, do not call this page directly');

//LOAD LANGUAGES

//load languages functions
if(!function_exists('rsmd_load_languages')){

	function rsmd_load_languages() {

		load_plugin_textdomain(
			'rsmdlang',
			false,
			RSMD_BASE_RELATIVE . '/languages/'
			);

	}
	
	//load languages
	add_action('plugins_loaded', 'rsmd_load_languages');

} else {
	
	error_log('function: "rsmd_load_languages" already exists');
	
}

//STYLES AND SCRIPTS

//admin styles
if(!function_exists('rsmd_register_admin_styles')){
	
	function rsmd_register_admin_styles() {
		
		wp_enqueue_style('rsmd-admin-style', RSMD_BASE_URL .'/admin/css/rsmd-admin-style.css');
		wp_enqueue_script('rsmd-admin-script', RSMD_BASE_URL .'/admin/js/rsmd-admin-script.js', array('jquery'), '', true );
		
	}
	
	//load admin styles and scripts
	add_action('admin_enqueue_scripts', 'rsmd_register_admin_styles');
		
} else {
	
	error_log('function: "rsmd_register_admin_styles" already exists');
	
}

//UNINSTALL

//plugin uninstall function
if(!function_exists('rsmd_plugin_uninstallation')){

	function rsmd_plugin_uninstallation() {
				
		//call plugin options
		global $rsmd_saved_options;
				
		//rewrite rules so that rules added by this plugin will be reset
		save_mod_rewrite_rules();

		//delete all post metas added by this plugin
		delete_post_meta_by_key('_rsmd_is_restricted');
		delete_post_meta_by_key('_rsmd_before_rename');
		delete_post_meta_by_key('_rsmd_after_rename');
		delete_post_meta_by_key('_rsmd_allowed_role');

		//delete this plugin options
		delete_option('_rsmd_options');	

		//deal with multiple options for WPML
		$rsmd_get_wpml_active_languages = apply_filters('wpml_active_languages', false);

		//if WPML has active languages
		if(!empty($rsmd_get_wpml_active_languages)) {
		  
			//loop into languages
			foreach($rsmd_get_wpml_active_languages as $rsmd_wpml_language) {
				
				$rsmd_wpml_language_code = $rsmd_wpml_language['language_code'];
				$rsmd_options_name = '_rsmd_options_'.$rsmd_wpml_language_code;
				delete_option($rsmd_options_name);
				
			}
			
		}	

	}
	
	//uninstall actions
	register_uninstall_hook(RSMD_BASE_FILE, 'rsmd_plugin_uninstallation');
		
}  else {
	
	error_log('function: "rsmd_plugin_uninstallation" already exists');
	
}

//DEACTIVATE

//plugin uninstall function
if(!function_exists('rsmd_plugin_deactivation')){

	function rsmd_plugin_deactivation() {
				
		//rewrite rules
		save_mod_rewrite_rules();

	}
	
	//uninstall actions
	register_deactivation_hook(RSMD_BASE_FILE, 'rsmd_plugin_deactivation');
		
}  else {
	
	error_log('function: "rsmd_plugin_deactivation" already exists');
	
}

//ACTIVATE

//plugin uninstall function
if(!function_exists('rsmd_plugin_activation')){

	function rsmd_plugin_activation() {
		
		require_once RSMD_BASE_PATH . 'admin/rsmd-common-functions.php';
		
		$rsmd_restricted_attachments_ids = rsmd_get_restricted_attachments_ids();
		
		//loop into post id array
		foreach($rsmd_restricted_attachments_ids as $rsmd_restricted_attachments_id) {			
		
			rsmd_add_mod_rewrite_rule($rsmd_restricted_attachments_id);
		
		}		
			
		//rewrite rules
		save_mod_rewrite_rules();

	}
	
	//uninstall actions
	register_activation_hook(RSMD_BASE_FILE, 'rsmd_plugin_activation');
		
}  else {
	
	error_log('function: "rsmd_plugin_activation" already exists');
	
}