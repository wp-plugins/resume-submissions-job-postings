<?php
global $wpdb, $post;

// Resume Settings
$update  = $_POST['update'];
$message = '';
$wpPages = get_pages();

// Set the list array for the custom state option
if ( $_POST['useStateList'] == 'US' ){
	$saveList = stateList();
} else {
	$saveList = explode ( '|', $_POST['customStateList'] );
}

$captchaUse        = $_POST['captchaUse'];
$captchaPrivateKey = $_POST['captchaPrivateKey'];
$captchaPublicKey  = $_POST['captchaPublicKey'];
$formPage          = $_POST['formPage'];
$jobsPage          = $_POST['jobsPage'];
$useTinymce        = $_POST['useTinymce'];
$attachments       = array( 'num' => $_POST['numAttachments'], 'allowed' => $_POST['allowedAttachments'], 'delete' => $_POST['deleteAttachments'] );
$customPDFBase     = $_POST['customPDFBase'];
$stateList         = array( 'use' => $_POST['useStateList'], 'list' => $saveList );
$sendAdminEmailTo  = $_POST['sendAdminEmailTo'];
$emailUserFrom     = $_POST['emailUserFrom'];
$sendEmailToUser   = $_POST['sendEmailToUser'];
$userEmailSubject  = $_POST['userEmailSubject'];
$userEmailCopy     = $_POST['userEmailCopy'];

$editorSettings    = array(
					'wpautop' => true,
					'media_buttons' => false,
					'textarea_rows' => '15',
					'textarea_name' => 'userEmailCopy',
					'teeny' => false,
					'tinymce' => true,
					'quicktags' => true
					);

if ( $update ) {
	update_option( 'resume_captcha', $captchaUse );
	update_option( 'resume_captcha_private_key', $captchaPrivateKey );
	update_option( 'resume_captcha_public_key', $captchaPublicKey );
	update_option( 'resume_form_page', $formPage );
	update_option( 'resume_jobs_page', $jobsPage );
	update_option( 'resume_use_tinymce', $useTinymce );
	update_option( 'resume_attachments', $attachments );
	update_option( 'resume_pdf_base_file', $customPDFBase );
	update_option( 'resume_state_list', $stateList );
	update_option( 'resume_send_admin_email_to', $sendAdminEmailTo );
	update_option( 'resume_email_user_from', $emailUserFrom );
	update_option( 'resume_send_email_to_user', $sendEmailToUser );
	update_option( 'resume_user_email_subject', $userEmailSubject );
	update_option( 'resume_user_email_copy', $userEmailCopy );
	
	$message = '<div class="updated fade" id="message"><p>Settings have been updated.</p></div>';
}

// If the wp_editor is not there, do not show the TinyMCE
if ( !function_exists( wp_editor ) ) {
	$tinymceOff = 'disabled="disabled"';
	$tinymceOffText = '<span style="font-size:10px; color:#CCC; font-style:italic; padding-left:10px;">Please upgrade to at least version 3.3 to use this feature.</span>';
} else {
	$tinymceOff = '';
	$tinymceOffText = '';
}

// Set the options to a varaible
$attachOptions    = get_option( 'resume_attachments' );
$stateListOptions = get_option( 'resume_state_list' );

// Get all of the states for the custom list
$stateCount = 0;
foreach ( $stateListOptions['list'] as $state ){
	$stateSep = ' | ';
	if ( $stateCount < 1 ) 
		$stateSep = '';
		
	$displayStateList .= $stateSep . $state;
	$stateCount++;
}
?>
<script language="javascript" type="text/javascript" src="<?php echo resume_get_plugin_dir( 'go' ); ?>/includes/jQuery/settings.js"></script>

<div class="wrap alternate">
	
    <div id="icon-rsjp-settings" class="icon32"></div>
    <h2><?php _e( 'Resumé Settings' ); ?></h2>
    <?php echo $message; ?>
    <br class="a_break" style="clear: both;"/>
	
    <div id="rsjpLeftCol">
    	<div id="rsjpSettingsMenu">
        	<input type="image" value="Core" id="coreTab" src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/settings-menu/core.png" onclick='jsOpenSettings(this)' />
            <input type="image" value="reCaptcha" id="recaptchaTab" src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/settings-menu/recaptcha.png" onclick='jsOpenSettings(this)' />
            <input type="image" value="Attachments" id="attachmentsTab" src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/settings-menu/attachments.png" onclick='jsOpenSettings(this)' />
            <input type="image" value="Emailing" id="emailingTab" src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/settings-menu/emailing.png" onclick='jsOpenSettings(this)' />
            
        </div>
        <br />
        <form name='form' id='form' class='form' method='post' enctype="multipart/form-data">
        <table class="widefat" id="coreSettings">
        	<thead>
                <tr>
                    <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/core-options-icon-20.png" alt="Core Options" /><?php _e( 'Core Options' ); ?></th>
                    <th scope="col">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><p><b><?php _e( 'Resume Form Page:' ); ?> </b></p></td>
                    <td align="left"><select name="formPage">
                                         <option value=""> -- Select -- </option>
                                         <?php 
                                         foreach ( $wpPages as $page ) {?>
                                             <option value="<?php echo get_page_link( $page->ID ); ?>" <?php echo checkIt( get_option( 'resume_form_page' ), get_page_link( $page->ID ), 'select' ); ?>><?php echo $page->post_title; ?></option>
                                             <?php
                                             }
                                         ?>
                                     </select></td>
                </tr>
                <tr>
                    <td width="150px"><p><b><?php _e( 'Display Jobs Page:' ); ?> </b></p></td>
                    <td align="left"><select name="jobsPage">
                                         <option value=""> -- Select -- </option>
                                         <?php 
                                         foreach ( $wpPages as $page ) {?>
                                             <option value="<?php echo get_page_link( $page->ID ); ?>" <?php echo checkIt( get_option( 'resume_jobs_page' ), get_page_link( $page->ID ), 'select' ); ?>><?php echo $page->post_title; ?></option>
                                             <?php
                                             }
                                         ?>
                                     </select></td>
                </tr>
                <tr>
                    <td><p><b><?php _e( 'Use TinyMCE:' ); ?> </b></p></td>
                    <td align="left"><input type="radio" name="useTinymce" value="Enabled" <?php echo checkIt( get_option( 'resume_use_tinymce' ), 'Enabled', 'radio' ); ?> />Enabled 
                                     <input type="radio" name="useTinymce" value="Disabled" <?php echo checkIt( get_option( 'resume_use_tinymce' ), 'Disabled', 'radio' ); ?> />Disabled</td>
                </tr>
                <!--<tr>
                    <td valign="top"><p><b><?php _e( 'PDF Base File:' ); ?> </b></p></td>
                    <td align="left"><input type='text' name='customPDFBase' size='100' value="<?php echo get_option( 'resume_pdf_base_file' ); ?>" /><br />
                                     <i style="font-size:10px;"><?php _e( 'Default:' ); ?> <?php echo resume_get_plugin_dir( 'path' ) . '/base-files/submission-entry.pdf'; ?></i></td>
                </tr>-->
                <tr>
                    <td valign="top"><p><b><?php _e( 'State List:' ); ?> </b></p></td>
                    <td align="left"><select name="useStateList" id="useStateList" onchange='jsOpenCustomList(this)'>
                                         <option value="US" <?php if ( $stateListOptions['use'] == 'US' ) echo 'selected="selected"';?>>US</option>
                                         <option value="Custom" <?php if ( $stateListOptions['use'] == 'Custom' ) echo 'selected="selected"';?>>Custom</option>
                                     </select></td>
                </tr>
                <tr id="customStateList">
                    <td valign="top"><p><b><?php _e( 'Custom State List:' ); ?> </b></p></td>
                    <td align="left"><input type='text' name='customStateList' size='100' <?php if ( $stateListOptions['use'] != 'US' ) echo 'value="' . $displayStateList . '"'; ?> /><br />
                                     <i style="font-size:10px;"><?php _e( 'Seperate with | Example: Florida | New York' ); ?></i></td>
                </tr>
            </tbody>
        </table>
        <table class="widefat" id="recaptchaSettings">
        	<thead>
                <tr>
                    <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/recaptcha-icon-20.png" alt="reCaptcha" /><?php _e( 'reCaptcha' ); ?></th>
                    <th scope="col">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="150px"><p><b><?php _e( 'Enable Captcha:' ); ?> </b></p></td>
                    <td align="left"><input type="radio" name="captchaUse" value="Enabled" <?php echo checkIt( get_option( 'resume_captcha' ), 'Enabled', 'radio' ); ?> />Enabled 
                                     <input type="radio" name="captchaUse" value="Disabled" <?php echo checkIt( get_option( 'resume_captcha' ), 'Disabled', 'radio' ); ?> />Disabled</td>
                </tr>
                <tr>
                    <td><p><b><?php _e( 'Captcha - Private Key:' ); ?> </b></p></td>
                    <td align="left"><input type='text' name='captchaPrivateKey' size='60' value='<?php echo get_option( 'resume_captcha_private_key' ); ?>' /></td>
                </tr>
                <tr>
                    <td><p><b><?php _e( 'Captcha - Public Key:' ); ?> </b></p></td>
                    <td align="left"><input type='text' name='captchaPublicKey' size='60' value='<?php echo get_option( 'resume_captcha_public_key' ); ?>' /></td>
                </tr>
            </tbody>
        </table>
        <table class="widefat" id="attachmentSettings">
        	<thead>
                <tr>
                    <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/attachments-icon-20.png" alt="Attachments" /><?php _e( 'Attachments' ); ?></th>
                    <th scope="col">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="150px"><p><b><?php _e( 'Number of Attachments:' ); ?> </b></p></td>
                    <td align="left"><input type='text' name='numAttachments' size='40' value='<?php echo $attachOptions['num']; ?>' /></td>
                </tr>
                <tr>
                    <td valign="top"><p><b><?php _e( 'Allowed Attachments:' ); ?> </b></p></td>
                    <td align="left"><input type='text' name='allowedAttachments' size='40' value='<?php echo $attachOptions['allowed']; ?>' /><br />
                                     <i style="font-size:10px;"><?php _e( 'Seperate with | Example: pdf|docx' ); ?></i></td>
                </tr>
                <tr>
                    <td valign="top"><p><b><?php _e( 'Delete Attachments:' ); ?> </b></p></td>
                    <td align="left"><input type="radio" name="deleteAttachments" value="Enabled" <?php echo checkIt( $attachOptions['delete'], 'Enabled', 'radio' ); ?> />Enabled 
        
                                     <input type="radio" name="deleteAttachments" value="Disabled" <?php echo checkIt( $attachOptions['delete'], 'Disabled', 'radio' ); ?> />Disabled
                                     <br /><i style="font-size:10px;"><?php _e( 'Will delete the submission\'s attachment(s) when the submission is deleted' ); ?></i></td>
                </tr>
            </tbody>
        </table>
        <table class="widefat" id="emailingSettings">
        	<thead>
                <tr>
                    <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/emailing-icon-20.png" alt="Emailing" /><?php _e( 'Emailing' ); ?></th>
                    <th scope="col">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td width="150px"><p><b><?php _e( 'Send Admin Email To:' ); ?> </b></p></td>
                    <td align="left"><input type='text' name='sendAdminEmailTo' size='40' value='<?php echo get_option( 'resume_send_admin_email_to' ); ?>' /></td>
                </tr>
                <tr>
                    <td><p><b><?php _e( 'Send User Email:' ); ?> </b></p></td>
                    <td align="left"><input type="radio" name="sendEmailToUser" value="Enabled" <?php echo $tinymceOff; ?> <?php echo checkIt( get_option( 'resume_send_email_to_user' ), 'Enabled', 'radio' ); ?> />Enabled 
                                     <input type="radio" name="sendEmailToUser" value="Disabled" <?php echo $tinymceOff; ?> <?php echo checkIt( get_option( 'resume_send_email_to_user' ), 'Disabled', 'radio' ); ?> />Disabled <?php _e( $tinymceOffText ); ?></td>
                </tr>    
                <tr>
                    <td><p><b><?php _e( 'Email User From:' ); ?> </b></p></td>
                    <td align="left"><input type='text' name='emailUserFrom' size='40' value='<?php echo get_option( 'resume_email_user_from' ); ?>' /></td>
                </tr>
                <tr>
                    <td><p><b><?php _e( 'User Email Subject:' ); ?> </b></p></td>
                    <td align="left"><input type='text' name='userEmailSubject' size='40' value='<?php echo get_option( 'resume_user_email_subject' ); ?>' /></td>
                </tr>
                <tr>
                    <td valign="top"><p><b><?php _e( 'User Email Copy:' ); ?> </b></p></td>
                    <td align="left" height="250px"><?php wp_editor( get_option( 'resume_user_email_copy' ), 'userEmailCopy', setTinySetting( 'userEmailCopy', '15', false, true, true ) ); ?></td>
                </tr>       
            </tbody>
        </table>
        <br />
        <input type="submit" value="Save Settings" name="update" class="button-primary" />
        </form>
	</div>
    
    
    <div id="rsjpRightCol">
        <table class="widefat">
            <thead>
                <tr>
                    <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/questions-icon-20.png" alt="Helpful Hints" /><?php _e( 'Helpful Hints' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div id="resumeHints">
                        
                            <h3>Adding Resumé Form and Job Postings List to a Page</h3>
                            <p>In order to use this plugin correctly, the following shortcodes must be added to a page of your choosing.</p>
                            <p><b>To add the Resumé Form:</b><br />
                            <code>[resumeForm]</code></p>
                            <p><b>To add the Job Post Listings:</b><br />
                            <code>[jobPostings]</code></p>
                            <br />
                                           
                            <h3>User Email Copy Shortcodes</h3>
                            <p>The following shortcodes may be used in the "User Email Copy" field. <br />
                               If these are not used correctly, you will experience errors.</p>
                            <p><i><code>%fname%</code>, <code>%lname%</code>, <code>%address%</code>, <code>%address2%</code>, <code>%city%</code>, <code>%state%</code>, <code>%zip%</code>, <code>%pnumber%</code>,<br />
                               <code>%pnumbertype%</code>, <code>%snumber%</code>, <code>%snumbertype%</code>, <code>%email%</code>, <code>%job%</code>, <code>%cover%</code>, <code>%resume%</code>, <code>%siteName%</code></i></p>
                            <br />
                            
                            <h3>Closing a Job Posting</h3>
                            <p>On the "Job Postings" page, select the item you would like to close by clicking on the "View/Edit" button.<br />
                               At the bottom of the form, you will see a checkbox that says "Archive". <br />
                               Check that box then press "Update".<br />
                               That job posting is now closed, but can be re-opened if need be.</p>
                            <br />
                            
                            <h3>Attachments</h3>
                            <p>Submission attachments are stored in the <b>wp-content/uploads/rsjp/attachments/</b> folder.<br />
                               The file names are renamed to the submission time which is md5 encoded.<br />
                               We do this so no two files will be the same.</p>
                            <br />
                            
                            <h3>How to Fix Possible Problems</h3>
                            <p>There may be times when something is not working right.<br />
                               Here is how to fix some of those problems.</p>
                            <br />
                            <p><b>Problem:</b> I click on "Submit Resume for this Job", but nothing happens or the page is not found.</p>
                            <p><i><b>Solution:</b> Make sure to have the "Resume Form Page" field filled out to go to the page that has the Form shortcode in it.</i></p>
                            <br />
                            <p><b>Problem:</b> I cannot get the Captcha to work correctly.</p>
                            <p><i><b>Solution:</b> You must make sure that the Captcha Key fields are correctly filled out. Also, make sure that the url for those keys is the same url as this site.</i></p>
                            <br />
                            <p><b>Problem:</b> There is an error when downloading a Submission as a PDF.</p>
                            <p><i><b>Solution:</b> Make sure that the <b>PDF Base File</b> setting is calling the Document Root path and not the URL path.</i></p>
                            <br />                      
                            
                            <h3>Report Bugs and Features Request</h3>
                            <p>If you would like to report any bugs or new features, please go to <a href="http://www.geerservices.com/wordpress-plugins/resume-jobs" target="_blank">
                               www.geerservices.com/wordpress-plugins/resume-jobs</a>.</p>
                            <p>By reporting bugs or asking for other features, you are helping with the growth of this plugin.</p>
                            <p>If you would also like to donate to this plugin, <b><a href="http://www.geerservices.com/wordpress-plugins/resume-jobs" target="_blank">feel free</a></b>!</p>
                            <br />
                            <center>
                            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
                                <input type="hidden" name="cmd" value="_s-xclick" />
                                <input type="hidden" name="hosted_button_id" value="NK3VDD3C4SUXY" />
                                <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" />
                                <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1" />
                            </form>
                            </center>
                            <br />
                            <div style="width:300px; text-align:center; margin:0 auto;">
                                <p>Another <a href="http://www.geerservices.com" target="_blank">Geer Built&reg;</a> Project</p>
                                <a href="http://www.geerservices.com" target="_blank"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/geer-built.png" /></a>
                            </div>
                        </div>    
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>