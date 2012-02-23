<?php
/*
Plugin Name: Resumé Submissions & Job Postings
Plugin URI: http://www.geerservices.com/wordpress-plugins/resume-jobs/
Description: Allows the admin to create and show job postings. Users can submit their resume in response to a posting or for general purposes. 
Version: 1.8.8.1
Author: Keith Andrews (GSI)
Author URI: http://www.geerservices.com
License: GPL2
*/


global $wpdb;

define( 'MANAGEMENT_PERMISSION', 'edit_pages' ); //The minimum privilege required to manage plugin.
define( 'SUBTABLE', $wpdb->prefix . 'resume_submissions' );
define( 'JOBTABLE', $wpdb->prefix . 'job_postings' );

date_default_timezone_set( get_option( 'timezone_string' ) );


//Installer
function resume_install () {

	require_once( dirname( __FILE__ ) . '/installer.php' );

}

register_activation_hook( __FILE__, 'resume_install' );


// Create widget for displaying job postings
add_action( 'widgets_init', array( 'resume_job_postings', 'register' ) );
register_activation_hook( __FILE__, array( 'resume_job_postings', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'resume_job_postings', 'deactivate' ) );
class resume_job_postings {
	function activate(){
		$data = array( 'title' => '' ,'display' => 5 );
		if ( !get_option( 'resume_job_postings_widget' ) ){
			add_option( 'resume_job_postings_widget' , $data );
		} else {
			update_option( 'resume_job_postings_widget' , $data );
		}
	}
	function deactivate(){
		delete_option( 'resume_job_postings_widget' );
	}
	function control(){
		$data          = get_option( 'widget_name' );
		$widgetTitle   = $data['title'];
		$widgetDisplay = $data['display'];
		?>
        <label for="job_display_title">Title: <input type="text" name="job_display_title" value="<?php echo $widgetTitle; ?>" size="33" /></label><br />
        <label for="job_display_amount">Posts to Display: <input type="text" name="job_display_amount" value="<?php echo $widgetDisplay; ?>" size="5" /></label>	
    	<?php
		if ( isset( $_POST['job_display_title'] ) ){
			$data['title']   = attribute_escape( $_POST['job_display_title'] );
			$data['display'] = attribute_escape( $_POST['job_display_amount'] );
			update_option( 'resume_job_postings_display', $data );
		}
    }
	function widget( $args ){
		global $wpdb;
		
		$data          = get_option( 'resume_job_postings_widget' );
		$widgetTitle   = $data['title'];
		$widgetDisplay = $data['display'];
		
		if ( !$widgetDisplay ){
			$widgetDisplay = 5;
		}
		
		echo $args['before_widget'];
		echo $args['before_title'] . $widgetTitle . $args['after_title'];
		
		$jobs = $wpdb->get_results( 'SELECT id, title, pubDate FROM ' . JOBTABLE . ' ORDER BY pubDate DESC, title DESC LIMIT ' . $widgetDisplay );
		?>
        <ul>
        	<?php
			foreach ($jobs as $job){
				?>
                <li><a href="<?php echo get_option( 'resume_jobs_page' ); ?>?postingID=<?php echo $job->id; ?>"><?php echo $job->title; ?></a><br />
                &nbsp;&nbsp; - <i style="font-size:10px;">Posted: <?php echo date( 'M j, Y', strtotime( $job->pubDate ) ); ?></i></li>
                <?php
			}
		?>
        </ul>
        <?php
		echo $args['after_widget'];
	}
	function register(){
		register_sidebar_widget( 'Current Job Postings', array( 'resume_job_postings', 'widget' ) );
		register_widget_control( 'Current Job Postings', array( 'resume_job_postings', 'control' ) );
	}
}


// BOF Resume Submissions Menu 
add_action( 'admin_menu', 'resume_submission_menu' );

function resume_submission_menu() {		
	add_menu_page( __( 'Resumés &amp; Jobs', 'resume_submission' ), __( 'Resumés &amp; Jobs', 'resume_submission' ), MANAGEMENT_PERMISSION, __FILE__, 'resume_view_all', resume_get_plugin_dir( 'go' ) . '/images/menu-icon.png', 25 );		
	add_submenu_page( __FILE__, __( 'Resumé Submissions', 'resume_submission' ), __( 'Resumé Submissions', 'resume_submission' ), MANAGEMENT_PERMISSION, __FILE__, 'resume_view_all' );		
	add_submenu_page( __FILE__, __( 'Job Postings', 'resume_submission' ), __( 'Job Postings', 'resume_submission' ), MANAGEMENT_PERMISSION, 'job_postings', 'resume_view_jp');		
	add_submenu_page( __FILE__, __( 'Input Fields', 'resume_submission' ), __( 'Input Fields', 'resume_submission' ), MANAGEMENT_PERMISSION, 'input_fields', 'resume_input_fields');
	add_submenu_page( __FILE__, __( 'Settings', 'resume_submission' ), __( 'Settings', 'resume_submission' ), MANAGEMENT_PERMISSION, 'settings', 'resume_settings');
}



//Return path to plugin directory (url/path)
function resume_get_plugin_dir( $type ) {
	if ( !defined( 'WP_CONTENT_URL' ) )
		define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
	if ( !defined('WP_CONTENT_DIR') )
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	if ( $type == 'path' ) { 
		return WP_CONTENT_DIR . '/plugins/' . plugin_basename( dirname( __FILE__ ) ); 
	} else { 
		return WP_CONTENT_URL . '/plugins/' . plugin_basename( dirname( __FILE__ ) ); 
	}

}

function resume_add_menu_favorite( $actions ) {
	$actions['admin.php?page=resume-submissions-job-postings/resume-submission.php'] = array( 'Resumé Submission', 'manage_options' );
	return $actions;
}

add_filter( 'favorite_actions', 'resume_add_menu_favorite' ); //Favorites Menu


if ( is_admin() ) { 
	add_action( 'admin_menu', 'resume_submission_menu' ); //Admin pages
}
		

// Functions for styling
function admin_register_resume_style( $hook ) {
	if( 'toplevel_page_resume-submissions-job-postings/resume-submission' != $hook && 'resumes-jobs_page_job_postings' != $hook )
        return;
    wp_enqueue_script( 'resume_custom_css', plugins_url( '/css/resume-admin-styles.css', __FILE__ ) );
}
function addStyles (){
	wp_enqueue_style( 'resume-style', resume_get_plugin_dir( 'go' ) . '/css/resume-styles.css' );	
}

// Add functions to head
add_action( 'admin_enqueue_scripts', 'admin_register_resume_style' );
add_action( 'resume_css', 'addStyles' );

// Bring in the functions
include( 'includes/functions.php' );
	
// Create Pages
// Main 'View All' Page
function resume_view_all(){
	include( 'includes/submissions.php' );
}

// Job Postings Page
function resume_view_jp(){
	include( 'includes/job-postings.php' );
}

// Input Fields Page
function resume_input_fields(){
	include( 'includes/input-fields.php' );
}

// Settings Page
function resume_settings(){
	include( 'includes/settings.php' );
}

// Form Page
function resumeForm_handler(){
	include( 'includes/form.php' );
}

// Jobs Page
function resumeJobsDisplay_handler(){
	include( 'includes/display-jobs.php' );
}

// Add the shortcodes
add_shortcode( 'resumeForm', 'resumeForm_handler' );
add_shortcode( 'jobPostings', 'resumeJobsDisplay_handler' );


/*  Copyright 2012  Keith Andrews  (email : keith@geerservices.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>