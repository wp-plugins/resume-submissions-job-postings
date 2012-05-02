<?php
/*
Plugin Name: Resumé Submissions & Job Postings
Plugin URI: http://www.geerservices.com/wordpress-plugins/resume-jobs/
Description: Allows the admin to create and show job postings. Users can submit their resume in response to a posting or for general purposes. 
Version: 2.1.5
Author: Keith Andrews (GSI)
Author URI: http://www.geerservices.com
License: GPL2
*/


global $wpdb;

define( 'MANAGEMENT_PERMISSION', 'edit_pages' ); //The minimum privilege required to manage plugin.
define( 'OLDSUBTABLE', $wpdb->prefix . 'resume_submissions' );
define( 'OLDJOBTABLE', $wpdb->prefix . 'job_postings' );
define( 'SUBTABLE', $wpdb->prefix . 'rsjp_submissions' );
define( 'JOBTABLE', $wpdb->prefix . 'rsjp_job_postings' );

date_default_timezone_set( get_option( 'timezone_string' ) );


//Installer
function resume_install () {

	require_once( dirname( __FILE__ ) . '/installer.php' );

}

register_activation_hook( __FILE__, 'resume_install' );


// Create widget for displaying job postings
include( 'includes/widget.php' );


// BOF Resume Submissions Menu 
add_action( 'admin_menu', 'resume_submission_menu' );

function resume_submission_menu() {		
	add_menu_page( __( 'Resumes &amp; Jobs', 'resume_submission' ), __( 'Resumes &amp; Jobs', 'resume_submission' ), MANAGEMENT_PERMISSION, __FILE__, 'resume_view_all', resume_get_plugin_dir( 'go' ) . '/images/icons/menu-icon.png', 25 );		
	add_submenu_page( __FILE__, __( 'Resume Submissions', 'resume_submission' ), __( 'Resume Submissions', 'resume_submission' ), MANAGEMENT_PERMISSION, __FILE__, 'resume_view_all' );		
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

	
// Set i18n
function resume_load_textdomain() {
	$pluginDir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'resume-submissions-job-postings', false, $pluginDir . '/languages/' );
}
add_action('init', 'resume_load_textdomain');


// Function for adding the Multi-File attachment script
function multiFileScript() {
	//if ( is_page( get_option( 'resume_form_page' ) ) ){
		wp_deregister_script( 'jqueryMultiFile' );
		wp_register_script( 'jqueryMultiFile', resume_get_plugin_dir( 'go' ) . '/includes/jQuery/jquery.multi-file.js' );
		wp_enqueue_script( 'jqueryMultiFile' );
	//}
} 

// Function for adding the settings script
function rsjpSettingsScript() {
	//if ( is_page( get_option( 'resume_form_page' ) ) ){
		wp_deregister_script( 'jqueryRSJPSettings' );
		wp_register_script( 'jqueryRSJPSettings', resume_get_plugin_dir( 'go' ) . '/includes/jQuery/settings.js' );
		wp_enqueue_script( 'jqueryRSJPSettings' );
	//}
}


// Functions for styling
function admin_register_resume_style( $hook ) {
	if( $hook == 'toplevel_page_resume-submissions-job-postings/resume-submission' || $hook == 'resumes-jobs_page_job_postings'
		|| $hook == 'resumes-jobs_page_input_fields' || $hook == 'resumes-jobs_page_settings' || $hook == 'resumes-jobs_page_extra_fields' )
	    wp_enqueue_style( 'resume-admin-custom', plugins_url( '/css/resume-admin-styles.css', __FILE__ ) );
}
function addStyles (){
	wp_enqueue_style( 'resume-style', resume_get_plugin_dir( 'go' ) . '/css/resume-styles.css' );	
}

// Add functions to head
add_action( 'admin_enqueue_scripts', 'admin_register_resume_style' );
add_action( 'resume_css', 'addStyles' );
add_action( 'wp_enqueue_scripts', 'multiFileScript' );
if( $hook == 'resumes-jobs_page_settings' )
	add_action( 'wp_footer', 'rsjpSettingsScript' );

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
	ob_start();
	
	function rsjpFormInclude(){
		include( 'includes/form.php' );
	}
	
	rsjpFormInclude();
	
	$output = ob_get_contents();;
	ob_end_clean();
	
	return $output;
}

// Jobs Page
function jobsDisplay_handler(){
	ob_start();
	
	function rsjpJobsInclude(){
		include( 'includes/display-jobs.php' );
	}
	
	rsjpJobsInclude();
	
	$output = ob_get_contents();;
	ob_end_clean();
	
	return $output;
}

// Resume Display Page
function resumeDisplay_handler( $atts ){
	ob_start();
	
	extract( shortcode_atts( array (
			 'email' => '',
			 'id'    => '',
			 'job' => '',
			 'limit' => 1000
    ), $atts ) );
	
	if ( $email )
		$condition = 'WHERE email = "' . $email . '"';
	if ( $id )
		$condition = 'WHERE id = "' . $email . '"';
	if ( $job )
		$condition = 'WHERE job = "' . $job . '"';
	
	function rsjpDisplayInclude( $condition, $limit ){
		include( 'includes/display-resumes.php' );
	}
	
	rsjpDisplayInclude( $condition, $limit );
	
	$output = ob_get_contents();;
	ob_end_clean();
	
	return $output;
}

// Add the shortcodes
add_shortcode( 'resumeForm', 'resumeForm_handler' );
add_shortcode( 'jobPostings', 'jobsDisplay_handler' );
add_shortcode( 'resumeDisplay', 'resumeDisplay_handler' );


// Check for the old tables and remove
function removeOldRSJPDBTables(){
	global $wpdb, $current_user;
		
	$userID = $current_user->ID;
	$code   = $_POST['rsjpaction'];
	
	// If dismiss, hide for that user
	if ( isset( $_POST['removeRSJPNoticeDismiss'] ) && $_POST['removeRSJPNoticeDismiss'] == '0' ) {
         add_user_meta( $userID, 'removeRSJPNoticeDismiss', 'true', true );
	}
	
	if ( $code == 'removeOldDB' ){
		$deleteOldSUB = 'DROP TABLE ' . OLDSUBTABLE;
		$deleteOldJob = 'DROP TABLE ' . OLDJOBTABLE;
		$deletedSub = $wpdb->query( $deleteOldSUB );
		$deletedJob = $wpdb->query( $deleteOldJob );
		if ( $deletedSub || $deletedJob ) {
			function removedRSJPDB(){
				echo '<div class="updated">
				   <p>The old tables have been successfully deleted.</p>
				</div>';
			}
			add_action('admin_notices', 'removedRSJPDB');
		} else {
			function removingRSJPDBError(){
				echo '<div class="error">
				   <p>There was an error deleteing the old tables.</p>
				</div>';
			}
			add_action('admin_notices', 'removingRSJPDBError');
		}
	}
	
	if ( current_user_can( 'install_plugins' ) && !get_user_meta( $userID, 'removeRSJPNoticeDismiss' ) ) {
		if( $wpdb->get_var( 'SHOW TABLES LIKE "' . OLDSUBTABLE . '"' ) == OLDSUBTABLE || $wpdb->get_var( 'SHOW TABLES LIKE "' . OLDJOBTABLE . '"' ) == OLDJOBTABLE ){
			function oldRSJPDBNotice(){
				echo '<div class="error">
				   <p>Resume Submissions and Job Postings Notice: You have old tables in your database that have been updated.<br>
				   <b>Make sure your data has transfered successfully before deleting!</b> <i>(Your data should be seen in the submissions and jobs pages... like nothing happened)</i><br />
				   Either click <i>Delete</i> to remove the old tables or <i>Dismiss</i> to remove this warning and keep the tables in your database.<br />
				   <br />
				   <form method="post" enctype="multipart/form-data" style="float:left; clear:left; width:100px;">
				   	   <input type="hidden" name="rsjpaction" value="removeOldDB">
				   	   <input name="deleteDBTBLs" type="submit" value="Delete" class="button-secondary" />
				   </form>
				   <form method="post" enctype="multipart/form-data" style="float:left; clear:right; width:100px;">
				       <input type="hidden" name="removeRSJPNoticeDismiss" value="0">
				   <input name="removeRSJPNotice" type="submit" value="Dismiss" class="button-secondary" />
				   </form></p>
				   <br clear="all">
				</div>';
			}
			add_action('admin_notices', 'oldRSJPDBNotice');
		}
	}
}
add_action('init', 'removeOldRSJPDBTables' );



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