<?php
/*
Plugin Name: Kalendas
Version: 0.1
Plugin URI: http://www.sebaxtian.com/acerca-de/por-hacer
Description: Display your Google Calendar events.
Author: Juan Sebastián Echeverry
Author URI: http://www.sebaxtian.com/
*/

/* Copyright 2010 Juan Sebastián Echeverry (email : sebaxtian@gawab.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

define('KALENDAS_CACHE_AGE', 3600); //Elapsed time to update cache (1 hour)
define('KALENDAS_MNMX_V', 0.3);
define('KALENDAS_XML_V', 1);

add_action('wp_head', 'kalendas_header');
add_action('init', 'kalendas_text_domain');
add_action('admin_menu', 'kalendas_menus');
add_action('activate_plugin', 'kalendas_activate');

/**
* To declare where are the mo files (i18n).
* This function should be called by an action.
*
* @access public
*/
function kalendas_text_domain() {
	add_thickbox();
	
	load_plugin_textdomain('kalendas', 'wp-content/plugins/kalendas/lang');
}

/**
* Function to add the required data to the header in the site.
* This function should be called by an action.
*
* @access public
*/
function kalendas_header() {
	$css = get_theme_root()."/".get_template()."/kalendas.css";
	if(file_exists($css)) {
		echo "<link rel='stylesheet' href='".get_bloginfo('template_directory')."/kalendas.css' type='text/css' media='screen' />";
	} else {
		echo "<link rel='stylesheet' href='".kalendas_plugin_url("/css/kalendas.css")."' type='text/css' media='screen' />";
	}
	echo "<script type='text/javascript'>	
	var tb_pathToImage = '".get_option('siteurl')."/".WPINC."/js/thickbox/loadingAnimation.gif';
	var tb_closeImage = '".get_option('siteurl')."/". WPINC."/js/thickbox/tb-close.png';
	</script>";
}


/**
* Function to return the url of the plugin concatenated to a string. The idea is to
* use this function to get the entire URL for some file inside the plugin.
*
* @access public
* @param string str The string to concatenate
* @return The URL of the plugin concatenated with the string 
*/
function kalendas_plugin_url($str = '') {

	$aux = '/wp-content/plugins/kalendas/'.$str;
	$aux = str_replace('//', '/', $aux);
	$url = get_bloginfo('wpurl');
	return $url.$aux;
	
}

/**
* Function to sort the events, from older to newer.
*
* @access private
* @param a objet first event
* @param b objet second event
*/
function kalendas_sort($a, $b) {
	$t_a = new DateTime($a->start);
	$t_b = new DateTime($b->start);
	if ($t_a == $t_b) return 0;
	return ($t_a < $t_b) ? -1 : 1;
}


/**
* Function to update cache if the time elapsed is older than the defined one.
*
* @access public
* @param boolean force Defines if we should update the iRate now or if we have
 to wait for the timestamp.
*/
function kalendas_list_events($source) {
	
	global $wp_locale;
	
	$md5 = md5($source);
	
	$options = get_option('kalendas_options');
	//$filename = kalendas_cache_filename();
	$rand = mt_rand(111111,999999);
	if($data = get_transient('kalendas-'.$md5)) {
		$data = new SimpleXMLElement($data);
		$event_list = array();
		$count = 0;
		foreach($data->event as $event) {
			array_push($event_list, $event);
		}
		
		uasort($event_list, "kalendas_sort");
		
		//Slice
		$max_events = $options['max_events'];
		if($max_events) $event_list = array_slice($event_list, 0, $max_events, true); 
		
		$date_title = "";
		
		$today = current_time("mysql", 0);
		list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = split( '([^0-9])', $today );
		
		$today = mktime(0, 0, 0, $today_month, $today_day, $today_year);
		$today = date("m d", $today);
		
		$tomorrow = mktime(0, 0, 0, $today_month, $today_day+1, $today_year);
		$tomorrow = date("m d", $tomorrow);
		
		//Time Zone
		$timeoff = get_option('gmt_offset')*(60*60);
		
		$first = true;
		if(count($event_list)>0) {
			$event_list = array_slice($event_list,0,10); //Max number of events to show 
			$num=0;
			foreach($event_list as $event) {
				$num++;
				$event_start = new DateTime($event->start);
				$this_title = date_i18n( "M j", $event_start->format('U') + $timeoff );
				$this_numeric = date_i18n( "m d", $event_start->format('U') + $timeoff );
				if(strcmp($this_title,$date_title)!=0) {
					$date_title = $this_title;
					if(!$first) $answer.= "</ul>";
					$first = false;
					
					$formated_title = $date_title;
					if(strcmp($today,$this_numeric)==0) $formated_title = __('Today', 'kalendas');
					if(strcmp($tomorrow,$this_numeric)==0) $formated_title = __('Tomorrow', 'kalendas');
					
					$answer.= "<div class='kalendas-title'>".$formated_title."</div><ul>";
				}
				
				$event_start = new DateTime($event->start);
				$event_end = new DateTime($event->end);
				
				//Time Zone
				$timeoff = get_option('gmt_offset')*(60*60);
				
				//Event window
				$file = get_theme_root()."/".get_template()."/kalendas_event.tpl"; //Form from the theme?
				if(!file_exists($file)) $file = ABSPATH."wp-content/plugins/kalendas/templates/kalendas_event.tpl"; //Nope
				$window = mnmx_readfile($file);
				$window = str_replace('%title%', $event->title, $window);
				$kalendas_date_format = $options['date_format'];
				
				$window = str_replace('%when_start%', date_i18n( $kalendas_date_format, $event_start->format('U') + $timeoff), $window );
				$window = str_replace('%when_end%', date_i18n( $kalendas_date_format, $event_end->format('U') + $timeoff), $window);
				$window = str_replace('%where%', $event->where, $window);
				$window = str_replace('%description%', $event->description, $window);
				$window = str_replace('%i18n_when%', __('When', 'kalendas'), $window);
				$window = str_replace('%i18n_where%', __('Where', 'kalendas'), $window);
				
				$answer.= "<li><a href='#TB_inline?height=300&amp;width=450&amp;title=Test&amp;inlineId=event$rand-$num' class='thickbox'>{$event->title}</a><div id='event$rand-$num' style='visibility: hidden; display: none;'>$window</div></li>";
			}
			$answer.= "</ul>";
						
		} else {
			$answer = __('No events programmed.', 'kalendas');
		}
	}
	
	//Section to update the gallery
	if(current_user_can('activate_plugins')) { //can this user update the events list?
		//Add a minimax function to update the gallery
		$rand = mt_rand(111111,999999);
		$nonce = wp_create_nonce('kalendas');
		$link="<div style='width: 100%; padding:7px;' ><div id='throbber-kalendas$rand' class='kalendas-img-off'><a style='cursor : pointer;' onclick=\"var update_kalendas$rand = function() {
			if (kalendas$rand.xhr.readyState == 4 && kalendas$rand.xhr.status==200 ) {
				window.location.reload();
			}
		}
		var kalendas$rand=new minimax('".kalendas_plugin_url('/ajax/content.php')."', false);
		kalendas$rand.setThrobber('throbber-kalendas$rand', 'kalendas-img-on', 'kalendas-img-off');
		kalendas$rand.setFunc(update_kalendas$rand);
		kalendas$rand.post('nonce=$nonce&amp;update=1&amp;source=$source');\" />".__('Update','kalendas')."</a></div></div>
		";
	}
		
	return $answer.$script.$link;

}

/**
* Function to create the cache.
*
* @access public
*/
function kalendas_create( $source ) {

	$md5 = md5($source);

	$options = get_option('kalendas_options');
	$answer = false;
	$today = current_time("mysql", 0);
	list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = split( '([^0-9])', $today );
	$hour  = (int)$hour;
	//                      H        M  S      M          D            Y
	$cicle_begin = mktime(date("H"), 0, 0, date("n"), date("j"), date("Y"));
	
	$begin = $options['days_begin'];
	if($begin<31) {
		$start_date = 	mktime(date("H")-$hour, 0, 0, date("n"), date("j")-$begin, date("Y"));
	} else { // A month!!!!
		$start_date = 	mktime(date("H")-$hour, 0, 0, date("n")-1, date("j"), date("Y"));
	}
	$start_date = date("Y-m-d\TH:i:s", $start_date);
	
	$end = $options['days_end'];
	if($end<31) {
		$end_date = 	mktime(date("H")-$hour+23, 59, 59, date("n"), date("j")+$end, date("Y"));
	} else { // A month!!!!
		$end_date = 	mktime(date("H")-$hour+23, 59, 59, date("n")+1, date("j"), date("Y"));
	}
	$end_date = date("Y-m-d\TH:i:s", $end_date);
	
	$url = $source."?start-min=$start_date&start-max=$end_date";
	
	$out = false;
	if($data = mnmx_readfile($url)) { 
		if($data[0]=="<") {
			$data = new SimpleXMLElement($data); //Parse the XML
			$out="<?xml version = '1.0' encoding = 'UTF-8'?><events version='".KALENDAS_XML_V."' timestamp='$cicle_begin'>";
			foreach($data->entry as $item) {
				$gd = $item->children('http://schemas.google.com/g/2005');
				$where_attr = $gd->where->attributes();
				$when_attr = $gd->when->attributes();
				$out.="<event><title>".htmlspecialchars($item->title)."</title><where>{$where_attr->valueString}</where><start>{$when_attr->startTime}</start><end>{$when_attr->endTime}</end><description>".htmlspecialchars($item->content)."</description></event>"; //Add the entry to the XML file
			}
			$out.="</events>";
		}
	}
		
	if( $out ) { //&& $fwr = fopen(kalendas_cache_filename(), "w") ) {
		/*fwrite($fwr,$out);
		fclose($fwr);
		$answer=true; //Yes, we did it*/
		$answer = set_transient('kalendas-'.$md5, $out, 60*60*24*2); //Every 2 days update....
		while (!get_transient('kalendas-'.$md5)) {
			sleep(1);
		}
		$answer = true;
	}
	
	return $answer;
}

/**
* Check if a file is old or doesn't exists
*
* @access public
* @return bool True if it's old or doesn't exists, false if exists and is the version we need. 
*/
function kalendas_not_ready_file( $source )
{
	$md5 = md5($source);
	$options = get_option('kalendas_options');
	$answer = false;
	if(!$data = get_transient('kalendas-'.$md5)) { //Doesn't exist
		$answer = true; 
	} else { //Exists
		switch($options['hours_update']) {
			case 1:
				$cicle_begin = mktime(date("H"), 0, 0, date("n"), date("j"), date("Y"));
				break;
			case 24:
				$cicle_begin = mktime(0, 0, 0, date("n"), date("j"), date("Y"));
				break;
		}

		$data = new SimpleXMLElement($data); //Pase XML
		$attr = $data->attributes();
		$timestamp = $attr->timestamp;//Get version
		if($timestamp<$cicle_begin) { //Is older
			$answer = true;
		}
	}
	return $answer;
}


/**
* Returns the HTML code to show the rate in the widget.
*
* @access public
* @return string The HTML code.
*/
function kalendas_htmlCode( $source ) {
	
	$md5 = md5($source);
	
	//Suppose we don't have to update
	$update=false;	
		
	if(!kalendas_not_ready_file($source)) { //If it's a readable file
		//We have data, so we can try to show the photo.
		$answer = kalendas_list_events($source);
	} else { 
		//Try to update the gallery.
		if(kalendas_create($source)) { //If we can update the file
			//We have data, so we can try to show the photo.
			$answer = kalendas_list_events($source);
		} else { 
			$answer = __('Can\'t create the events list. Check your options.', 'kalendas' );
		}
	}
	
	return $answer;

}


/**
* Returns the html code with 'minimax' script and div.
* Add this code into the html page to create the RSS reader.
*
* @access public
* @return string The HTML code.
*/
function kalendas_content( $source ) {
	$md5 = md5($source);
	$answer="";
	
	// If we have minimax, go ahead
	if(function_exists('minimax_version') && minimax_version()>=KALENDAS_MNMX_V) {
		$num = mt_rand();
		$url = kalendas_plugin_url('/ajax/content.php');
		$nonce = wp_create_nonce('kalendas');
		// Create the post to ask for the rss feeds
		$post="nonce=$nonce&amp;source=$source";
		// Create the div where we want the feed to be shown, and the instance of minimax
		$answer.="\n<div id='kalendas$num' class='kalendas'><table><tr><td><img class='kalendas' src='".get_bloginfo('wpurl')."/wp-content/plugins/kalendas/img/loading.gif' alt='RSS' border='0' /></td><td>".__('Loading Events list...','kalendas')."</td></tr></table></div><script type='text/javascript'>
		
		var update_eventslist = function() {
			if (mx_kalendas$num.xhr.readyState == 4 && mx_kalendas$num.xhr.status==200 ) {
				var text=mx_kalendas$num.xhr.responseText;
				document.getElementById('kalendas$num').innerHTML=text;
				var tb_pathToImage = '".get_bloginfo( 'wpurl' )."/wp-includes/js/thickbox/loadingAnimation.gif';
				var tb_closeImage = '".get_bloginfo( 'wpurl' )."/wp-includes/js/thickbox/tb-close.png';
				tb_init('a.thickbox, area.thickbox, input.thickbox');
			}
		}
		
		mx_kalendas$num = new minimax('$url', 'kalendas$num');
		mx_kalendas$num.setFunc(update_eventslist);
		mx_kalendas$num.post('$post');
		
		
		</script>";
	} else { // If minimax isn't installed, ask for it to the user
		$answer.= "<div id='kalendas'><label>";
		$answer.= sprintf(__('You have to install <a href="%s" target="_BLANK">minimax %1.1f</a> in order for this plugin to work.', 'kalendas'), "http://wordpress.org/extend/plugins/minimax/", KALENDAS_MNMX_V);
		$answer.= "</label></div>";
	}
	return $answer;
}


/**
* Enable menus.
* This function should be called by an action.
*
* @access public
*/
function kalendas_menus() {
	add_options_page('Kalendas', 'Kalendas', 10, 'kalendasoptions', 'kalendas_options');
}


/**
* Function to create the database and to add options into WordPress
* This function should be called by an action.
*
* @access public
*/
function kalendas_activate() {
	$options = get_option('kalendas_options');
	if(!$options) {
		$options = array(
			'date_format'=>'l, M j. Y h:i A',
			'days_begin'=>0,
			'days_end'=>7,
			'max_events'=>10,
			'hours_update'=>24 );
		update_option( 'kalendas_options', $options);
	}
}

/**
* Page to manage the options.
*
* @access public
*/
function kalendas_options()
{
	global $wpdb;
	$messages=array();

	$options = get_option('kalendas_options');
	if(!$options) {
		$options = array( 
			'date_format'=>'l, M j. Y h:i A',
			'days_begin'=>0,
			'days_end'=>7,
			'max_events'=>10,
			'hours_update'=>24 );
	}

	$mode_x=$_POST['mode_x']; //Someting to execute?
	$mode=$_GET['mode']; //Something to show?
	
	switch($mode_x) {
		case 'manage_x': //Update the config data
			$mode='manage';
			
			$options['date_format'] = $_POST['date_format'];
			$options['days_begin'] = $_POST['days_begin'];
			$options['days_end'] = $_POST['days_end'];
			$options['max_events'] = $_POST['max_events'];
			$options['hours_update'] = $_POST['hours_update'];
						
			// Save the posted value in the database
			update_option( 'kalendas_options', $options);

			// Put an 'options updated' message on the screen
			array_push($messages,__( 'Options saved', 'kalendas' ));
			
			break;
	}
	
	switch($mode) {
		case 'manage':
			break;
	}
	
	//Do we have messages to show?
	if(count($messages)>0) {
		echo "<div class='updated'>";
		foreach($messages as $message) echo "<p><strong>$message</strong></p>";
		echo "</div>";
	}
	
	//Ok, show the dialog
	include('templates/kalendas_options.php');
}

/**
* Kalendas widget stuff (New MultiWidget )
*
*/
	
// check version. only 2.8 WP support class multi widget system
global $wp_version;
if((float)$wp_version >= 2.8) { //The new widget system
	
	class PhWidget extends WP_Widget {
	
	/**
		 * constructor
		 */	 
		function PhWidget() {
			$control_ops = array( 'width' => 420, 'height' => 280 );
			parent::WP_Widget('kalendas', 'Kalendas', array('description' => __('Add an event list to your sidebar.', 'kalendas') ), $control_ops);
			
		}
		
		/**
		 * display widget
		 */	 
		function widget($args, $instance) {
			extract($args, EXTR_SKIP);
			
			$source = $instance['source'];

			
			echo $before_widget;
			if ( strlen($instance['title'])>0 ) { echo $before_title . $instance['title'] . $after_title; };
			
			if(kalendas_not_ready_file($source)) {
				echo kalendas_content($source);	
			} else {
				echo kalendas_htmlCode($source);
			}
			echo $after_widget;
		}
		
		/**
		 *	update/save function
		 */	 	
		function update($new_instance, $old_instance) {
			$new_instance['source'] = urldecode($new_instance['source']);
			$new_instance['source'] = str_replace('/public/basic', '/public/full', $new_instance['source']);
			return $new_instance;
			
		}
		
		/**
		 *	admin control form
		 */	 	
		function form($instance) {
			if(!function_exists('minimax_version') || minimax_version()<KALENDAS_MNMX_V) { ?>
				<p>
					<label>
						<?php printf(__('You have to install <a href="%s" target="_BLANK">minimax %1.1f</a> in order for this plugin to work.', 'kalendas'), "http://wordpress.org/extend/plugins/minimax/", KALENDAS_MNMX_V); ?>
					</label>
				</p><?
			} else {
				$default = 	array('title'=> '', 'source' => '');
				$instance = wp_parse_args( (array) $instance, $default );
				
				//Show the widget control.
				include('templates/kalendas_widget.php');
			}
		}
	}

	/* register widget when loading the WP core */
	add_action('widgets_init', kalendas_register_widgets);

	function kalendas_register_widgets() {
		register_widget('PhWidget');
	}

}

?>
