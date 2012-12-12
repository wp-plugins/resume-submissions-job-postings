<?php
global $wpdb, $post;
plugin_basename( $file );

// Declarations
$siteName   = get_option( 'blogname' );
$message    = '';
$searching  = $_POST['search'];
$searchFor  = $_POST['searchFor'];
$showing    = $_POST['showFor'];
$showAllFor = $_POST['showAllFor'];
$job        = 'General Purpose'; 
$find       = array('\'', '\"', '"', '<', '>');
$replace    = array('&#39;', '&quot;', '&quot;', '&lt;', '&gt;');
$edit       = $_POST['edit'];
$ID         = $_GET['id'];
$sendEmail  = $_POST['sendEmail'];

// Delete single submission attachments
$deleteAttach = $_POST['deleteAttach'];
$attachDelete = $_POST['attachDelete'];

if ( $deleteAttach ){
	list( $attachMessage, $didDelete ) = deleteFileFromUpload( $attachDelete, 'attachments' );
	if ( $didDelete ){
		$getAttached = $wpdb->get_row( 'SELECT attachment FROM ' . SUBTABLE . ' WHERE id = "' . $ID . '"' );
		$updatedAttachments = $getAttached->attachment;
		foreach( $attachDelete as $attach ){
			$findIn    = array( ',' . $attach, $attach );
			$replaceIn = array ( '', '' );
			$updatedAttachments = str_replace( $findIn, $replaceIn, $updatedAttachments );
		}
		
		$updateSub = $wpdb->query( 'UPDATE ' . SUBTABLE . ' SET attachment = "' . $updatedAttachments . '" WHERE id = "' . $ID . '"');
		if ( $updateSub ){
			$updatedRecordMessage = __( 'The submission has updated successfully.' );
		} else {
			$updatedRecordMessage = __( 'The submission could not be updated.' );
		}
	}
	$message = '<div class="updated fade" id="message"><p>' . $updatedRecordMessage . $attachMessage . '</p></div>';
}


// Send PDF to other email
if ( $sendEmail ) {
	
	$seTo      = $_POST['seTo'];
	$seSubject = $_POST['seSubject'];
	$seCopy    = $_POST['secopy'];
	$sePDF     = $_POST['sePDF'];
	
	if ( $seTo ) {
		
		$seAttachment = array( WP_CONTENT_DIR . '/uploads/rsjp/pdfs/' . $sePDF );
		
		$seSubject = $seSubject;
		$seBody    = '<html>
						<head>
							<title>' . $seSubject . '</title>
						</head>
						<body>
							' . $seCopy . '
						</body>
					</html>';
		$seHeaders  = 'MIME-Version: 1.0' . "\r\n";
		$seHeaders .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		$seHeaders .= 'From: "' . $siteName . '"<' . get_option( 'resume_send_admin_email_to' ) . '>' . "\r\n";
		$mailIt     = wp_mail( $seTo, $seSubject, $seBody, $seHeaders, $seAttachment );
		
		if ( $mailIt ){
			$message = '<div class="updated fade" id="message"><p>' . __( 'Resume was successfully sent to' ) . ' <b>' . $seTo . '</b>.</p></div>';
		} else {
			$message = '<div class="error fade" id="message"><p>' . __( 'Something went wrong. Please check your form and try again' ) . '.</p></div>';
		}
	
	} else {
		$message = '<div class="error fade" id="message"><p>' . __( 'To send an email, there must be an email address' ) . '...</p></div>';
	}
	
}


// BOF View/Edit Submission 
if ( isset( $ID ) ){
	$single = $wpdb->get_results( 'SELECT * FROM ' . SUBTABLE . ' WHERE id = "' . $ID . '"' );
	
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
	$cover       = $_POST['cover'];
	$resume      = $_POST['resume'];
	
	$resumeSubmit = '';
	$formError    = false;
	$range        = 3;
	$currentPage  = $_GET['currentPage'];
	 
	
	if ( isset( $edit ) ){
		
		$updateQuery = $wpdb->query( 'UPDATE ' . SUBTABLE . ' SET fname = "' . $fname . '",
																  lname = "' . $lname . '",
																  address = "' . $address . '",
																  address2 = "' . $address2 . '",
																  city = "' . $city . '",
																  state = "' . $state . '",
																  zip = "' . $zip . '",
																  pnumber = "' . $pnumber . '",
																  pnumbertype = "' . $pnumbertype . '",
																  snumber = "' . $snumber . '",
																  snumbertype = "' . $snumbertype . '",
																  email = "' . $email . '"
																  WHERE id = "' . $ID . '"' );
		
		// What to display 
		if( $updateQuery ){
			$updateText = __( 'The submission was succesfully updated.' );
			$message    = '<div class="updated fade" id="message"><p>' . $updateText . '</p></div>';
		} else {
			$updateText  = __( 'Sorry, the submission could not be updated.' );
			$updateText2 = __( 'There may not have been any change to the entry.' );
			$message     = '<div class="error fade" id="message"><p>' . $updateText . ' <br />' . $updateText2 . '</p></div>';
		}
	}
}
// EOF View/Edit


// BOF Delete 
$deleteSubmit = $_POST['deleteSubmit'];
$deleteID     = $_POST['deleteID'];

$attachOptions = get_option( 'resume_attachments' );

if ( $deleteSubmit ){
	$count = 0;
	$sep = '';
	foreach ( $deleteID as $id ) {
		if ( $count > 0 ) 
			$sep = ',';
    	$deleteIDs .= $sep . $id;
		$count++;
		
		// Delete Attachments if enabled
		if ( $attachOptions['delete'] == 'Enabled' ) {
			$getAttach = $wpdb->get_row( $wpdb->prepare( 'SELECT attachment FROM ' . SUBTABLE . ' WHERE id = "%d"', $id ) );
			$queDelete = explode( ',', $getAttach->attachment );
			
			list( $deleteMessage, $didRemove ) = deleteFileFromUpload( $queDelete, 'attachments' );
		}
	}
    $deleteQuery = $wpdb->query('DELETE FROM ' . SUBTABLE . ' WHERE id IN ( ' . $deleteIDs . ' ) ');'';
  	
	// Display message after delete
    if ( $deleteQuery ){
		$deleteText = __( 'Submission(s) have been deleted.' ); 
		$message    = '<div class="updated fade" id="message"><p>' . $deleteText . '</p>' . $deleteMessage . '</div>';
    }
}
// EOF Delete
?>


<div class="wrap alternate">
	
    <div id="icon-rsjp-submissions" class="icon32"></div>
	<h2><?php _e( 'Resume Submissions' ); ?></h2>
	<?php echo $message; ?>
	<br class="a_break" style="clear: both;"/>
    
	<?php
    if( !isset( $edit ) && !isset( $ID ) ){
		?>
		<div id="rsjpLeftCol">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="280px" align="left">
                    <form method="post" name="search" id="search">
                        <p><?php _e( 'Find:' ); ?> <input type="text" name="searchFor" /> <input type="submit" name="search" value="<?php _e( 'Search' ); ?>" class="button-secondary" /></p>
                    </form>
                    </td>
                    <td width="280px" align="left">
                    <?php 
                    if ( $searchFor != '' || $showAllFor != '' ){ ?>
                        <form name="showall" method="post" enctype="multipart/form-data" action="<?php echo admin_url(); ?>admin.php?page=rsjp-submissions">
                            <input name="showall" type="submit" value="<?php _e( 'Show All' ); ?>" class="button-secondary" />
                        </form>
                        <?php
                    }
                    ?>
                    </td>
    
                    <td align="right">
                    <?php 
					$getJobsArg = array( 'post_type'  => 'rsjp_job_postings',
										 'orderby'    => 'post_date',
                                         'order'      => 'DESC',
										 'meta_query' => array(
															 array(
																 'key' => 'rsjp_archive_posting',
																 'value' => 1,
																 'compare' => 'NOT LIKE'
															 ) ) ); 
			  		$getJobs = get_posts( $getJobsArg );
                    ?>
                    <form method="post" name="showfor" id="showfor">
                        <p><?php _e( 'Show All For:' ); ?> <select name="showAllFor">       
                           <option value="">-- Select --</option>                     	
                           <?php 
						   foreach( $getJobs as $getJob ){
							   ?>
						       <option value="<?php echo $getJob->post_name; ?>"><?php echo $getJob->post_title; ?></option>
                           <?php 
						   }
						   wp_reset_postdata();
						   ?>
                            <option value="General Purpose" <?php if ( $showFor->title == 'General Purpose' ){ echo 'selected="selected"'; } ?>><?php _e( 'General Purpose' ); ?></option>    
                        </select>
                        <input type="submit" name="showFor" value="<?php _e( 'Display' ); ?>" class="button-secondary" /></p>
                    </form>
                    </td>
                </tr>
            </table>
        </div>
        <?php
	}
      
	// If not viewing a single resume, show the list
	if( !isset( $edit ) && !isset( $ID ) ){
		?>
		<div id="rsjpLeftCol">
			<?php
			if ( $searchFor != '' ){
				$queryFind = ' WHERE fname LIKE "%' . $searchFor . '%" OR lname LIKE "%' . $searchFor . '%" OR email LIKE "%' . $searchFor . '%" OR cover LIKE "%' . $searchFor . '%" OR resume LIKE "%' . $searchFor . '%" OR job LIKE "%' . $searchFor . '%"';
			} elseif( $showAllFor != '' ){
				$queryFind = ' WHERE job = "' . $showAllFor . '"';	
			} else {
				$queryFind = '';
			}
		
			// Get the number of entries to start the pagination
			$getNum = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . SUBTABLE . $queryFind );
			$numRows = $getNum;
			
			$rowsPerPage = 10;
			$totalPages = ceil( $numRows / $rowsPerPage );
			
			if ( isset( $_GET['currentPage'] ) && is_numeric( $_GET['currentPage'] ) ) {
			   $currentPage = ( int ) $_GET['currentPage'];
			} else {
			   $currentPage = 1;
			}
			
			if ( $currentPage > $totalPages ) {
			   $currentPage = $totalPages;
			}
			if ( $currentPage < 1 ) {
			   $currentPage = 1;
			} 
			
			$offSet = ( $currentPage - 1 ) * $rowsPerPage;
			
			$infoQuery = $wpdb->get_results( 'SELECT * FROM ' . SUBTABLE . $queryFind . ' ORDER BY pubdate DESC, lname DESC, fname DESC LIMIT ' . $offSet . ', ' .$rowsPerPage );
			?>
		
			<form name="deleteEntries" method="post" enctype="multipart/form-data">
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" width="20px">&nbsp;</th>
						<th scope="col" width="300px"><?php _e( 'Name' ); ?></th>
						<th scope="col" width="280px"><?php _e( 'E-Mail' ); ?></th>
						<th scope="col" width="240px"><?php _e( 'Regarding Job' ); ?></th>
						<th scope="col" width="200px"><?php _e( 'Submission Date' ); ?></th>
						<th scope="col">&nbsp;</th>
						<th scope="col">&nbsp;</th>
					</tr>
				</thead>
				<tbody><?php 
						if ( $numRows > 0 ){
							foreach ( $infoQuery as $info ){
								$getJobArg = array( 'numberposts'     => 1,
								                    'post_type'       => 'rsjp_job_postings',
								   					'name' => $info->job ); 
			 					$getJob = get_posts( $getJobArg );
								?>
								<tr>
									<td><input type="checkbox" name="deleteID[]" value="<?php echo $info->id; ?>" /></td>
									<td><p><?php echo $info->fname; ?> <?php echo $info->lname; ?></p></td>
									<td><p><?php echo $info->email; ?></p></td>
									<td><p><?php echo $getJob[0]->post_title; ?></p></td>
									<td><p><?php echo date( 'F j, Y g:ia', strtotime( $info->pubdate ) ); ?></p></td>
									<td>&nbsp;</td>
									<td align="right" width="50px">
										<input name="view" type="button" value="<?php _e( 'View/Edit' ); ?>" class="button-secondary" onclick="location.href='<?php echo admin_url(); ?>admin.php?page=rsjp-submissions&id=<?php echo $info->id; ?>'" /></td>
								</tr>
								<?php
								wp_reset_postdata();
							}
						} else {
							?>
								<tr>
									<td>&nbsp;</td>
									<td><?php if ( $searchFor != '' ){ ?>
											<p><?php _e( 'There are no submissions that contain' ); ?> &quot;<b><?php echo $searchFor; ?></b>&quot;.</p>
										<?php } elseif( $showAllFor != '' ){ ?> 
											<p><?php _e( 'There are no submissions for the job posting' ); ?> &quot;<b><?php echo $showAllFor; ?></b>&quot;.</p> 
										<?php } else { ?> 
											<p><?php _e( 'There are no submissions at this time.' ); ?></p> 
										<?php }?></td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									
									<td>&nbsp;</td>
									<td>&nbsp;</td>
                                    <td>&nbsp;</td>
								</tr>
							<?php
							
						}
										 
						if ( $getNum != '' && $getNum != 0 ){
							
						// Display the pagination
						?>
						<div class="tablenav">
							<div class="tablenav-pages">
								<span class="displaying-num">Displaying <?php echo $offSet + 1; ?> - <?php echo $offSet + count( $infoQuery ); ?> of <?php echo $numRows; ?></span>
								
								
								<?php
								if ( $currentPage > 1 ) {
								   echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=rsjp-submissions&currentPage=1">First</a> ';
								   $prevPage = $currentPage - 1;
								   echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=rsjp-submissions&currentPage=' . $prevPage . '">«</a> ';
								} 
								
								for ( $x = ( $currentPage - $range ); $x < ( ( $currentPage + $range ) + 1 ); $x++ ) {
								   
								   if ( ( $x > 0 ) && ( $x <= $totalPages ) ) {
									  if ( $x != $currentPage ) {
										 echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=rsjp-submissions&currentPage=' . $x . '">' . $x . '</a> ';
									  } 
								   } 
								}
												 
								if ( $currentPage != $totalPages ) {
								   $nextPage = $currentPage + 1;
								   echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=rsjp-submissions&currentPage=' . $nextPage . '">»</a> ';
								   echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=rsjp-submissions&currentPage=' . $totalPages . '">Last</a> ';
								}
								?>
							</div> 
						</div>
						<?php
						}
						?>
				</tbody>
			</table>
			<?php
			if ( $getNum > 0 ){
				?>
					<input type="submit" name="deleteSubmit" value="Delete Record(s)" class="button-secondary" onClick="return( confirm( '<?php _e( 'Are you sure you want to delete these entries?' ); ?>' ) )" />
                </form>
				<?php
			}
			?>
        </div>
        
        <div id="rsjpRightCol">
            <div id="rsjpDownloads">
                <table class="widefat">
                    <thead>
                        <tr>
                            <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/download-icon-20.png" alt="<?php _e( 'Export Submissions' ); ?>" /><?php _e( 'Export Submissions' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <?php
                                // Export to CSV
                                exportSubToCSV();
                                ?>
                                <table cellpadding="2" cellspacing="2">
                                    <tr>
                                        <td><a href="<?php echo resume_get_plugin_dir( 'go' ); ?>/base-files/submission-entries.csv"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/csv-icon.gif" border="0" alt="<?php _e( 'Download as CSV' ); ?>" title="<?php _e( 'Download as CSV' ); ?>" /></a></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
	} else {	
	  // Display the single entry for update
	  $single = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . SUBTABLE . ' WHERE id = "%d"', $ID ) );
	  ?>
	  <div id="rsjpLeftCol">
		  <table width="100%" cellpadding="0" cellspacing="0">
			  <tr>
				  <td><form name="back" method="post" id="backButton" enctype="multipart/form-data" action="<?php echo admin_url(); ?>admin.php?page=rsjp-submissions">
										<input name="back" type="submit" value="<?php _e( 'Back' ); ?>" class="button-secondary" />
									</form>
				  </td>
			  </tr>
		  </table>
		  
		  <br class="a_break" style="clear: both;"/>
				  
		  <form name='form' id='form' class='form' method='POST'>
		  <table width="100%" cellpadding="0" cellspacing="0">
			  <tr>
				  <td width="115px"></td>
				  <td width="145px"></td>
			  </tr>
			  <?php 
			  if ( grabContents( get_option( 'resume_input_fields' ), 'fname', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'First Name' ); ?>: </p></td>
					  <td><input type='text' name='fname' size='40'value='<?php echo $single->fname; ?>' /></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'lname', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'Last Name' ); ?>: </p></td>
					  <td><input type='text' name='lname' size='40' value='<?php echo $single->lname; ?>' /></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'address', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'Address' ); ?>: </p></td>
					  <td><input type='text' name='address' size='40' value='<?php echo $single->address; ?>' /></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'address2', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'Suite/Apt' ); ?>: </p></td>
					  <td><input type='text' name='address2' size='40' value='<?php echo $single->address2; ?>' /></td>
					  <td valign="top"></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'city', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'City' ); ?>: </p></td>
					  <td><input type='text' name='city' size='40' value='<?php echo $single->city; ?>' /></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'state', 0 ) ) {	
				  $theStateList =  get_option( 'resume_state_list' );
				  ?>
				  <tr>
					  <td><p><?php _e( 'State' ); ?>: </p></td>
					  <td><select name="state" id="state">
							  <?php echo arrayToSelect( $theStateList['list'], $single->state ); ?>
						  </select></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'zip', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'Zip Code' ); ?>: </p></td>
					  <td><input type='text' name='zip' size='10' value='<?php echo $single->zip; ?>' /></td>
				  </tr>
				  <?php
			  }
			  ?>
		  </table>
		  <table width="100%" cellpadding="0" cellspacing="0">	
			  <?php
			  if ( grabContents( get_option( 'resume_input_fields' ), 'pnumber', 0 ) ) {	
				  ?>
				  <tr>
					  <td width="190px"><p><?php _e( 'Primary Contact Number' ); ?>: </p></td>
					  <td width="160px"><input type='text' name='pnumber' size='25' value='<?php echo $single->pnumber; ?>' /></td>
					  <td valign="top"><p><input type='text' name='pnumbertype' size='5' value='<?php echo $single->pnumbertype; ?>' /></p></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'snumber', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'Secondary Contact Number' ); ?>: </p></td>
					  <td><input type='text' name='snumber' size='25' value='<?php echo $single->snumber; ?>' /></td>
					  <td valign="top"><p><input type='text' name='snumbertype' size='5' value='<?php echo $single->snumbertype; ?>' /></p></td>
				  </tr>
				  <?php
			  }	
			  ?>
		  </table>
		  <table width="100%" cellpadding="0" cellspacing="0">	
			  <?php
			  if ( grabContents( get_option( 'resume_input_fields' ), 'email', 0 ) ) {	
				  ?>
				  <tr>
					  <td width="115px"><p><?php _e( 'E-Mail Address' ); ?>: </p></td>
					  <td align="left"><input type='text' name='email' size='60' value='<?php echo $single->email; ?>' /></td>
				  </tr>
				  <?php
			  }
			  
			  $getJobArg = array( 'numberposts'     => 1,
								  'post_type'       => 'rsjp_job_postings',
								  'name' => $single->job ); 
			  $getJob = get_posts( $getJobArg );
			  ?>
			  <tr>
				  <td><p><?php _e( 'Regarding Job' ); ?>: </p></td>
					  <td><input type='text' name='job' size='60' value='<?php echo $getJob[0]->post_title; ?>' readonly="readonly" /></td>
				  <?php
                  wp_reset_postdata();
			      ?>
			  </tr>
		  </table>
		  <br />
		  <table width="100%;" cellpadding="0" cellspacing="0">
			  <?php
			  if ( grabContents( get_option( 'resume_input_fields' ), 'cover', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><b><?php _e( 'Cover Letter' ); ?>: </b></p></td>
					  <td></td> 
				  </tr>
				  <tr>
					  <td style="background-color:#FFFFFF; border:1px solid #CCC; padding:5px;"><p><?php echo html_entity_decode( $single->cover ); ?></p></td>
				  </tr>	
				  <tr>
					  <td>&nbsp;</td>
					  <td></td>
				  </tr>
				  <?php
			  }
			  
			  if ( grabContents( get_option( 'resume_input_fields' ), 'resume', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><b><?php _e( 'Resume' ); ?>:</b></p></td>
					  <td></td>
				  </tr>
				  <tr>
					  <td style="background-color:#FFFFFF; border:1px solid #CCC; padding:5px;"><p><?php echo html_entity_decode( $single->resume ); ?></p></td>
				  </tr>
				  <?php
			  }
			  ?>
			  <tr>
				  <td><input type='hidden' name='edit' value='Edit' />
					  <p><input type='submit' value='<?php _e( 'Update Resume' ); ?>' name='submit' class="button-primary" /></p></td>
				  <td></td>
			  </tr>
		  </table>       
		  </form>
	  </div>
	  
	  <div id="rsjpRightCol">
		  <?php 
		  if ( grabContents( get_option( 'resume_input_fields' ), 'attachment', 0 ) ) {	
			  ?>
		  
			  <div id="submissionAttachments">
			  <form enctype="multipart/form-data" method="post">
			  <table class="widefat">
				  <thead>
					  <tr>
						  <th scope="col">&nbsp;</th>
						  <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/attachments-icon-20.png" alt="<?php _e( 'Submission Attachments' ); ?>" /><?php _e( 'Submission Attachments' ); ?></th>
					  </tr>
				  </thead>
				  <tbody>
					  <?php
					  if ( $single->attachment != '' ) {
						  $attachments = explode( ',', $single->attachment );
						  $attachCount = 1;
						  foreach ( $attachments as $attach){
							  ?>
							  <tr>
								  <td width="10px"><input type="checkbox" name="attachDelete[]" value="<?php echo $attach; ?>" />
								  <td><a href="<?php echo WP_CONTENT_URL; ?>/uploads/rsjp/attachments/<?php echo $attach; ?>" target="_blank"><?php echo $attach; ?></a></td>
							  </tr>
							  <?php
							  $attachCount++;
						  }
					  } else {
						  ?>
                          <tr>
                              <td width="10px">&nbsp;</td>
                              <td><p><?php _e( 'No attachments for this submission.' ); ?></p></td>
                          </tr>
                          <?php
					  }
					  ?>    
				  </tbody>
			  </table>
			      <input type="submit" name="deleteAttach" value="<?php _e( 'Delete Attachment(s)' ); ?>" class="button-secondary" onClick="return( confirm( '<?php _e( 'Are you sure you want to delete these attachments?' );?>' ) )" />
			  </form>
			  </div>
			  <?php
		  }
		  ?>
		  <br />
		  <br />
		  <table class="widefat rsjp-download">
			  <thead>
				  <tr>
					  <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/download-icon-20.png" alt="<?php _e( 'Export Submission' ); ?>" /><?php _e( 'Export Submission' ); ?></th>
				  </tr>
			  </thead>
			  <tbody>
				  <tr>
					  <td>
						  <?php
						  // Export to PDF
						  $pdfLink = @exportSubToPDF( $single->id );
						  ?>
						  <table cellpadding="2" cellspacing="2">
							  <tr>
								  <td><a href="javascript:window.print()"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/printing-icon-32.png" alt="<?php _e( 'Print Resume' ); ?>" title="<?php _e( 'Print Resume' ); ?>" id="printResume" /></a></a>
								  <td><a href="<?php echo WP_CONTENT_URL . '/uploads/rsjp/pdfs/' . $pdfLink; ?>" target="_blank"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/pdf-icon.png" alt="<?php _e( 'Download to PDF' ); ?>" title="<?php _e( 'Download to PDF' ); ?>" /></a></td>
							  </tr>
						  </table>
					  </td>
				  </tr>
			  </tbody>
		  </table>
          <br />
          <br />
		  <table class="widefat rsjp-email">
			  <thead>
				  <tr>
					  <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/emailing-icon-20.png" alt="<?php _e( 'Email Submission' ); ?>" /><?php _e( 'Email Submission' ); ?></th>
				  </tr>
			  </thead>
              <form name="sendResumeToEmail" enctype="multipart/form-data" method="post">
			  <tbody>
                  <tr>
                      <td>
                          <table cellpadding="2" cellspacing="2">
                              <tr>
                                  <td><p><b><?php _e( 'To' ); ?>:</b></p></td>
                                  <td><input type="text" name="seTo" value="" size="40" /></td>
                              </tr>
                              <tr>
                                  <td><p><b><?php _e( 'Subject' ); ?>:</b></p></td>
                                  <td><input type="text" name="seSubject" value="" size="40" /></td>
                              </tr>
                              <tr>
                                  <td><p><b><?php _e( 'Text' ); ?>:</b></p></td>
                                  <td><input type="hidden" name="sePDF" value="<?php echo $pdfLink; ?>" /></td>
                              </tr>
                          </table>
                          <table cellpadding="2" cellspacing="2">
                              <tr>
                                  <td><?php wp_editor( 'Hello, <br />The above attachment is the resume for ' . $single->fname . ' ' . $single->lname . '. <br /><br />If you cannot see this attachment, please <a href="' . WP_CONTENT_URL . '/uploads/rsjp/pdfs/' . $pdfLink . '" target="_blank">Click Here</a>.',
                                                       'secopy', setTinySetting( 'secopy', '10', false, true, true ) ); ?></td>
                              </tr>
                              <tr>
                                  <td><input type="submit" name="sendEmail" value="<?php _e( 'Send Email' ); ?>" class="button-primary"</td>
                              </tr>
                          </table>
                      </td>
                  </tr>
			  </tbody>
              </form>
		  </table>
	  </div>
	  <?php  
	}
?>
</div>