<?php
/*
Plugin Name: Resumé Submissions & Job Postings
Plugin URI: http://www.geerservices.com/wordpress-plugins/resume-jobs/
Description: Allows the admin to create and show job postings. Users can submit their resume in response to a posting or for general purposes. 
Version: 1.9.7
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
include( 'includes/widget.php' );


// BOF Resume Submissions Menu 
add_action( 'admin_menu', 'resume_submission_menu' );

function resume_submission_menu() {		
	add_menu_page( __( 'Resumés &amp; Jobs', 'resume_submission' ), __( 'Resumés &amp; Jobs', 'resume_submission' ), MANAGEMENT_PERMISSION, __FILE__, 'resume_view_all', resume_get_plugin_dir( 'go' ) . '/images/icons/menu-icon.png', 25 );		
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
		

// Set i18n
add_action( 'init', 'resume_load_textdomain' );

function resume_load_textdomain() {
    load_plugin_textdomain( 'resume-submissions-job-postings', false, 'resume-submissions-job-postings/languages' );
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