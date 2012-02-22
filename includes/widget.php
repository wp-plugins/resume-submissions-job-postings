<?php

extract( $args );

echo $before_widget;

if ( get_option( 'resume_widget_title' ) != '' ) {

	echo '\n' . $before_title; 
	echo get_option( 'resume_widget_title' ); 
	echo $after_title;

}

resume_write();

echo $after_widget;

?>