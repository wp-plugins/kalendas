	var loading_kalendas_img = new Image(); 
	loading_kalendas_img.src = kalendas_url+'/wp-content/plugins/kalendas/img/loading-page.gif';
	
	function kalendas_feed( source, rand )
	{
		var kalendas_sack = new sack(kalendas_url+'/wp-admin/admin-ajax.php' );
		
		//Our plugin sack configuration
		kalendas_sack.execute = 0;
		kalendas_sack.method = 'POST';
		kalendas_sack.setVar( 'action', 'kalendas_ajax' );
		kalendas_sack.element = 'kalendas'+rand;
		
		//The ajax call data
		kalendas_sack.setVar( 'source', source );
		kalendas_sack.setVar( 'rand', rand );
		
		//What to do on error?
		kalendas_sack.onError = function() {
			var aux = document.getElementById(kalendas_sack.element);
			aux.innerHTMLsetAttribute=kalendas_i18n_error;
		};
		
		kalendas_sack.onCompletion = function() {
			tb_init('a.thickbox, area.thickbox, input.thickbox');
		}
		
		kalendas_sack.runAJAX();
		
		return true;

	} // end of JavaScript function kalendas_feed
