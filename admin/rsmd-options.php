<?php
//if this file is called directly, abort.
if(!defined('ABSPATH')) die('please, do not call this page directly');

//define option page parameters
if(!function_exists('rsmd_option_page_parameters')){
	
	function rsmd_option_page_parameters() {	
					
		add_submenu_page(
			'options-general.php',		//parent-page
			'Restricted Media',			//page title
			'Restricted Media',			//menu title
			'activate_plugins',			//capability
			'rsmd-setup',				//menu slug
			'rsmd_setup'				//function
		);
		
	}
	
	//add option page
	add_action('admin_menu', 'rsmd_option_page_parameters');
	
} else {
	
	error_log('function: "rsmd_option_page_parameters" already exists');
	
}


//option page content
if(!function_exists('rsmd_setup')){
	
	function rsmd_setup() {
		
		global $rsmd_saved_options;
		
		?>
		
		<div class="wrap">
			<h1 style="margin-bottom:15px; margin-top:5px;">Restricted Media <?php echo __('Setup','rsmdlang'); ?></h1>

			<form id="rsmd-settings-form" method="post" action="options.php">
			
				<?php
				
				//load option content
				require_once plugin_dir_path(__FILE__).'rsmd-options-content.php';
				
				//can't find out if nonce is checked on register_setting, so let's check it "manually"
				$rsmd_options_nonce = wp_create_nonce('rsmd-options-nonce');
				echo '<input type="hidden" name="rsmd-options-nonce" value="'.$rsmd_options_nonce.'">';
				
				?>
			
			</form>
			
			<h2 class="rsmd-media-list-title"><?php echo __('Media currently subject to restriction','rsmdlang'); ?></h2>
			
			<?php
			
			$rsmd_restricted_attachments_ids = rsmd_get_restricted_attachments_ids();	

			//if restricted media are found
			if(!empty($rsmd_restricted_attachments_ids)) {
				
				echo '<ul class="rsmd-media-list">';
				
				//loop into involved media
				foreach($rsmd_restricted_attachments_ids as $rsmd_restricted_attachments_id) {
						
					$rsmd_restricted_post_title = get_the_title($rsmd_restricted_attachments_id);
				
					//print post name and the link for a rapid edit
					echo '<li><a href="'.admin_url().'upload.php?item='.$rsmd_restricted_attachments_id.'" target="_blank">'.$rsmd_restricted_post_title.'</a></li>';

				}
				
				echo '</ul>';

			//if restricted media are not found
			} else {
				
				//print info
				echo '<p>'.__('No restricted media found','rsmdlang').'</p>';
				
			}

			?>
			
			<h2 class="rsmd-content-list-title"><?php echo __('Contents currently subject to restriction','rsmdlang'); ?></h2>
			
			<?php

			
			$rsmd_restricted_posts_ids = rsmd_get_restricted_posts_ids();	

			//if restricted media are found
			if(!empty($rsmd_restricted_posts_ids)) {
				
				echo '<ul class="rsmd-content-list">';

				//loop into involved contents
				foreach($rsmd_restricted_posts_ids as $rsmd_restricted_posts_id) {
					
					$rsmd_restricted_post_title = get_the_title($rsmd_restricted_posts_id);
					
					//print post name and the link for a rapid edit
					echo '<li><a href="'.admin_url().'post.php?post='.$rsmd_restricted_posts_id.'&action=edit" target="_blank">'.$rsmd_restricted_post_title.'</a></li>';

				}
				
				echo '</ul>';

			//if restricted media are not found
			} else {
				
				//print info
				echo '<p>'.__('No restricted contents found','rsmdlang').'</p>';
				
			}
			
			?>

		</div>
		<?php
				
	}
	
} else {
	
	error_log('function: "rsmd_setup" already exists');
	
}

//include page with sanitize and save functions
if(!function_exists('rsmd_register_settings')){

	function rsmd_register_settings() {
		
		//check capabilty
		if(!current_user_can('activate_plugins')) return;

		if(!empty($_POST['rsmd-save-options'])) {
			require_once plugin_dir_path(__FILE__).'rsmd-options-save.php';
		}  
		
	}
	
	add_action('admin_menu', 'rsmd_register_settings');
	
} else {
	
	error_log('function: "rsmd_register_settings" already exists');
	
}