<?php
/*
Plugin Name: Resume Submissions & Job Postings
Plugin URI: http://www.geerservices.com/wordpress-plugins/resume-jobs/
Description: Allows the admin to create and show job postings. Users can submit their resume in response to a posting or for general purposes. 
Version: 2.5
Author: Keith Andrews (GSI)
Author URI: http://www.geerservices.com
License: GPL2
*/


global $wpdb;

define( 'MANAGEMENT_PERMISSION', 'edit_pages' ); //The minimum privilege required to manage plugin.
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
	add_menu_page( __( 'RSJP Resumes' ), __( 'RSJP Resumes' ), MANAGEMENT_PERMISSION, 'rsjp-submissions', 'resume_view_all', resume_get_plugin_dir( 'go' ) . '/images/icons/menu-icon.png', 25 );		
	add_submenu_page( 'rsjp-submissions', __( 'Resume Submissions' ), __( 'Resume Submissions' ), MANAGEMENT_PERMISSION, 'rsjp-submissions', 'resume_view_all' );						
	add_submenu_page( 'rsjp-submissions', __( 'Input Fields' ), __( 'Input Fields' ), MANAGEMENT_PERMISSION, 'rsjp-input-fields', 'resume_input_fields' );
	add_submenu_page( 'rsjp-submissions', __( 'Settings' ), __( 'Settings' ), MANAGEMENT_PERMISSION, 'rsjp-settings', 'resume_settings' );
}


// Inlcude the page that builds the custom post type for the Job Postings
include( 'rsjp-job-postings.php' );


//Return path to plugin directory (url/path)
function resume_get_plugin_dir( $type ) {
	if( !defined( 'WP_CONTENT_URL' ) )
		define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
	if( !defined('WP_CONTENT_DIR') )
		define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	if( $type == 'path' ) { 
		return WP_CONTENT_DIR . '/plugins/' . plugin_basename( dirname( __FILE__ ) ); 
	} else { 
		return WP_CONTENT_URL . '/plugins/' . plugin_basename( dirname( __FILE__ ) ); 
	}

}

function resume_add_menu_favorite( $actions ) {
	$actions['admin.php?page=rsjp-submissions'] = array( 'Resume Submission', 'manage_options' );
	return $actions;
}

add_filter( 'favorite_actions', 'resume_add_menu_favorite' ); //Favorites Menu


if( is_admin() ) { 
	add_action( 'admin_menu', 'resume_submission_menu' ); //Admin pages
}

	
// Set i18n
function resume_load_textdomain() {
	load_plugin_textdomain( 'resume-submissions-job-postings', false, resume_get_plugin_dir( 'path' ) . '/languages/' );
}
add_action( 'init', 'resume_load_textdomain' );


// Function for adding the Multi-File attachment script
function multiFileScript() {
	wp_deregister_script( 'jqueryMultiFile' );
	wp_register_script( 'jqueryMultiFile', resume_get_plugin_dir( 'go' ) . '/includes/jQuery/jquery.multi-file.js' );
	wp_enqueue_script( 'jqueryMultiFile' );
} 


// Add widget to Dashboard
function rsjp_dashboard_widget_function() {
	include( 'includes/dashboard-widget.php' );
} 

function rsjp_dashboard() {
	wp_add_dashboard_widget( 'rsjp_dashboard_widget', __( 'RSJP - Recently Submitted Resumes' ), 'rsjp_dashboard_widget_function' );	
} 

add_action( 'wp_dashboard_setup', 'rsjp_dashboard' );


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
	if( $hook == 'toplevel_page_rsjp-submissions' || $hook == 'rsjp-resumes_page_rsjp-job-postings' || $hook == 'rsjp-resumes_page_rsjp-input-fields' 
	    || $hook == 'rsjp-resumes_page_rsjp-settings' || $hook == 'rsjp-resumes_page_rsjp-extra-fields' || $hook == 'edit.php' || $hook == 'post-new.php' || $hook == 'post.php' )
	    wp_enqueue_style( 'resume-admin-custom', plugins_url( '/css/resume-admin-styles.css', __FILE__ ) );
}
function addStyles ( $hook ){
	wp_enqueue_style( 'resume-style', resume_get_plugin_dir( 'go' ) . '/css/resume-styles.css' );	
}

// Add functions to head
add_action( 'admin_enqueue_scripts', 'admin_register_resume_style' );
add_action( 'wp_enqueue_scripts', 'addStyles' );
add_action( 'wp_enqueue_scripts', 'multiFileScript' );
if( $hook == 'rsjp-resumes_page_rsjp-settings' )
	add_action( 'wp_footer', 'rsjpSettingsScript' );

// Bring in the functions
include( 'includes/functions.php' );
	
// Create Pages
// Main 'View All' Page
function resume_view_all(){
	include( 'includes/submissions.php' );
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
function jobsDisplay_handler( $atts ){
	ob_start();
	
	extract( shortcode_atts( array (
			 'orderby' => 'post_date',
			 'order'   => 'DESC',
			 'archive' => 'Hide',
			 'limit'   => 1000
    ), $atts ) );
	
	function rsjpJobsInclude( $orderby, $order, $archive, $limit ){
		include( 'includes/display-jobs.php' );
	}
	
	rsjpJobsInclude( $orderby, $order, $archive, $limit );
	
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
	
	if( $email )
		$condition = 'WHERE email = "' . $email . '"';
	if( $id )
		$condition = 'WHERE id = "' . $email . '"';
	if( $job )
		$condition = 'WHERE job = "' . $job . '"';
	
	function rsjpDisplayInclude( $condition, $limit ){
		include( 'includes/display-resumes.php' );
	}
	
	rsjpDisplayInclude( $condition, $limit );
	
	$output = ob_get_contents();
	ob_end_clean();
	
	return $output;
}

// Resume Display Page
function rsjpSubmit_handler( $atts ){
	global $post;
	
	$jobID = get_the_ID();
	$postInfo = get_post( $jobID ); 
	$slug = $postInfo->post_name;

	ob_start();
	
	extract( shortcode_atts( array (
			 'job' => $slug
    ), $atts ) );
	
	function rsjpSubmitFormInclude( $job ){
		include( 'includes/submit-button.php' );
	}
	
	rsjpSubmitFormInclude( $job );
	
	$output = ob_get_contents();;
	ob_end_clean();
	
	return $output;
}

// Add the shortcodes
add_shortcode( 'resumeForm', 'resumeForm_handler' );
add_shortcode( 'jobPostings', 'jobsDisplay_handler' );
add_shortcode( 'resumeDisplay', 'resumeDisplay_handler' );
add_shortcode( 'rsjpSubmit', 'rsjpSubmit_handler' );


// Check for the old tables and remove
function removeOldRSJPDBTables(){
	global $wpdb, $current_user;
		
	$userID = $current_user->ID;
	$code   = $_POST['rsjpaction'];
	
	// If dismiss, hide for that user
	if( isset( $_POST['removeRSJPNoticeDismiss'] ) && $_POST['removeRSJPNoticeDismiss'] == '0' ) {
         add_user_meta( $userID, 'removeRSJPNoticeDismiss', 'true', true );
	}
	
	if( $code == 'removeJobTable' ){
		$deleteOldJob = 'DROP TABLE ' . JOBTABLE;
		$deletedJob = $wpdb->query( $deleteOldJob );
		if ( $deletedJob ) {
			function removedRSJPDB(){
				echo '<div class="updated">
				          <p>The old tables have been successfully deleted.</p>
				      </div>';
			}
			add_action('admin_notices', 'removedRSJPDB');
		} else {
			function removingRSJPDBError(){
				echo '<div class="error">
				          <p>There was an error deleteing the old table.</p>
				      </div>';
			}
			add_action('admin_notices', 'removingRSJPDBError');
		}
	}
	
	if( $code == 'jobsToPost' ) {
		// Transfer jobs into posts
		if( $wpdb->get_var( 'SHOW TABLES LIKE "' . JOBTABLE . '"' ) ) {
			$transferJobs = '';
			$jobs = $wpdb->get_results( 'SELECT * FROM ' . JOBTABLE );
			
			foreach( $jobs as $job ){
				
				$newPost = array( 'post_author' => $userID,
								  'post_date' => $job->pubDate,
								  'post_date_gmt' => $job->pubDate,
								  'post_content' => $job->description,
								  'post_title' => $job->title,
								  'post_excerpt' => $job->subTitle,
								  'post_status' => 'publish',
								  'comment_status' => 'closed',
								  'ping_status' => 'closed',
								  'post_name' => sanitize_title_with_dashes( $job->title ),
								  'post_modified' => $job->pubDate,
								  'post_modified_gmt' => $job->pubDate,
								  'post_type' => 'rsjp_job_postings' );
				
				$newPostId = wp_insert_post( $newPost );
				
				$addMetaData = $wpdb->query( 'INSERT INTO ' . $wpdb->postmeta . ' VALUES ( NULL, "' . $newPostId . '", "rsjp_archive_posting", "' . $job->archive . '" ) ' );
				if( !$newPostId )
					$transferJobs = 'error';
			}
			
			if( $transferJobs != 'error' ){
				
				function RSJPJobsToPosts(){
					global $wpdb, $current_user;
			   		$userID = $current_user->ID;
					
					echo '<div class="updated">
							  <p><b>' . __( 'The Job Postings transfer was complete!' ) . '</b><br />
							     ' . __( 'If you are using permalinks, you may need to re-save the settings in order for you to see the pages.' ) . '<br />
								 ' . __( 'You may now delete the old table or dissmiss the notice.' ) . '</p>
						  </div>';
					add_user_meta( $userID, 'removeRSJPUpdateNotice', 'true', true );
				}
				add_action('admin_notices', 'RSJPJobsToPosts');
				
			} else {
				function RSJPJobsToPostsError(){
					echo '<div class="error">
							  <p><b>' . __( 'The Job Postings transfer was not complete!' ) . '</b><br />
								 ' . __( 'Please try the update again.' ) . '<br />
								 ' . __( 'If you continue to receive an error, then you will need to manually copy the postings into the new format.' ) . '</p>
						  </div>';
				}
				add_action('admin_notices', 'RSJPJobsToPostsError');
			}
		}
	}
	
	if( current_user_can( 'install_plugins' ) && !get_user_meta( $userID, 'removeRSJPNoticeDismiss' ) ) {
		if( $wpdb->get_var( 'SHOW TABLES LIKE "' . JOBTABLE . '"' ) ){
			function oldRSJPDBNotice(){
				global $wpdb, $current_user;
			    $userID = $current_user->ID;
				
				echo '<div class="error">
				      <p><b>' . __( 'RSJP Notice' ) . '</b>: ' . __( 'Your Job Postings table needs to be updated!' ) . '<br />
				      ' . __( 'Click on the <i>Transfer Jobs Into Posts</i> button below to transfer you old Job Postings into the new Wordpress posts. If you do not do this, this plugin will break.' ) . '<br />
				      ' . __( 'Once your have ran the update, either click <i>Delete</i> to remove the old table or <i>Dismiss</i> to remove this warning and keep the table in your database.' ) . '<br />
				      <br />';
				if( !get_user_meta( $userID, 'removeRSJPUpdateNotice' ) && ( $wpdb->query( 'SELECT * FROM ' . JOBTABLE ) ) ) {
					echo '<form method="post" enctype="multipart/form-data" style="float:left; clear:left; width:200px;">
					          <input type="hidden" name="rsjpaction" value="jobsToPost">
						      <input name="updateJobsTable" type="submit" value="' . __( 'Transfer Jobs Into Posts' ) . '" class="button-primary" />
					      </form>';
				}
				echo '<form method="post" enctype="multipart/form-data" style="float:left; width:100px;">
				   	      <input type="hidden" name="rsjpaction" value="removeJobTable">
				   	      <input name="deleteDBTBLs" type="submit" value="' . __( 'Delete' ) . '" class="button-secondary" />
				      </form>
				      <form method="post" enctype="multipart/form-data" style="float:left; clear:right; width:100px;">
				          <input type="hidden" name="removeRSJPNoticeDismiss" value="0">
				          <input name="removeRSJPNotice" type="submit" value="' . __( 'Dismiss' ) . '" class="button-secondary" />
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