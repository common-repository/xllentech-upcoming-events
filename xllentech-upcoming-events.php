<?php
/*
 * Plugin Name: XllenTech Upcoming Events
 * Plugin URI: https://wordpress.org/plugins/xllentech-upcoming-events/
 * Description: A Plugin to display Upcoming Islamic Events from php file
 * Author: XllenTech Solutions
 * Author URI: https://xllentech.com
 * Version: 1.2.5
 * Text Domain: xllentech-upcoming-events
 *
 * License: GPLv2 or later  
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * XllenTech English Islamic Calendar is free software: 
 * you can redistribute it and/or modify it under the terms of 
 * the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * XllenTech English Islamic Calendar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You can see the GNU General Public License at <http://www.gnu.org/licenses/>.
 *
*/
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Xllentech_Upcoming_Events extends WP_Widget {

	// constructor
	public function __construct() {
		/* ... */
		parent::__construct('xllentech_upcoming_events_plugin' , __('XllenTech Upcoming Events', 'xllentech-upcoming-events'),
		array( 'description' => __( 'XllenTech Upcoming Events', 'xllentech-upcoming-events'), ) ); // Args
	}

	// widget form creation
	function form($instance) {
		// Check values
		if( $instance) {
			 $title = esc_attr($instance['title']);
			 $count = esc_attr($instance['count']);
			 $page_id = esc_attr($instance['page_id']);
		} else {
			 $title = '';
			 $count = '';
			 $page_id = '';
		}
		
		?>

		<p>
		<label for="<?php _e( $this->get_field_id('title') ); ?>"><?php _e('Widget Title', 'xllentech-upcoming-events'); ?></label>
		<input class="widefat" id="<?php _e( $this->get_field_id('title') ); ?>" name="<?php _e( $this->get_field_name('title') ); ?>" type="text" value="<?php _e( $title ); ?>" />
		</p>
		<p>
		<label for="<?php _e( $this->get_field_id('count') ); ?>"><?php _e('Number of events to show', 'xllentech-upcoming-events'); ?>:</label>
		<input id="<?php _e( $this->get_field_id('count') ); ?>" name="<?php _e( $this->get_field_name('count') ); ?>" type="text" value="<?php _e( $count ); ?>" size="2" />
		</p>
		<p>
		<label for="<?php _e( $this->get_field_id('page_id') ); ?>"><?php _e('If any, Calendar Page ID', 'xllentech-upcoming-events'); ?>:</label>
		<input id="<?php _e( $this->get_field_id('page_id') ); ?>" name="<?php _e( $this->get_field_name('page_id') ); ?>" type="text" value="<?php _e( $page_id ); ?>" size="3" />
		</p>

		<?php
	}
	/*
	This code is simply adding 3 fields to the widget. The first one is the widget title, the second a text field, and the last one is a textarea. Let’s see now how to save and update each field value with the update() function.
	*/

// update widget
function update($new_instance, $old_instance) {
      $instance = $old_instance;
      // Fields
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['count'] = strip_tags($new_instance['count']);
      $instance['page_id'] = strip_tags($new_instance['page_id']);
     return $instance;
	}

	// display widget
function widget($args, $instance) {
	global $wpdb;
	extract( $args );
	
   // these are the widget options
   $title = apply_filters('widget_title', $instance['title']);
   $count = apply_filters('widget_title', $instance['count']);
   $page_id = apply_filters('widget_title', $instance['page_id']);
   
   $err_msg ="";
   $output ="";
   _e( $before_widget );
   
   // Display the widget
  // _e( '<div class="widget-text wp_widget_plugin_box">' );

   // Check if title is set
   if ( $title ) {
      _e( $before_title . $title . $after_title );
   }
	
	date_default_timezone_set('America/Denver');

//include php file with islamic events data, 1st from Child theme or 2nd Plugin folder
	$XUE_datafile = get_stylesheet_directory() . '/xllentech-calendar-data.php';
	$XUE_datafile1 = plugin_dir_path( __FILE__ ) . 'xllentech-calendar-data.php';
	
	if ( file_exists($XUE_datafile) )
		include $XUE_datafile;
	else if ( file_exists($XUE_datafile1) )
		include $XUE_datafile1;
	else {
		$err_msg="Oops, Events Data file not found in either Theme directory or this Plugin directory. Please make sure Data file 'xllentech-calendar-data.php' exists in either location. Nothing to display for now.";
		_e( $err_msg );
		return;
	}
	
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	 
	// check for plugin using plugin name
	if ( ! is_plugin_active( 'xllentech-english-islamic-calendar/xllentech-english-islamic-calendar.php' ) ) {
		//plugin is not active
		$err_msg .= '<p><strong>Please make sure <a href="https://wordpress.org/plugins/xllentech-english-islamic-calendar/" target="_blank">Xllentech English Islamic Calendar</a> Plugin is installed and active. It\'s required for the Xllentech Upcoming Events to work, Due to it\'s reliance on Islamic Dates.</strong></p>';
		_e( $err_msg );
		return;
	}
	
	// check for plugin using plugin name
	$xc_options = get_option("xc_options");
	if ( ! is_array( $xc_options ) ) {
		//plugin is not active
		$err_msg .= '<p><strong>Please make sure <a href="https://wordpress.org/plugins/xllentech-english-islamic-calendar/" target="_blank">Xllentech English Islamic Calendar</a> Plugin is installed and active. It\'s required for the Xllentech Upcoming Events to work, Due to it\'s reliance on Islamic Dates.</strong></p>';
		_e( $err_msg );
		return;
		
	}

	$islamic_months = explode(",", $xc_options['islamic_months']);
	$islamic_month_days = explode(",", $xc_options['islamic_month_days']);

	$english_currentdate=time();
	$english_currentday=date("j",$english_currentdate);
	$english_currentmonth=date("n",$english_currentdate);
	$english_currentyear=date("Y",$english_currentdate);
	
	$month_days_table = $wpdb->prefix . 'month_days'; 
	$month_firstdate_table = $wpdb->prefix . 'month_firstdate';
	

	$output .="<div class='xllentech_upcoming_events_widget'>";
	
	$islamic_date_data = $wpdb->get_results( "SELECT islamic_day,islamic_month,islamic_year FROM " .$month_firstdate_table. " WHERE english_year=" . $english_currentyear . " and english_month=" . $english_currentmonth );
	
	if( count($islamic_date_data)>0 ){
		foreach( $islamic_date_data as $results ) {
			$islamic_day=$islamic_date_data[0]->islamic_day;
			$islamic_month=$islamic_date_data[0]->islamic_month;
			$islamic_year=$islamic_date_data[0]->islamic_year;
		}
	}
	else {
		$err_msg="Oops, Looks like Islamic Date is missing in the database, Please make sure Xllentech English Islamic Caledar is Installed, Active and Error-free. OR create support ticket at <a href='https://wordpress.org/support/plugin/xllentech-upcoming-events'>Plugin Support</a>";
		_e( $err_msg . "</div>");
		return;
	}
		
		$month_data = $wpdb->get_results( "SELECT days FROM ".$month_days_table." WHERE year_number=".$islamic_year ." and month_number=".$islamic_month );
		if( count( $month_data ) > 0 ) {
			foreach( $month_data as $xue_data ) {
			$islamic_month_days[$islamic_month]=$xue_data->days;
			}    
		}
	
	$output .= "<ul class='xllentech_upcoming_events'>";
			
 	$counter=0;		
	for ($i=1; $i<=130; $i++) {
		if($i>=$english_currentday) {
			if(!empty($islamic_events[$islamic_month][$islamic_day][0])) {
				$output .="<li style='list-style:none;margin:0 5px;padding:0 5px;'><span class='xllentech-event-desc'>".$islamic_events[$islamic_month][$islamic_day][1]."</span>";
				$output .="<span class='xllentech-event-date'>".$islamic_day."  ".$islamic_months[$islamic_month]."</span></li>";
				$counter++;
				if($counter>=$count) {
					break;
				}
			}
		}
		$islamic_day++;
		If ($islamic_day>$islamic_month_days[$islamic_month]){
		$islamic_day=1;
		$islamic_month++;
			if ($islamic_month>12){
				$islamic_month=1;
				$islamic_year++;
			}
			$month_data = $wpdb->get_results( "SELECT days FROM " . $month_days_table . " WHERE year_number=" . $islamic_year . " and month_number=" . $islamic_month );
    		if(count($month_data)>0) {
				foreach( $month_data as $xue_data ) {
					$islamic_month_days[$islamic_month]=$xue_data->days;
				}    
			}
		}
	}
	
	if( $page_id!=NULL ) {
		$calendar_page=get_page_link($page_id);
		$output .='<li><span class="xllentech-bottom-link"><a href="'.$calendar_page.'">VIEW CALENDAR</a></span></li>';
	}
	
	$output .="</ul></div>";

	_e( $output );
	
	_e( $after_widget );
	} // end function widget
} //end class


// register widget
add_action( 'widgets_init', 'xllentech_upcoming_events_widget' );

function xllentech_upcoming_events_widget() {
	register_widget( 'Xllentech_Upcoming_Events' );
}
/**
 * Register with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
 */
add_action( 'wp_enqueue_scripts', 'xllentech_upcoming_events_css' );

/**
 * Enqueue plugin style-file
 */
function xllentech_upcoming_events_css() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style( 'xllentech-upcoming-events-styles', plugins_url('style.css', __FILE__) );
    wp_enqueue_style( 'xllentech-upcoming-events-styles' );
}
?>