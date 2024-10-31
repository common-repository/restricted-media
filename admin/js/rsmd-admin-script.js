jQuery(document).ready(function() {
			
	/*auothide messages*/
	var rsmdSettingErrorHeight = jQuery('#setting-error-rsmd-message').height();
	
	if(rsmdSettingErrorHeight > 0) {
		jQuery('#setting-error-rsmd-message').delay(3000).fadeTo( 100 , 0, function() {
			jQuery('#setting-error-rsmd-message').slideUp( rsmdSettingErrorHeight, function() {
				jQuery('#setting-error-rsmd-message').remove();
			});
		});
	} 
	
	/*auothide waringns*/
	var rsmdSettingInfoHeight = jQuery('#setting-error-rsmd-info').height();
	
	if(rsmdSettingInfoHeight > 0) {
		jQuery('#setting-error-rsmd-info').delay(5000).fadeTo( 100 , 0, function() {
			jQuery('#setting-error-rsmd-info').slideUp( rsmdSettingInfoHeight, function() {
				jQuery('#setting-error-rsmd-info').remove();
			});
		});
	} 
		
	/*prevent user to change page with unsaved changes*/
 	rsmdFormChange = false; 
	window.onbeforeunload = function() {

		if (rsmdFormChange) {
			return "Your unsaved data will be lost."; 
		};
		
	};
 
	jQuery("#rsmd-save-options").click(function() {
		rsmdFormChange = false;
	});
 
	jQuery("#rsmd-settings-form").change(function() {
		rsmdFormChange = true;
	});
	
	
});