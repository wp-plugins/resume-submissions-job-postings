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

// Grab Extension from file
function getExtension( $str ) {
	$i = strrpos( $str, '.' );
	if ( !$i ) { 
		return ''; 
	}
	$l = strlen( $str ) - $i;
	$ext = substr( $str, $i+1, $l );
	return $ext;
}

// Upload user attachments
function uploadAttachments( $files, $input ){
	$uploadDir = WP_CONTENT_DIR . '/uploads/rsjb/attachments/';
	$count     = 1;
	 
	foreach( $_FILES[$input]['error'] as $key => $error ){
		if ( $error == UPLOAD_ERR_OK ) {
			$tmpName  = $_FILES[$input]['tmp_name'][$key];
			$ext      = getExtension( $_FILES[$input]['name'][$key] );
			$name     = md5( date( 'Y-m-d H:i:s' ) ) . '-' . $count . '.' . $ext;
			$moveFile = move_uploaded_file( $tmpName, $uploadDir . $name );
			
			if ( $moveFile ){
				if ( $count > 1 ) {
					$sep = ',';
				} else {
					$sep = '';
				}
				$dbInsert .= $sep . $name;
				$count++;
			} else {
				echo 'Count not upload file ' . $_FILES[$input]['name'][$key] . '.<br/>';
			}
		}
	}
	
	return  $dbInsert;
}

// Delete file from the set folder in the rsjp in the wp-contents/uploads folder
function deleteFileFromUpload( $files, $folder ){
	foreach( $files as $file ){
		if ( $file ){
			if ( !( @unlink( WP_CONTENT_DIR . '/uploads/rsjb/' . $folder . '/' . $file ) ) ) {
				$message = '<p style="color:#A83434;"><b>Could not delete the attached file(s).</b></p>';
				$deleted = false;
			} else {
				$message = '<p style="color:#369B38;"><b>Attached file(s) were successfully deleted.</b></p>';
				$deleted = true;
			}
		}
	}	
	
	return array( $message, $deleted );
}

// Export Submissions List to CSV
function exportSubToCSV() {
	global $wpdb;
	
	$exportEntries = $wpdb->get_results( 'SELECT * FROM ' . SUBTABLE . ' ORDER BY lname ASC, fname ASC, pubdate DESC' );				
	$getFile       = fopen( resume_get_plugin_dir( 'path' ) . '/base-files/submission-entries.csv', 'w' );
	
	fputcsv( $getFile, array( 'First Name', 'Last Name', 'Address', 'Suite/Apt', 'City', 'State', 
							  'Zip Code', 'Primary Number', 'Secondary Number', 'Email', 'Job', 'Attachments', 'Submit Date'  ), ',' );
	foreach ( $exportEntries as $entry ) {
		$newline     = " \r\n";
		$attachments = explode( ',', $entry->attachment );
		
		foreach ( $attachments as $attachment ) {
			$attachedNames .= $attachment . $newline;
		}
		fputcsv( $getFile, array( $entry->fname, $entry->lname, $entry->address, $entry->address2, 
								  $entry->city, $entry->state, $entry->zip, $entry->pnumber, $entry->snumber, 
								  $entry->email, $entry->job, $attachedNames, date( 'm/d/Y', strtotime( $entry->pubdate ) ) ), ',' );
	}
	fclose( $getFile );
}

// Export Submission to PDF
function exportSubToPDF( $id ) {
	include( resume_get_plugin_dir( 'go' ) . '/includes/create-submission-pdf.php' );
}
?>