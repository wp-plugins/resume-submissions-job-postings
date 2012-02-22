<?php
//***** Installer *****
global $wp_version, $wpdb;
if ( version_compare( $wp_version, '3.0', '<' ) ) {
	require_once( ABSPATH . 'wp-admin/upgrade.php' );
} else {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
}
//***Installer variables***
$resume_db_version = "1.8.7";
//***Installer****
if( $wpdb->get_var( 'SHOW TABLES LIKE "' . SUBTABLE . '"' ) != SUBTABLE ) {
	$sql = 'CREATE TABLE ' . SUBTABLE . ' (
		  id int(12) NOT NULL auto_increment,
		  fname varchar(150) NOT NULL,
		  lname varchar(250) NOT NULL,
		  address varchar(500) NOT NULL,
		  address2 varchar(350) NOT NULL,
		  city varchar(150) NOT NULL,
		  state varchar(200) NOT NULL,
		  zip varchar(100) NOT NULL,
		  pnumber varchar(50) NOT NULL,
		  pnumbertype varchar(15) NOT NULL,
		  snumber varchar(50) NOT NULL,
		  snumbertype varchar(15) NOT NULL,
		  email varchar(300) NOT NULL,
		  job varchar(300) NOT NULL,
		  cover text NOT NULL,
		  resume text NOT NULL,
		  pubdate datetime NOT NULL,
		  PRIMARY KEY  (id)
		);';
	dbDelta( $sql );
}	

if( $wpdb->get_var( 'SHOW TABLES LIKE "' . JOBTABLE . '"' ) != JOBTABLE ) {
	$sql = 'CREATE TABLE ' . JOBTABLE . ' (
		  id int(12) NOT NULL auto_increment,
		  title varchar(1000) NOT NULL,
		  subTitle varchar(1000) NOT NULL,
		  description text NOT NULL,
		  archive int(1) NOT NULL DEFAULT 0,
		  pubDate datetime NOT NULL,
		  PRIMARY KEY  (id)
		);';
	dbDelta( $sql );
}

add_option( 'resume_widget_title', 'Resumé Submission' );

add_option( 'resume_db_version', $resume_db_version );

// For Settings
add_option( 'resume_captcha', 'Disabled' );
add_option( 'resume_captcha_private_key', '' );
add_option( 'resume_captcha_public_key', '' );
add_option( 'resume_form_page', '' );
add_option( 'resume_jobs_page', '' );
add_option( 'resume_use_tinymce', 'Enabled' );
add_option( 'resume_send_admin_email_to', get_option( 'admin_email' ) );
add_option( 'resume_email_user_from', get_option( 'admin_email' ) );
add_option( 'resume_send_email_to_user', 'Enabled' );
add_option( 'resume_user_email_subject', 'Thank You For Submitting Resume' );
add_option( 'resume_user_email_copy', '<p>Dear %fname%,</p>
<p>We at %siteName% appreciate your interests with us.</p>
<p>If you have met our qualifications, we will contact for further infomation.</p>
<br/>' );




//***Upgrader***
$installed_ver = get_option( 'resume_db_version' );
if( $installed_ver != $resume_db_version ) {

	add_option( 'resume_widget_title', 'Resumé Submission' );
	
	update_option( 'resume_db_version', $resume_db_version );
	
	if( $wpdb->get_var( 'SHOW TABLES LIKE "' . JOBTABLE . '"' ) != JOBTABLE ) {
		$sql = 'CREATE TABLE ' . JOBTABLE . ' (
			  id int(12) NOT NULL auto_increment,
			  title varchar(1000) NOT NULL,
			  subTitle varchar(1000) NOT NULL,
			  description text NOT NULL,
			  archive int(1) NOT NULL 0,
			  pubDate datetime NOT NULL,
			  PRIMARY KEY  (id)
			);';
		dbDelta( $sql );
	}
}
//***** End Installer *****
?>