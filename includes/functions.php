<?php
// Functions

// Set states into an array
function stateList(){
	$stateList = array( 'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 
					    'District Of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 
					    'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 
					    'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 
					    'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 
					    'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 
					    'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming' );

	return $stateList;
}

// Put a list into selection  
function arrayToSelect( $options, $selected = '', $optgroup = NULL ){
	echo '<option value="">- - Select - -</option>';
	foreach ( $options as $value ) {
		if ( is_object( $value ) ){
			$optValue = $value->title;
		} else {
			$optValue = $value;
		}
		if ( $selected == $optValue ){
			$set = 'selected="selected"';
		} else {
			$set = '';
		}
		$returnStatement .= '<option value="' . $optValue . '" ' . $set . '>' . $optValue . '</option>';
	}
	return $returnStatement;
}

// Checks the input and returns the correct syntax
function checkIt( $field, $set, $type ) {
	switch( $type ){
		case 'radio'  :
		case 'check'  :
					  $language = 'checked="checked"';
					  break;
		case 'select' :
					  $language = 'selected="selected"';
					  break;
	}
	if ( $field == $set ){
		$selected = $language;
	} else {
		$selected = '';
	}
	return $selected;
}


// Replace the shortcodes with the variables
function replaceShortCode( $text, $array){
	$shortCodes = array( '%fname%', '%lname%', '%address%', '%address2%', '%city%', '%state%', '%zip%', '%pnumber%', 
						 '%pnumbertype%', '%snumber%', '%snumberType%', '%email%', '%job%', '%cover%', '%resume%', '%siteName%' );
	$variables  = array( $array->fname, $array->lname, $array->address, $array->address2, $array->city, $array->state, $array->zip, $array->pnumber, 
						 $array->pnumbertype, $array->snumber, $array->snumberType, $array->email, $array->job, $array->cover, $array->resume, get_option( 'blogname' ) );
	
	$newText = str_replace( $shortCodes, $variables, $text );
	
	return $newText;
}

// Set the TinyMce settings easily
function setTinySetting( $name, $rows, $media, $tiny, $tags ) {
	$settings = array(
					'wpautop' => true,
					'media_buttons' => $media,
					'textarea_rows' => $rows,
					'textarea_name' => $name,
					'teeny' => true,
					'tinymce' => $tiny,
					'quicktags' => $tags
					);

	return $settings;
}


// Grab the contents of the specific field in the array
function grabContents( $array, $field, $sub ){
	
	$value = $array[$field][$sub];
	
	return $value;	
}

// Display the form * if the field is required
function displayRequired( $value ){
	
	if ( $value == 1 ) {
		$display = '<span style="color:#CC0000; font-weight:bold;">*</span>';
	} else {
		$display = '';
	}
	
	return $display;
}

// Checks to make sure all the required fields are filled out
function formErrorCheck( $fields ) {
	
	$array = get_option( 'resume_input_fields' );
	
	foreach ( $array as $item => $key) {
		foreach ( $fields as $field => $sub ) {
			if ( $field == $item && $key[1] == 1 ){
				if ( !$sub ) {
					return $error = true;
				} else {
					$error = false;
				}
			} elseif ( ( ( $item == 'pnumber' && $key[1] == 1 ) && $field == 'pnumbertype' ) || ( ( $item == 'snumber' && $key[1] == 1 ) && $field == 'snumbertype' ) ){
				if ( !$sub ) {
					return $error = true;
				} 
			} elseif ( $field == 'job' ){
				if ( !$sub ) {
					return $error = true;
				}
			} 
		}
		
	}
	
	return $error;	
}

?>