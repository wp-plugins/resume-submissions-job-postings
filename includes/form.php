<?php
global $current_user, $wpdb;
wp_get_current_user();


$siteName = get_option( 'blogname' );

$adminEmail     = get_option( 'resume_send_admin_email_to' );
$fromAdminEmail = get_option( 'resume_email_user_from' );
$toUserCopy     = get_option( 'resume_user_email_copy' );
$useTinyMce     = get_option( 'resume_use_tinymce' );
$useTinyMceQT   = get_option( 'resume_use_tinymce_qt' );
$rcOptions      = get_option( 'resume_captcha_options' );
$rcLang         = get_option( 'resume_use_tinymce_qt' );

$action      = $_POST['action'];
$fname       = esc_html( $_POST['fname'] );
$lname       = esc_html( $_POST['lname'] );
$address     = esc_html( $_POST['address'] );
$address2    = esc_html( $_POST['address2'] );
$city        = esc_html( $_POST['city'] );
$state       = $_POST['state'];
$zip         = esc_html( $_POST['zip'] );
$pnumber     = esc_html( $_POST['pnumber'] );
$pnumbertype = $_POST['pnumbertype'];
$snumber     = esc_html( $_POST['snumber'] );
$snumbertype = $_POST['snumbertype'];
$email       = esc_html( $_POST['email'] );
$job         = $_POST['job'];
$attachment  = array($_FILES['attachment']);
$cover       = $_POST['cover'];
$resume      = $_POST['resume'];
$fromPosting = $_POST['fromPosting'];

$resumeSubmit = '';
$formError    = false; 
$formMessage  = '';

$find    = array( '\'', '\"', '"', '<', '>' );
$replace = array( '&#39;', '&quot;', '&quot;', '&lt;', '&gt;' );
$fields  = array( 'fname' => $fname, 'lname' => $lname, 'address' => $address, 'address2' => $address2, 'city' => $city, 'state' => $state,
				  'zip' => $zip, 'pnumber' => $pnumber, 'pnumbertype' => $pnumbertype, 'snumber' => $snumber, 'snumbertype' => $snumbertype, 
				  'email' => $email, 'attachment' => $attachment, 'job' => $job, 'cover' => $cover, 'resume' => $resume );
				  
$pubDate = date('Y-m-d H:i:s');

if ( $fromPosting ){
	$job      = $fromPosting;
	$errorJob = $fromPosting;
}

// Add captcha to the form
if ( get_option( 'resume_captcha') == 'Enabled' ) {
	?>
     <script type="text/javascript">
	     var RecaptchaOptions = {
		    theme : '<?php echo $rcOptions['theme']; ?>',
			lang : '<?php echo $rcOptions['lang']; ?>'
	     };
	 </script>
    <?php
	require_once( 'recaptchalib.php' );
	$privateKey = get_option( 'resume_captcha_private_key' );
	$resp       = recaptcha_check_answer ( $privateKey,
											$_SERVER['REMOTE_ADDR'],
											$_POST['recaptcha_challenge_field'],
											$_POST['recaptcha_response_field'] );
						
	if ( !$resp->is_valid && $action == 'add' ) {
		$formMessage = '<p style="color:#CC0000;"><b>' . __( 'Error' ) . ':</b> ' . __( 'The reCAPTCHA was not entered correctly. Please try again.' ) . '</p>';
		$formError   = true;
	}
}



// Error Checking
if ( ( $action == 'add' ) && formErrorCheck( $fields ) == true ){
	$formError = true;
	$formMessage = '<p style="color:#CC0000;"><b>' . __( 'Error' ) . ':</b> ' . __( 'Make sure all fields required are filled out correctly.' ) . '</p>';
}

	
if( $action == 'add' && $formError == false ) {
	
	$attachFinal = uploadAttachments( $attachment, 'attachment' );
	
	if ( $attachFinal != 'Error' ){
		
		$insertQuery = $wpdb->query('INSERT INTO ' . SUBTABLE . ' VALUES (NULL,
																		"' . $fname . '",
																		"' . $lname . '",
																		"' . $address . '",
																		"' . $address2 . '",
																		"' . $city . '",
																		"' . $state . '",
																		"' . $zip . '",
																		"' . $pnumber . '",
																		"' . $pnumbertype . '",
																		"' . $snumber . '",
																		"' . $snumbertype . '",
																		"' . $email . '",
																		"' . $job . '",
																		"' . $attachFinal . '",
																		"' . $cover . '",
																		"' . $resume . '",
																		"' . $pubDate . '")' );
		
		if ( $insertQuery ){
			
			$resumeSubmit = "submitted";
			
			// Get the info of the inserted entry so the admin can click on the link, also builds array for replacing the shortcodes
			$upload = $wpdb->get_row( 'SELECT * FROM ' . SUBTABLE . ' WHERE email = "' . $email . '" ORDER BY pubdate DESC LIMIT 1' );
			
			// Send email to the admin
			$admin_to      = $adminEmail;
			$admin_subject = 'New Resume Submitted';
			$admin_message = '<html>
								<head>
									<title>New Resume Submitted</title>
								</head>
								<body>
									<p>' . $fname . ' ' . $lname . ' has uploaded their resume into the database.</p>
									<p>The user\'s submission is for: ' . $job . '.</p>
									<p><a href="' . admin_url() . 'admin.php?page=rsjp-submissions&id=' . $upload->id . '"><b>Click Here</b></a> to view their resume.</p>
									<br/>
								</body>
							</html>';
			
			$admin_headers  = 'MIME-Version: 1.0' . "\r\n";
			$admin_headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$admin_headers .= 'From: "' . $siteName . '"<' . $adminEmail . '>' . "\r\n";
			wp_mail( $admin_to, $admin_subject, $admin_message, $admin_headers );
		  
			// Send email to the user, if enabled
			if ( get_option( 'resume_send_email_to_user' )  == 'Enabled' ) {
				$to      = $email; 
				$subject = get_option( 'resume_user_email_subject' );
				$message = '<html>
								<head>
									<title>' . get_option( 'resume_user_email_subject' ) . '</title>
								</head>
								<body>
									' . replaceShortCode( get_option( 'resume_user_email_copy' ), $upload ) . '
								</body>
							</html>';
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: "' . $siteName . '"<' . $fromAdminEmail . '>' . "\r\n";
				wp_mail( $to, $subject, $message, $headers );
			}
			$formMessage = get_option( 'resume_thank_you_text' );
			
		}
	} else {
		$formError = true;
		$formMessage = '<p style="color:#CC0000;"><b>' . __( 'Error' ) . ':</b> ' . __( 'The uploaded file(s) extension is not allowed.' ) . '</p>';
	}
	$upload = $wpdb->get_row( 'SELECT * FROM ' . SUBTABLE . ' ORDER BY pubdate DESC LIMIT 1' );
} 


// Set the inputs to the submitted data if the form has an error, if not the unset
if ( $formError == true ){
	$errorFName    = $fname;
	$errorLName    = $lname;
	$errorAddress  = $address;
	$errorAddress2 = $address2;
	$errorCity     = $city;
	$errorState    = $state;
	$errorZip      = $zip;
	$errorPNumber  = $pnumber;
	$errorPrimType = $pnumbertype;
	$errorSNumber  = $snumber;
	$errorSecType  = $snumbertype;
	$errorEmail    = $email;
	$errorJob      = $job;
	$errorCover    = $cover;
	$errorResume   = $resume;
} else {
	$errorFName    = "";
	$errorLName    = "";
	$errorAddress  = "";
	$errorAddress2 = "";
	$errorCity     = "";
	$errorState    = "";
	$errorZip      = "";
	$errorPNumber  = "";
	$errorPrimType = "";
	$errorSNumber  = "";
	$errorSecType  = "";
	$errorEmail    = "";
	if ( !$fromPosting )
		$errorJob  = "";
	$errorCover    = "";
	$errorResume   = "";
}

// Set the radio buttons for the phone numbers 
$type = array( 'Home', 'Mobile', 'Work', 'Other' );
for( $t = 0; $t < count( $type ); $t++ ){
	if ( $errorPrimType == $type[$t] ){
		$primTypeSelected = "checked";
	} else {
		$primTypeSelected = "";
	}
	$pType .= '<input type="radio" value="' . $type[$t] . '" name="pnumbertype" valign="bottom" ' . $primTypeSelected . '> ' . $type[$t];
}

$type2 = array( 'Home', 'Mobile', 'Work', 'Other' );
for( $t2 = 0; $t2 < count( $type2 ); $t2++ ){
	if ( $errorSecType == $type2[$t2] ){
		$secTypeSelected = "checked";
	} else {
		$secTypeSelected = "";
	}
	$sType .= '<input type="radio" value="' . $type2[$t2] . '" name="snumbertype" valign="bottom" ' . $secTypeSelected . '> ' . $type2[$t2];
}



?>
<div id="resumeSubmission">
	<?php
    // Display form message
    if ( $formMessage ){
        ?>
        <div class="updated fade" id="message">
            <?php echo $formMessage; ?>
        </div>
        <?php
    }
	
	if ( $formError == true || $action != 'add' ) {
		?>
		

		<form id='formSubmission' method='POST' action="" enctype="multipart/form-data">
		<table width="100%" cellpadding="0" cellspacing="5">
			<tr>
				<td width="190px"><p style='color:#CC0000;'><b>*</b> <?php _e( 'Required' ); ?></p></td>
				<td>&nbsp;</td>
			</tr>
			<?php
			if ( grabContents( get_option( 'resume_input_fields' ), 'fname', 0 ) ) {	
				?>
				<tr>
					<td><p><?php _e( 'First Name' ); ?>: </p></td>
					<td valign="top" align="left"><input type='text' name='fname' size='30' value='<?php if ( $errorFName == '' ) echo $current_user->user_firstname; else echo $errorFName; ?>' />
						<sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'fname', 1 ) ); ?></sup></td>
				</tr>
				<?php
			}
			if ( grabContents( get_option( 'resume_input_fields' ), 'lname', 0 ) ) {	
				?>
				<tr>
					<td><p><?php _e( 'Last Name' ); ?>: </p></td>
					<td valign="top" align="left"><input type='text' name='lname' size='30' value='<?php if ( $errorLName == '' ) echo $current_user->user_lastname; else echo $errorLName; ?>' />
						<supp><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'lname', 1 ) ); ?></sup></td>
				</tr>
				<?php
			}
			if ( grabContents( get_option( 'resume_input_fields' ), 'address', 0 ) ) {	
				?>
				<tr>
					<td><p><?php _e( 'Address' ); ?>: </p></td>
					<td valign="top" align="left"><input type='text' name='address' size='30' value='<?php echo $errorAddress; ?>' />
						<sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'address', 1 ) ); ?></sup></td>
				</tr>
				<?php
			}
			if ( grabContents( get_option( 'resume_input_fields' ), 'address2', 0 ) ) {	
				?>
				<tr>
					<td><p><?php _e( 'Address2' ); ?>: </p></td>
					<td valign="top" align="left"><input type='text' name='address2' size='30' value='<?php echo $errorAddress2; ?>' />
						<sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'address2', 1 ) ); ?></sup></td>
				</tr>
				<?php
			}
			if ( grabContents( get_option( 'resume_input_fields' ), 'city', 0 ) ) {	
				?>
				<tr>
					<td><p><?php _e( 'City' ); ?>: </p></td>
					<td valign="top" align="left"><input type='text' name='city' size='30' value='<?php echo $errorCity; ?>' />
						<sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'city', 1 ) ); ?></sup></td>
				</tr>
				<?php
			}
			if ( grabContents( get_option( 'resume_input_fields' ), 'state', 0 ) ) {
				$theStateList =  get_option( 'resume_state_list' );
				?>
				<tr>
					<td><p><?php _e( 'State' ); ?>: </p></td>
					<td valign="top" align="left"><select name="state" id="state">
							<?php echo arrayToSelect( $theStateList['list'], $errorState, '', true ); ?>
						</select>
						<sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'state', 1 ) ); ?></sup></td>
				</tr>
				<?php
			}
			if ( grabContents( get_option( 'resume_input_fields' ), 'zip', 0 ) ) {	
				?>
				<tr>
					<td><p><?php _e( 'Zip Code' ); ?>: </p></td>
					<td valign="top" align="left"><input type='text' name='zip' size='20' value='<?php echo $errorZip; ?>' /> <sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'zip', 1 ) ); ?></sup></td>
				</tr>
				<?php
			}
			if ( grabContents( get_option( 'resume_input_fields' ), 'pnumber', 0 ) ) {	
				?>
				<tr>
					<td valign="top"><p><?php _e( 'Primary Contact Number' ); ?>: </p></td>
					<td valign="top" align="left"><input type='text' name='pnumber' size='25' value='<?php echo $errorPNumber; ?>' />
						<sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'pnumber', 1 ) ); ?></sup><br />
						<?php echo $pType; ?></td>
				</tr>
				<?php
			}
			if ( grabContents( get_option( 'resume_input_fields' ), 'snumber', 0 ) ) {	
				?>
				<tr>
					<td valign="top"><p><?php _e( 'Secondary Contact Number' ); ?>: </p></td>
					<td valign="top" align="left"><input type='text' name='snumber' size='25' value='<?php echo $errorSNumber; ?>' />
						<sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'snumber', 1 ) ); ?></sup><br />
						<?php echo $sType; ?></td>
				</tr>
				<?php
			}
			if ( grabContents( get_option( 'resume_input_fields' ), 'email', 0 ) ) {	
				?>
				<tr>
					<td><p><?php _e( 'E-Mail Address' ); ?>: </p></td>
					<td valign="top" align="left"><input type='text' name='email' size='30' value='<?php if ( $errorEmail == '' ) echo $current_user->user_email; else echo $errorEmail; ?>' />
						<sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'email', 1 ) ); ?></sup></td>
				</tr>
				<?php 
			}
			
			$currentJobs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . JOBTABLE . ' WHERE archive != "%d" ORDER BY title DESC', '1' ) );
			?>
		</table>
			
		<table width="100%" cellpadding="0" cellspacing="0">
			<tr>
				<td width="190px"><p><?php _e( 'Regarding Job' ); ?>: </p></td>
				<td valign="top" align="left"><select name="job">                            	
						<option value="General Purpose" <?php if ( $errorJob == 'General Purpose' ){ echo 'selected="selected"'; } ?>><?php _e( 'General Purpose' ); ?></option>
                        <?php
						$getJobsArg = array( 'numberposts'  => -1,
											 'post_type'  => 'rsjp_job_postings',
											 'orderby'    => 'post_date',
											 'order'      => 'DESC',
											 'meta_query' => array(
																 array(
																	 'key' => 'rsjp_archive_posting',
																	 'value' => 1,
																	 'compare' => 'NOT LIKE'
																 ) ) ); 
						$getJobs = get_posts( $getJobsArg );

						foreach( $getJobs as $getJob ){
							?>
						    <option value="<?php echo $getJob->post_name; ?>" <?php if( $getJob->post_name == $errorJob ) echo 'selected="selected"'; ?>><?php echo $getJob->post_title; ?></option>
                             <?php 
						}
						wp_reset_postdata();
						?>
					</select>
					<sup style='color:#CC0000; font-weight:bold;'>*</sup></td>
			 </tr>
		 </table>
		 
		 <table width="100%" cellpadding="0" cellspacing="0">    
			 <?php
			 if ( grabContents( get_option( 'resume_input_fields' ), 'attachment', 0 ) ) {	
				$attachSettings = get_option( 'resume_attachments' );
				?>
				<tr>
					<td valign="top" width="190px"><p><?php _e( 'Attachment(s):' ); ?> </p></td>
					<td valign="top" align="left"><input type="file" name="attachment[]" id="attachment" class="multi" accept="<?php echo $attachSettings['allowed']; ?>" maxlength="<?php echo $attachSettings['num']; ?>" />
						<sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'attachment', 1 ) ); ?></sup><br />
                        <i><small><?php _e( 'Allowed extensions:' );?> <?php echo str_replace( '|', ',', $attachSettings['allowed'] ); ?></small></i></td>
				</tr>
				<?php 
			}
			?>
		</table>
		
		<br />
		<table width="100%" cellpadding="0" cellspacing="0">
			<?php
			if ( grabContents( get_option( 'resume_input_fields' ), 'cover', 0 ) ) {	
				?>
				<tr>
					<td><p><b><?php _e( 'Cover Letter' );?>:</b> <?php _e( '(Please submit with good formatting)' ); ?> <sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'cover', 1 ) ); ?></sup></p></td>
				</tr> 
				<tr>
					<td>
						<?php 
						if ( function_exists( wp_editor ) ) {
							wp_editor( $errorCover, 'cover', setTinySetting( 'cover', '35', false, settype( $useTinyMce, boolean ), settype( $useTinyMceQT, boolean ) ) ); 
						} else {
							?>
							<textarea name="cover" rows="20" cols="40"><?php echo $errorCover; ?></textarea>
							<?php 
						}
						?>
					</td>
				</tr>	
				<?php
			}
			if ( grabContents( get_option( 'resume_input_fields' ), 'resume', 0 ) ) {	
				?>
				<tr>
					<td><p><b><?php _e( 'Resume' ); ?>:</b> <?php _e( '(Please submit with good formatting)' ); ?> <sup><?php echo displayRequired( grabContents( get_option( 'resume_input_fields' ), 'resume', 1 ) ); ?></sup></p></td>
				</tr>
				<tr>
					<td>
						<?php 
						if ( function_exists( wp_editor ) ) {
							wp_editor( $errorResume, 'resume', setTinySetting( 'resume', '35', false, settype( $useTinyMce, boolean ), settype( $useTinyMceQT, boolean ) ) ); 
						} else {
							?>
							<textarea name="resume" rows="20" cols="40"><?php echo $errorResume; ?></textarea>
							<?php
						}
						?>
					</td>
				</tr>	
				<?php
			}
			
			// Display Captcha if enabled
			if ( get_option( 'resume_captcha' ) == 'Enabled' ) {
				?>
				<tr>
					<td><p><?php require_once( 'recaptchalib.php' ); 
							$publicKey = get_option( 'resume_captcha_public_key' );
							echo recaptcha_get_html( $publicKey ); ?></p></td>
				</tr>
				<?php
			}
			?>
			<input type='hidden' name='action' value='add' />
			<tr>
				<td><p><input type='submit' value='<?php _e( 'Submit Resume' );?>' name='submit' /></p></td>
			</tr>
		</table>
		</form>
        <?php
	}
	?>
</div>