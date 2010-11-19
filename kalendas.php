<?php
/*
Plugin Name: Kalendas
Version: 0.2.4.4
Plugin URI: http://www.sebaxtian.com/acerca-de/kalendas
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
define('KALENDAS_HEADER_V', 1);
define('KALENDAS_XML_V', 1);

add_action('wp_head', 'kalendas_header');
add_action('init', 'kalendas_text_domain');
add_action('admin_menu', 'kalendas_menus');
add_filter('the_content', 'kalendas_content');
add_action('wp_ajax_kalendas_ajax', 'kalendas_ajax');
add_action('wp_ajax_nopriv_kalendas_ajax', 'kalendas_ajax');
register_activation_hook(__FILE__, 'kalendas_activate');

/**
* To declare where are the mo files (i18n).
* This function should be called by an action.
*
* @access public
*/
function kalendas_text_domain() {
	add_thickbox();
	load_plugin_textdomain('kalendas', false, 'kalendas/lang');
}

/**
* Function to add the required data to the header in the site.
* This function should be called by an action.
*
* @access public
*/
function kalendas_header() {

	//Local URL
	$url = get_bloginfo( 'wpurl' );
	$local_url = parse_url( $url );
	$aux_url   = parse_url(wp_guess_url());
	$url = str_replace($local_url['host'], $aux_url['host'], $url);
	
	// Define custom JavaScript function
	echo "<script type='text/javascript'>
	kalendas_i18n_error = '".__("Can\'t read Kalendas Feed", 'kalendas')."';
	kalendas_url = '$url';
	var tb_pathToImage = '".get_option('siteurl')."/".WPINC."/js/thickbox/loadingAnimation.gif';
	var tb_closeImage = '".get_option('siteurl')."/". WPINC."/js/thickbox/tb-close.png';
	</script>
	";
	
	//Declare javascript
	wp_register_script('kalendas', $url.'/wp-content/plugins/kalendas/kalendas.js', array('sack', 'thickbox'), KALENDAS_HEADER_V);
	wp_enqueue_script('kalendas');
	
	//Define custom CSS URI
	$css = get_theme_root()."/".get_template()."/kalendas.css";
	if(file_exists($css)) {
		$css_register = get_bloginfo('template_directory')."/kalendas.css";
	} else {
		$css_register = kalendas_plugin_url("/css/kalendas.css");
	}
	//Declare style
	wp_register_style('kalendas', $css_register, false, KALENDAS_HEADER_V);
	wp_enqueue_style('kalendas');
	
	// Declare we use JavaScript SACK library for Ajax
	wp_print_scripts( array( 'kalendas' ));
	wp_print_styles( array( 'kalendas' ));
}

/**
* Function to answer the ajax call.
* This function should be called by an action.
*
* @access public
*/
function kalendas_ajax() {
	//Get the data from the post call
	$source = urldecode($_POST['source']);
	$rand = urldecode($_POST['rand']);

	//Update this calendar
	kalendas_create($source);
	
	//Get the new data from this calendar.
	$results = kalendas_htmlCode($source, $rand);

	// Compose JavaScript for return
	die( $results );
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


function kalendas_i18n( $dateformatstring, $date ) {
	global $wp_locale;
	
	// store original value for language with untypical grammars
	// see http://core.trac.wordpress.org/ticket/9396
	$req_format = $dateformatstring;
	
	if ( ( !empty( $wp_locale->month ) ) && ( !empty( $wp_locale->weekday ) ) ) {
		$datemonth = $wp_locale->get_month( $date->format( 'm' ) );
		$datemonth_abbrev = $wp_locale->get_month_abbrev( $datemonth );
		$dateweekday = $wp_locale->get_weekday( $date->format( 'w' ) );
		$dateweekday_abbrev = $wp_locale->get_weekday_abbrev( $dateweekday );
		$datemeridiem = $wp_locale->get_meridiem( $date->format( 'a' ) );
		$datemeridiem_capital = $wp_locale->get_meridiem( $date->format( 'A' ) );
		$dateformatstring = ' '.$dateformatstring;
		$dateformatstring = preg_replace( "/([^\\\])D/", "\\1" . backslashit( $dateweekday_abbrev ), $dateformatstring );
		$dateformatstring = preg_replace( "/([^\\\])F/", "\\1" . backslashit( $datemonth ), $dateformatstring );
		$dateformatstring = preg_replace( "/([^\\\])l/", "\\1" . backslashit( $dateweekday ), $dateformatstring );
		$dateformatstring = preg_replace( "/([^\\\])M/", "\\1" . backslashit( $datemonth_abbrev ), $dateformatstring );
		$dateformatstring = preg_replace( "/([^\\\])a/", "\\1" . backslashit( $datemeridiem ), $dateformatstring );
		$dateformatstring = preg_replace( "/([^\\\])A/", "\\1" . backslashit( $datemeridiem_capital ), $dateformatstring );

		$dateformatstring = substr( $dateformatstring, 1, strlen( $dateformatstring ) -1 );
	}
	$j = $date->format( $dateformatstring );
	// allow plugins to redo this entirely for languages with untypical grammars
	$j = apply_filters('date_i18n', $j, $req_format, $date->format('U'), false);
	return $j;
}

/**
* Function to update cache if the time elapsed is older than the defined one.
*
* @access public
* @param boolean force Defines if we should update the iRate now or if we have
 to wait for the timestamp.
*/
function kalendas_list_events($source, $rand) {
	
	global $wp_locale;
	
	$md5 = md5($source);
	$answer = $script = false;
	
	$options = get_option('kalendas_options');
	//$filename = kalendas_cache_filename();
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
			$event_list = array_slice($event_list,0,$options['max_events']); //Max number of events to show 
			$num=0;
			foreach($event_list as $event) {
				//Time Zone
				$aux_timeoff = $timeoff; // = get_option('gmt_offset')*(60*60);
				$allday = false;
				if(strlen($event->start)<12) {
					$aux_timeoff = 0;
					$allday = true;
				}
				
				$num++;
				$event_start = new DateTime($event->start);
				$this_title = date_i18n( "M j", $event_start->format('U') + $aux_timeoff );
				$this_numeric = date_i18n( "m d", $event_start->format('U') + $aux_timeoff );
				if(strcmp($this_title,$date_title)!=0) {
					$date_title = $this_title;
					if(!$first) $answer.= "</ul>";
					$first = false;
					
					$formated_title = $date_title;
					if(strcmp($today,$this_numeric)==0) $formated_title = __('Today', 'kalendas');
					if(strcmp($tomorrow,$this_numeric)==0) $formated_title = __('Tomorrow', 'kalendas');
					
					$answer.= "<div class='kalendas-title'>".$formated_title."</div><ul>";
				}
				
				$timezone = get_option('timezone_string');
				$event_start = new DateTime($event->start);
				//$event_start->setTimezone($timezone);
				$event_end = new DateTime($event->end);
				//$event_end->setTimezone($timezone);
				$aux_timeoff = 0;
				
				//Event window
				$file = get_theme_root()."/".get_template()."/kalendas_event.tpl"; //Form from the theme?
				if(!file_exists($file)) $file = ABSPATH."wp-content/plugins/kalendas/templates/kalendas_event.tpl"; //Nope
				$window = kalendas_readfile($file);
				$window = str_replace('%title%', $event->title, $window);
				$kalendas_date_format = $options['date_format'];
				if($allday) $kalendas_date_format = $options['date_format_allday'];
				
				//$window = str_replace('%when_start%', $event_start->format($kalendas_date_format.' T'), $window );
				$window = str_replace('%when_start%', kalendas_i18n( $kalendas_date_format, $event_start), $window );
				$window = str_replace('%when_end%', kalendas_i18n( $kalendas_date_format, $event_end), $window);
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
		//Add an Ajax function to update the gallery
		$nonce = wp_create_nonce('kalendas');
		$link="<div style='width: 100%; padding:7px;' ><div id='throbber-kalendas$rand' class='kalendas-img-off'><a style='cursor : pointer;' onclick=\"var aux = document.getElementById('throbber-kalendas$rand');
				aux.setAttribute('class', 'kalendas-img-on');
				aux.setAttribute('className', 'kalendas-img-on'); //IE sucks
				kalendas_feed( '$source', $rand );\">".__('Update','kalendas')."</a></div></div>
		";
	}
		
	return apply_filters('comment_text', $answer).$script.$link;

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
	$now = current_time("timestamp", 0);
	list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = split( '([^0-9])', $today );
	$hour  = (int)$hour;
	
	switch($options['hours_update']) {
		case 1:
			$timestamp = $now+(60-$second+(59-$minute)*60);
			break;
		case 24:
			$timestamp = $now+(60-$second+(59-$minute)*60+(23-$hour)*(60*60));
			break;
	}
	//Calculate 'midnight' in server Time Zone
	$timeoff = get_option('gmt_offset')*(60*60);
	$timestamp = $timestamp - $timeoff;
	
	//                      H        M  S      M          D            Y
	$cicle_begin = mktime(date("H"), 0, 0, date("n"), date("j"), date("Y"));
	
	$begin = $options['days_begin'];
	if($begin<31) {
		$start_date = 	mktime(date("H")-$hour, 0, 0, date("n"), date("j")-$begin, date("Y"));
	} else { // Months!!!!
		$months = 1;
		if($begin == 62) $months = 2;
		if($begin == 182) $months = 6;
		if($begin == 365) $months = 12;
		$start_date = 	mktime(date("H")-$hour, 0, 0, date("n")-$months, date("j"), date("Y"));
	}
	$start_date = date("Y-m-d\TH:i:s", $start_date);
	
	$end = $options['days_end'];
	if($end<31) {
		$end_date = 	mktime(date("H")-$hour+23, 59, 59, date("n"), date("j")+$end, date("Y"));
	} else { // Months!!!!
		$months = 1;
		if($end == 62) $months = 2;
		if($end == 182) $months = 6;
		if($end == 365) $months = 12;
		$end_date = 	mktime(date("H")-$hour+23, 59, 59, date("n")+$months, date("j"), date("Y"));
	}
	$end_date = date("Y-m-d\TH:i:s", $end_date);
	
	$url = $source."?start-min=$start_date&start-max=$end_date";
	
	$out = false;
	if($data = kalendas_readfile($url)) { 
		if($data[0]=="<") {
			$data = new SimpleXMLElement($data); //Parse the XML
			$out="<?xml version = '1.0' encoding = 'UTF-8'?><events version='".KALENDAS_XML_V."' timestamp='$timestamp'>";
			foreach($data->entry as $item) {
				$gd = $item->children('http://schemas.google.com/g/2005');
				$where_attr = $gd->where->attributes();
				$when_attr = $gd->when; //->attributes();
				for($aux = 0; $aux<count($when_attr); $aux++) {
					if(strstr($gd->eventStatus->attributes()->value,'confirmed')) {
						$out.="<event><title>".htmlspecialchars($item->title)."</title><where>{$where_attr->valueString}</where><start>{$when_attr[$aux]->attributes()->startTime}</start><end>{$when_attr[$aux]->attributes()->endTime}</end><description>".htmlspecialchars($item->content)."</description></event>"; //Add the entry to the XML file
					}
				}
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
		$data = new SimpleXMLElement($data); //Pase XML
		$attr = $data->attributes();
		$timestamp = $attr->timestamp;//Get version

		if($timestamp<=time()) { //Is older	echo "$timestamp<=".time();

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
function kalendas_htmlCode( $source, $rand=false ) {
	
	$md5 = md5($source);
	
	//Suppose we don't have to update
	$update=false;
	
	if(!$rand) $rand = mt_rand(111111,999999);
		
	if(!kalendas_not_ready_file($source)) { //If it's a readable file
		//We have data, so we can try to show the photo.
		$answer = kalendas_list_events($source, $rand);
	} else { 
		//Try to update the gallery.
		if(kalendas_create($source)) { //If we can update the file
			//We have data, so we can try to show the photo.
			$answer = kalendas_list_events($source, $rand);
		} else { 
			$answer = __('Can\'t create the events list. Check your options.', 'kalendas' );
		}
	}
	
	return "<div id='kalendas$rand'>$answer</div>";

}


/**
* Returns the html code with the 'Ajax' script and div.
* Add this code into the html page to create the RSS reader.
*
* @access public
* @return string The HTML code.
*/
function kalendas_container( $source ) {
	$md5 = md5($source);
	$answer="";
	
	$rand = mt_rand(111111,999999);
	$url = kalendas_plugin_url('/ajax/content.php');
	$nonce = wp_create_nonce('kalendas');
	// Create the post to ask for the rss feeds
	$post="nonce=$nonce&amp;source=$source";
	// Create the div where we want the feed to be shown, and the instance of kalendas_feed
	$answer.="\n<div id='kalendas$rand' class='kalendas'><table><tr><td><img class='kalendas' src='".get_bloginfo('wpurl')."/wp-content/plugins/kalendas/img/loading.gif' alt='RSS' border='0' /></td><td>".__('Loading Events list...','kalendas')."</td></tr></table></div><script type='text/javascript'>kalendas_feed('$source', $rand);</script>";
	return $answer;
}


/**
* Enable menus.
* This function should be called by an action.
*
* @access public
*/
function kalendas_menus() {
	add_options_page('Kalendas', 'Kalendas', 'manage_options', 'kalendasoptions', 'kalendas_options');
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
			'date_format_allday'=>'l, M j. Y',
			'days_begin'=>0,
			'days_end'=>7,
			'max_events'=>10,
			'hours_update'=>24 );
		update_option( 'kalendas_options', $options);
	}
	
	if(!isset($options['date_format_allday'])) {
		$options['date_format_allday'] = 'l, M j. Y';
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
	$mode_x = $mode = false;

	$options = get_option('kalendas_options');
	if(!$options) {
		$options = array( 
			'date_format'=>'l, M j. Y h:i A',
			'date_format_allday'=>'l, M j.',
			'days_begin'=>0,
			'days_end'=>7,
			'max_events'=>10,
			'hours_update'=>24 );
	}

	if(isset($_POST['mode_x'])) $mode_x=$_POST['mode_x']; //Someting to execute?
	if(isset($_POST['mode'])) $mode=$_GET['mode']; //Something to show?
	
	switch($mode_x) {
		case 'manage_x': //Update the config data
			$mode='manage';
			
			$options['date_format'] = $_POST['date_format'];
			$options['date_format_allday'] = $_POST['date_format_allday'];
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
* Filter to manage contents. Check for [kalendas] tags.
* This function should be called by a filter.
*
* @access public
* @param string content The content to change.
* @return The content with the changes the plugin have to do.
*/
function kalendas_content($content) {
	//Show a specific event list
	$search = "@(?:<p>)*\s*\[kalendas\s*:([^,]+),([^\]]+)\]\s*(?:</p>)*@i";
	if(preg_match_all($search, $content, $matches)) {
		if(is_array($matches)) {
			foreach($matches[0] as $key=>$search) {
				// Get data from tag
				$title = $matches[1][$key];
				$source = urldecode($matches[2][$key]);
				$source = str_replace('/public/basic', '/public/full', $source);
								
				$replace = "<h2>$title</h2>"; 
				if(kalendas_not_ready_file($source)) {
					$replace.= kalendas_container($source);
				} else {
					$replace.= kalendas_htmlCode($source);
				}
				
				$content = str_replace ($search, $replace, $content);
			}
		}
	}
	return $content;
}

/**
* Kalendas widget stuff (New MultiWidget )
*
*/
	
// check version. only 2.8 WP support class multi widget system
global $wp_version;
if((float)$wp_version >= 2.8) { //The new widget system
	
	class KalendasWidget extends WP_Widget {
	
	/**
		 * constructor
		 */	 
		function KalendasWidget() {
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
				echo kalendas_container($source);	
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
			$default = 	array('title'=> '', 'source' => '');
			$instance = wp_parse_args( (array) $instance, $default );
			
			//Show the widget control.
			include('templates/kalendas_widget.php');
		}
	}

	/* register widget when loading the WP core */
	add_action('widgets_init', 'kalendas_register_widgets');

	function kalendas_register_widgets() {
		register_widget('KalendasWidget');
	}

}

/**
* A kind of readfile function to determine if use Curl or fopen.
*
* @access public
* @param string filename URI of the File to open
* @return The content of the file
*/
function kalendas_readfile($filename)
{
	//Just to declare the variables
	$data = false;
	$have_curl = false;
	$local_file = false;
	
	if(function_exists('curl_init')) { //do we have curl installed?
		$have_curl = true;
	}
	
	$search = "@([\w]*)://@i"; //is the file to read a local file?
	if (!preg_match_all($search, $filename, $matches)) {
		$local_file = true;
	}
	
	if($local_file) { //A local file can be handle by fopen
		if($fop = @fopen($filename, 'r')) {
			$data = null;
			while(!feof($fop))
				$data .= fread($fop, 1024);
			fclose($fop);
		}
	} else { //Oops, an external file
		if($have_curl) { //Try with curl
			if($ch = curl_init($filename)) {
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$data=curl_exec($ch);
				curl_close($ch);
			}
		} else { //Try with fsockopen
			$url = parse_url($filename);
			
			if($fp = fsockopen($url['host'], 80)) {
				//Send GET data
				fputs($fp, "GET " . $url['path'] . "?" . $url['query'] . " HTTP/1.1\r\n");
				fputs($fp, "HOST: " . $url['host'] . " \r\n");
				fputs($fp, "Connection: close \r\n\r\n");
				 
				//Read data
				while(!feof($fp))
				    $data .= fgets($fp, 1024);
				fclose($fp);
				
				$chunked = false;
				$http_status = trim(substr($data, 0, strpos($data, "\n")));
				if ( $http_status != 'HTTP/1.1 200 OK' ) {
					die('The web service endpoint returned a "' . $http_status . '" response');
				}
				if ( strpos($data, 'Transfer-Encoding: chunked') !== false ) {
					$temp = trim(strstr($data, "\r\n\r\n"));
					$data = '';
					$length = trim(substr($temp, 0, strpos($temp, "\r")));
					while ( trim($temp) != "0" && ($length = trim(substr($temp, 0, strpos($temp, "\r")))) != "0" ) {
						$data .= trim(substr($temp, strlen($length)+2, hexdec($length)));
						$temp = trim(substr($temp, strlen($length) + 2 + hexdec($length)));
					}
				} elseif ( strpos($data, 'HTTP/1.1 200 OK') !== false ) {
					$data = trim(strstr($data, "\r\n\r\n"));
				}
			}
		}
	}

	return $data;
}

?>
