<?php
global $current_user, $wpdb;
wp_get_current_user();

$siteName = get_option( 'blogname' );

$adminEmail     = get_option( 'resume_send_admin_email_to' );
$fromAdminEmail = get_option( 'resume_email_user_from' );
$toUserCopy     = get_option( 'resume_user_email_copy' );

$action      = $_POST['action'];
$fname       = $_POST['fname'];
$lname       = $_POST['lname'];
$address     = $_POST['address'];
$address2    = $_POST['address2'];
$city        = $_POST['city'];
$state       = $_POST['state'];
$zip         = $_POST['zip'];
$pnumber     = $_POST['pnumber'];
$pnumbertype = $_POST['pnumbertype'];
$snumber     = $_POST['snumber'];
$snumbertype = $_POST['snumbertype'];
$email       = $_POST['email'];
$job         = $_POST['job'];
$cover       = $_POST['cover'];
$resume      = $_POST['resume'];
$fromPosting = $_POST['fromPosting'];

$resumeSubmit = '';
$formError    = false; 
$formMessage  = '';

$find    = array( '\'', '\"', '"', '<', '>' );
$replace = array( '&#39;', '&quot;', '&quot;', '&lt;', '&gt;' );

$pubDate = date('Y-m-d H:i:s');


if ( $fromPosting ){
	$job      = $fromPosting;
	$errorJob = $fromPosting;
}

// Add captcha to the form
if ( get_option( 'resume_captcha') == 'Enabled' ) {
	require_once( 'recaptchalib.php' );
	$privateKey = get_option( 'resume_captcha_private_key' );
	$resp       = recaptcha_check_answer ( $privateKey,
											$_SERVER['REMOTE_ADDR'],
											$_POST['recaptcha_challenge_field'],
											$_POST['recaptcha_response_field'] );
						
	if ( !$resp->is_valid && $action == 'add' ) {
		$formMessage = '<p style="color:#CC0000;"><b>Error:</b> The reCAPTCHA was not entered correctly. Please try it again.</p>';
		$formError   = true;
	}
}




// Error Checking
if ( ( $action == 'add' ) && ( !$fname || !$lname || !$address || !$city ||	!$state || !$zip || !$pnumber || !$pnumbertype ||
	( !$email || ( strpos( $email, '.' ) == false || strpos( $email, '@' ) == false)) || !$resume ) ){
	$formError = true;
	$formMessage = '<p style="color:#CC0000;"><b>Error:</b> Make sure all fields required are filled out correctly.</p>';
}

	
if( $action == 'add' && $formError == false ) {

	$fname    = htmlentities( $fname );
	$lname    = htmlentities( $lname );
	$address  = htmlentities( $address );
	$address2 = htmlentities( $address2 );
	$city     = htmlentities( $city );
	$state    = htmlentities( $state );
	$zip      = htmlentities( $zip );
	$email    = htmlentities( $email );
	$cover    = htmlentities( $cover );
	$resume   = htmlentities( $resume );
	
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
								<p>Look at their resume <a href="' . admin_url() . 'admin.php?page=resume-submission/resume-submission.php&id=' . $upload->id . '"><b>here</b></a>.</p>
								<br/>
							</body>
						</html>';
		
		$admin_headers  = 'MIME-Version: 1.0' . "\r\n";
		$admin_headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$admin_headers .= 'From: "' . $siteName . '"<' . $adminEmail . '>' . "\r\n";
		mail( $admin_to, $admin_subject, $admin_message, $admin_headers );
	  
	  	// Send email to the user, if enabled
		if ( get_option( 'resume_send_email_to_user' ) ) {
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
			mail( $to, $subject, $message, $headers );
		}
		$formMessage = '<p style="color:#008f07;"><b>Thank you for your submission.</b></p>
						<p style="color:#008f07;">Your resumé is now stored in our database for future reference.</p>
						<p style="color:#008f07;">If you have any questions, please feel free to contact us.</p>';
		
	}
}
$upload = $wpdb->get_row( 'SELECT * FROM ' . SUBTABLE . ' ORDER BY pubdate DESC LIMIT 1' );

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
    ?>
    
    <form id='formSubmission' method='POST' action="" enctype="multipart/form-data">
    <table width="100%" cellpadding="0" cellspacing="5">
        <tr>
            <td width="190px"></td>
            <td width="145px"></td>
            <td><p style='color:#CC0000;'><b>*</b> Required</p></td>
        </tr>
        <tr>
            <td><p>First Name: </p></td>
            <td><input type='text' name='fname' size='20' value='<?php if ( $errorFName == '' ) echo $current_user->user_firstname; else echo $errorFName; ?>' /></td>
            <td valign="top"><p style='color:#CC0000; font-weight:bold;'>*</p></td>
        </tr>
        <tr>
            <td><p>Last Name: </p></td>
            <td><input type='text' name='lname' size='20' value='<?php if ( $errorLName == '' ) echo $current_user->user_lastname; else echo $errorLName; ?>' /></td>
            <td valign="top"><p style='color:#CC0000; font-weight:bold;'>*</p></td>
        </tr>
        <tr>
            <td><p>Address: </p></td>
            <td><input type='text' name='address' size='20' value='<?php echo $errorAddress; ?>' /></td>
            <td valign="top"><p style='color:#CC0000; font-weight:bold;'>*</p></td>
        </tr>
        <tr>
            <td><p>Address2: </p></td>
            <td><input type='text' name='address2' size='20' value='<?php echo $errorAddress2; ?>' /></td>
            <td valign="top"></td>
        </tr>
        <tr>
            <td><p>City: </p></td>
            <td><input type='text' name='city' size='20' value='<?php echo $errorCity; ?>' /></td>
            <td valign="top"><p style='color:#CC0000; font-weight:bold;'>*</p></td>
        </tr>
        <tr>
            <td><p>State: </p></td>
            <td><select name="state" id="state">
                	<?php echo arrayToSelect( stateList(), $errorState ); ?>
                </select></td>
            <td valign="top"><p style='color:#CC0000; font-weight:bold;'>*</p></td>
        </tr>
        <tr>
            <td><p>Zip Code: </p></td>
            <td><input type='text' name='zip' size='20' value='<?php echo $errorZip; ?>' /></td>
            <td valign="top"><p style='color:#CC0000; font-weight:bold;'>*</p></td>
        </tr>
        <tr>
            <td><p>Primary Contact Number: </p></td>
            <td><input type='text' name='pnumber' size='15' value='<?php echo $errorPNumber; ?>' /></td>
            <td valign="top"><p><span style='color:#CC0000; font-weight:bold;'>*</span> <?php echo $pType; ?></p></td>
        </tr>
        <tr>
            <td><p>Secondary Contact Number: </p></td>
            <td><input type='text' name='snumber' size='15' value='<?php echo $errorSNumber; ?>' /></td>
            <td valign="top"><p>&nbsp;&nbsp;<?php echo $sType; ?></p></td>
        </tr>
        <tr>
            <td><p>E-Mail Address: </p></td>
            <td><input type='text' name='email' size='20' value='<?php if ( $errorEmail == '' ) echo $current_user->user_email; else echo $errorEmail; ?>' /></td>
            <td valign="top"><p style='color:#CC0000; font-weight:bold;'>*</p></td>
        </tr>
        <?php 
		$currentJobs = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . JOBTABLE . ' WHERE archive != "1" ORDER BY title DESC' ) );
		?>
        <tr>
            <td><p>Regarding Job: </p></td>
            <td><select name="job">                            	
					<?php echo arrayToSelect( $currentJobs, $errorJob ); ?>
                    <option value="General Purpose" <?php if ( $errorJob == 'General Purpose' ){ echo 'selected="selected"'; } ?>>General Purpose</option>    
                </select></td>
             <td valign="top"><p style='color:#CC0000; font-weight:bold;'>*</p></td>
         </tr>
                     
    </table>
    <br />
    <table width="100%" cellpadding="0" cellspacing="0">
    	<tr>
            <td><p><b>Cover Letter: </b>(Please submit with good formatting)</p></td>
            <td></td>
        </tr>
        <tr>
            <td><p><textarea name='cover' rows='15' cols='70' id="intro"><?php echo $errorCover; ?></textarea></p></td>
            <td valign="top" width="5px"><p style='color:#CC0000; font-weight:bold;'>*</p></td>
        </tr>	
        <tr>
            <td><p><b>Resumé: </b>(Please submit with good formatting)</p></td>
            <td></td>
        </tr>
        <tr>
            <td><p><textarea name='resume' rows='15' cols='70' id="intro"><?php echo $errorResume; ?></textarea></p></td>
            <td valign="top" width="5px"><p style='color:#CC0000; font-weight:bold;'>*</p></td>
        </tr>	
        <?php
		// Display Captcha if enabled
        if ( get_option( 'resume_captcha' ) == 'Enabled' ) {
            ?>
            <tr>
                <td><p><?php require_once( 'recaptchalib.php' ); 
                        $publicKey = get_option( 'resume_captcha_public_key' );
                        echo recaptcha_get_html( $publicKey ); ?></p></td>
                <td></td>
            </tr>
            <?php
        }
        ?>
        <input type='hidden' name='action' value='add' />
        <tr>
            <td><p><input type='submit' value='Submit Resume' name='submit' /></p></td>
            <td></td>
        </tr>
    </table>
    </form>
</div>