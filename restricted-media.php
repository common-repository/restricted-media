<?php
/*
Plugin Name:		Restricted Media, Pages and Posts
Plugin URI:			https://wordpress.org/plugins/restricted-media/
Description:		Prevent not logged users from downloading the elements of your media library and browse posts or pages that you decide to keep private.
Version:			1.8
Author:				Christian Gatti
Author URI:			https://profiles.wordpress.org/christian-gatti/
License:			GPL-2.0+
License URI:		http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain:		rsmdlang
Domain Path:		/languages
*/
 
//if this file is called directly, abort.
if(!defined('ABSPATH')) die('please, do not call this page directly');

//DEFINITIONS

define('RSMD_BASE_PATH', plugin_dir_path( __FILE__ ));
//this will output /var/www/vhosts/domain.com/wp-content/plugins/my-plugin/

define('RSMD_BASE_FILE', __FILE__ );
//this will output /var/www/vhosts/domain.com/wp-content/plugins/my-plugin/

define('RSMD_BASE_URL', plugins_url().'/'.plugin_basename( __DIR__ ).'/');
//this will output https://domain.com/wp-content/plugins/my-plugin/

define('RSMD_BASE_RELATIVE', dirname( plugin_basename( __FILE__ )));
//this will output /wp-content/plugins/my-plugin/

define('RSMD_PLUGIN_VERSION', 15);

//include base functions
require_once RSMD_BASE_PATH.'/admin/rsmd-base-functions.php';

//DEPENDENCIES

//load dependencies
if(!function_exists('rsmd_load_dependencies')){
	
	function rsmd_load_dependencies() {	

		//set saveds option as global, so that we can use them in every part of the plugin
		global $rsmd_saved_options;
		global $rsmd_options_name;
		
		//get options by language for a perfect compliance with WPML
		$rsmd_current_site_lang = apply_filters( 'wpml_current_language', NULL );
		
		if(!empty($rsmd_current_site_lang)) {
			
			$rsmd_options_name = '_rsmd_options_'.$rsmd_current_site_lang;
			
		} else {
			
			$rsmd_options_name = '_rsmd_options';
			
		}

		//get and check saved options
		$rsmd_saved_options = get_option($rsmd_options_name);

		if(is_admin() && current_user_can('upload_files')) {
					
			//include common functions
			require_once RSMD_BASE_PATH.'/admin/rsmd-common-functions.php';
						
			//include new releases	
			require_once RSMD_BASE_PATH.'/admin/rsmd-new-releases.php';
			
			//include option pages
			require_once RSMD_BASE_PATH.'/admin/rsmd-options.php';
							
			//include actions for media
			require_once RSMD_BASE_PATH.'/admin/actions/rsmd-media-restriction.php';
			
			//include actions for contents
			require_once RSMD_BASE_PATH.'/admin/actions/rsmd-content-restriction.php';
		
		}
		
		if(!is_admin()) {

			//include frontend functions
			require_once RSMD_BASE_PATH.'/public/rsmd-public-functions.php';
			
		}
		
	}
	
	//load dependencies
	add_action('plugins_loaded', 'rsmd_load_dependencies');	
	
} else {
	
	error_log('function: "rsmd_load_dependencies" already exists');
	
}

//ADD SETTINGS LINK

//add settings link in plugin list page
if(!function_exists('rsmd_add_setting_link')){
	
	function rsmd_add_setting_link ($rsmd_setting_links) {
		
		$rsmd_links_to_add = array(
			'<a href="'.admin_url('admin.php?page=rsmd-setup').'" title="Rstricted Media Settings" alt="Restricted Media Settings">'.__('Settings','rsmdlang').'</a>'
		);
			
		return array_merge($rsmd_setting_links, $rsmd_links_to_add);
		
	}

	if(is_admin()) {
		
		add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'rsmd_add_setting_link');
		
	}

} else {
	
	error_log('function: "rsmd_add_setting_link" already exists');
	
}


//OLD PRO PLUGIN ALERT

//display an alert to invite to download the new plugin
if(!function_exists('mnnt_new_plugin_alert')){
	
	function mnnt_new_plugin_alert(){
			
		if(!defined('NFPRCT_BASE_PATH')){
			
			?>
				<div class="notice notice-error">
					<p><?php echo __('A brand new version or "Restricted Media, Pages and Posts" is out, please download it now, since this plugin will be no more maintained. Please download and install it from the <a href="/wp-admin/plugin-install.php?s=nutsforpress&tab=search&type=term" title="NutsForPress" alt"NutsForPress">WordPress repository</a>','rsmdlang'); ?>!</p>
				</div>
			<?php

		} else {
			
			if(defined('RSMD_BASE_PATH')){
			
				?>
					<div class="notice notice-error">
						<p>
						<?php 
						
							echo __('Thanks for downloading "NutsForPress Restricted Contents"','rsmdlang'); 
							echo '! ';
							echo __('Please, do the setup of "NutsForPress Restricted Contents", then deactivate and delete "Restricted Media, Pages and Posts"','rsmdlang');
							
						?>
						</p>
					</div>
				<?php

			}			
			
		}
		
	}
	
	add_action('admin_notices', 'mnnt_new_plugin_alert');	
	
} else {
	
	error_log('function: "mnnt_new_plugin_alert" already exists');
	
}