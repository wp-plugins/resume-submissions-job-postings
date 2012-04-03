<?php
global $wpdb;
plugin_basename( $file );

// Declarations
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
			$updatedRecordMessage = '<p>The submission has updated successfully.</p>';
		} else {
			$updatedRecordMessage = '<p>The submission could not be updated.</p>';
		}
	}
	$message = '<div class="updated fade" id="message">' . $updatedRecordMessage . $attachMessage . '</div>';
}

// BOF View/Edit Submission 
if ( isset( $ID ) ){
	$single = $wpdb->get_results( 'SELECT * FROM ' . SUBTABLE . ' WHERE id = "' . $ID . '"' );
	
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
			$message = '<div class="updated fade" id="message"><p>The submission was succesfully updated.</p></div>';
		} else {
			$message = '<div class="updated fade" id="message"><p>Sorry, the submission could not be updated. <br />There may not have been any change to he entry.</p></div>';
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
			$getAttach = $wpdb->get_row( $wpdb->prepare( 'SELECT attachment FROM ' . SUBTABLE . ' WHERE id = "' . $id . '"' ) );
			$queDelete = explode( ',', $getAttach->attachment );
			
			list( $deleteMessage, $didRemove ) = deleteFileFromUpload( $queDelete, 'attachments' );
		}
	}
    $deleteQuery = $wpdb->query('DELETE FROM ' . SUBTABLE . ' WHERE id IN ( ' . $deleteIDs . ' ) ');'';
  	
	// Display message after delete
    if ( $deleteQuery ){
		$message = '<div class="updated fade" id="message"><p>Submission(s) have been deleted.</p>' . $deleteMessage . '</div>';
    }
}
// EOF Delete
?>


<div class="wrap alternate">
	
    <div id="icon-rsjp-submissions" class="icon32"></div>
	<h2><?php _e( 'Resumé Submissions' ); ?></h2>
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
                        <p><?php _e( 'Find:' ); ?> <input type="text" name="searchFor" /> <input type="submit" name="search" value="Search" class="button-secondary" /></p>
                    </form>
                    </td>
                    <td width="280px" align="left">
                    <?php 
                    if ( $searchFor != '' || $showAllFor != '' ){ ?>
                        <form name="showall" method="post" enctype="multipart/form-data" action="<?php echo admin_url(); ?>admin.php?page=resume-submissions-job-postings/resume-submission.php">
                            <input name="showall" type="submit" value="Show All" class="button-secondary" />
                        </form>
                        <?php
                    }
                    ?>
                    </td>
    
                    <td align="right">
                    <?php 
                    $showFor = $wpdb->get_results( $wpdb->prepare( 'SELECT title FROM ' . JOBTABLE . ' WHERE archive != "1" ORDER BY title DESC' ) );
                    ?>
                    <form method="post" name="showfor" id="showfor">
                        <p><?php _e( 'Show All For:' ); ?> <select name="showAllFor">                            	
                            <?php echo arrayToSelect($showFor, $showFor->title); ?>
                            <option value="General Purpose" <?php if ( $showFor->title == 'General Purpose' ){ echo 'selected="selected"'; } ?>>General Purpose</option>    
                        </select>
                        <input type="submit" name="showFor" value="Display" class="button-secondary" /></p>
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
								
								?>
								<tr>
									<td><input type="checkbox" name="deleteID[]" value="<?php echo $info->id; ?>" /></form></td>
									<td><p><?php echo $info->fname; ?> <?php echo $info->lname; ?></p></td>
									<td><p><?php echo $info->email; ?></p></td>
									<td><p><?php echo $info->job; ?></p></td>
									<td><p><?php echo date( 'F j, Y g:ia', strtotime( $info->pubdate ) ); ?></p></td>
									<td>&nbsp;</td>
									<td align="right" width="50px">
										<form name="view_<?php echo $info->id; ?>" method="post" enctype="multipart/form-data" action="<?php echo admin_url(); ?>admin.php?page=resume-submissions-job-postings/resume-submission.php&id=<?php echo $info->id; ?>">
											<input name="view" type="submit" value="View/Edit" class="button-secondary" />
										</form></td>
								</tr>
								<?php
							}
						} else {
							?>
								<tr>
									<td>&nbsp;</td>
									<td><?php if ( $searchFor != '' ){ ?>
											<p><?php _e( 'There are no submissions that contain &quot;<b>' . $searchFor . '</b>&quot;.' ); ?></p>
										<?php } elseif( $showAllFor != '' ){ ?> 
											<p><?php _e( 'There are no submissions for the job posting &quot;<b>' . $showAllFor . '</b>&quot;.' ); ?></p> 
										<?php } else { ?> 
											<p><?php _e( 'There are no submissions at this time.' ); ?></p> 
										<?php }?></td>
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
								   echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=resume-submissions-job-postings/resume-submission.php&currentPage=1">First</a> ';
								   $prevPage = $currentPage - 1;
								   echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=resume-submissions-job-postings/resume-submission.php&currentPage=' . $prevPage . '">«</a> ';
								} 
								
								for ( $x = ( $currentPage - $range ); $x < ( ( $currentPage + $range ) + 1 ); $x++ ) {
								   
								   if ( ( $x > 0 ) && ( $x <= $totalPages ) ) {
									  if ( $x != $currentPage ) {
										 echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=resume-submissions-job-postings/resume-submission.php&currentPage=' . $x . '">' . $x . '</a> ';
									  } 
								   } 
								}
												 
								if ( $currentPage != $totalPages ) {
								   $nextPage = $currentPage + 1;
								   echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=resume-submissions-job-postings/resume-submission.php&currentPage=' . $nextPage . '">»</a> ';
								   echo ' <a href="' . $_SERVER['PHP_SELF'] . '?page=resume-submissions-job-postings/resume-submission.php&currentPage=' . $totalPages . '">Last</a> ';
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
				<input type="submit" name="deleteSubmit" value="Delete Record(s)" class="button-secondary" onClick="return( confirm( 'Are you sure you want to delete these entries?' ) )" />
				<?php
			}
			?>
        </div>
        
        <div id="rsjpRightCol">
            <div id="rsjpDownloads">
                <table class="widefat">
                    <thead>
                        <tr>
                            <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/download-icon-20.png" alt="Export Submissions" /><?php _e( 'Export Submissions' ); ?></th>
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
                                        <td><a href="<?php echo resume_get_plugin_dir( 'go' ); ?>/base-files/submission-entries.csv"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/csv-icon.gif" border="0" alt="Download as CSV" title="Download as CSV" /></a></td>
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
	  $single = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . SUBTABLE . ' WHERE id = "' . $ID . '"' ) );
	  ?>
	  <div id="rsjpLeftCol">
		  <table width="100%" cellpadding="0" cellspacing="0">
			  <tr>
				  <td><form name="back" method="post" id="backButton" enctype="multipart/form-data" action="<?php echo admin_url(); ?>admin.php?page=resume-submissions-job-postings/resume-submission.php">
										<input name="back" type="submit" value="Back" class="button-secondary" />
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
					  <td><p><?php _e( 'First Name:' ); ?> </p></td>
					  <td><input type='text' name='fname' size='40'value='<?php echo $single->fname; ?>' /></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'lname', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'Last Name:' ); ?> </p></td>
					  <td><input type='text' name='lname' size='40' value='<?php echo $single->lname; ?>' /></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'address', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'Address:' ); ?> </p></td>
					  <td><input type='text' name='address' size='40' value='<?php echo $single->address; ?>' /></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'address2', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'Suite/Apt:' ); ?> </p></td>
					  <td><input type='text' name='address2' size='40' value='<?php echo $single->address2; ?>' /></td>
					  <td valign="top"></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'city', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'City:' ); ?> </p></td>
					  <td><input type='text' name='city' size='40' value='<?php echo $single->city; ?>' /></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'state', 0 ) ) {	
				  $theStateList =  get_option( 'resume_state_list' );
				  ?>
				  <tr>
					  <td><p><?php _e( 'State:' ); ?> </p></td>
					  <td><select name="state" id="state">
							  <?php echo arrayToSelect( $theStateList['list'], $single->state ); ?>
						  </select></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'zip', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'Zip Code:' ); ?> </p></td>
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
					  <td width="190px"><p><?php _e( 'Primary Contact Number:' ); ?> </p></td>
					  <td width="160px"><input type='text' name='pnumber' size='25' value='<?php echo $single->pnumber; ?>' /></td>
					  <td valign="top"><p><input type='text' name='pnumbertype' size='5' value='<?php echo $single->pnumbertype; ?>' /></p></td>
				  </tr>
				  <?php
			  }
			  if ( grabContents( get_option( 'resume_input_fields' ), 'snumber', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><?php _e( 'Secondary Contact Number:' ); ?> </p></td>
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
					  <td width="115px"><p><?php _e( 'E-Mail Address:' ); ?> </p></td>
					  <td align="left"><input type='text' name='email' size='60' value='<?php echo $single->email; ?>' /></td>
				  </tr>
				  <?php
			  }
			  ?>
			  <tr>
				  <td><p><?php _e( 'Regarding Job:' ); ?> </p></td>
				  <td><input type='text' name='job' size='60' value='<?php echo $single->job; ?>' readonly="readonly" /></td>
			  </tr>
		  </table>
		  <br />
		  <table width="100%;" cellpadding="0" cellspacing="0">
			  <?php
			  if ( grabContents( get_option( 'resume_input_fields' ), 'cover', 0 ) ) {	
				  ?>
				  <tr>
					  <td><p><b><?php _e( 'Cover Letter:' ); ?> </b></p></td>
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
					  <td><p><b><?php _e( 'Resumé:'); ?></b></p></td>
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
					  <p><input type='submit' value='Update Resume' name='submit' class="button-primary" /></p></td>
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
						  <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/attachments-icon-20.png" alt="Submission Attachments" /><?php _e( 'Submission Attachments' ); ?></th>
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
                              <td><p>No attachments for this submission.</p></td>
                          </tr>
                          <?php
					  }
					  ?>    
				  </tbody>
			  </table>
			  <input type="submit" name="deleteAttach" value="Delete Attachment(s)" class="button-secondary" onClick="return( confirm( 'Are you sure you want to delete these attachments?' ) )" />
			  </form>
			  </div>
			  <?php
		  }
		  ?>
		  <br />
		  <br />
		  <table class="widefat">
			  <thead>
				  <tr>
					  <th scope="col"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/download-icon-20.png" alt="Export Submission" /><?php _e( 'Export Submission' ); ?></th>
				  </tr>
			  </thead>
			  <tbody>
				  <tr>
					  <td>
						  <?php
						  // Export to CSV
						  //exportSubToPDF( $single->id );
						  ?>
						  <table cellpadding="2" cellspacing="2">
							  <tr>
								  <td><a href="javascript:window.print()"><img src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/printing-icon-32.png" alt="Print Resume" title="Print Resume" id="printResume" /></a></a>
								  <?php /* <td><input type="image" name="subToPDF" src="<?php echo resume_get_plugin_dir( 'go' ); ?>/images/icons/pdf-icon.png" onclick="exportSubToPDF()" alt="Download to PDF" title="Download to PDF" /></td>*/ ?>
							  </tr>
						  </table>
					  </td>
				  </tr>
			  </tbody>
		  </table>
	  </div>
	  <?php  
	}
?>
</div>