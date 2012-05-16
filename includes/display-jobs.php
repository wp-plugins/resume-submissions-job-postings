<?php 
global $wpdb;

$view      = $_GET['view'];
$postingID = $_GET['postingID'];
$siteName  = get_option( 'blogname' ); 

$range = 3;

// Check to see if there are other variables
if ( $_SERVER["QUERY_STRING"] ) {
	$connect = '&';
} else {
	$connect = '?';
}
?>

<div id="jobPostings">
	<?php
	// View archived jobs
    if ( $view == 'archive' ){
        ?>
        <h2><?php _e( 'Archived Job Postings' ); ?></h2>
        
        <?php
		$archivedRows = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . JOBTABLE . ' WHERE archive = 1');
		$numRows = $archivedRows;
		
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
		
		$archivedQuery = $wpdb->get_results( 'SELECT * FROM ' . JOBTABLE . ' WHERE archive = 1 ORDER BY pubDate DESC, title DESC LIMIT ' . $offset . ', ' .$rowsperpage );

		if ($numRows > 0){
			$startUL = '<ul>';
			$endUL = '</ul>';
		} else {
			$startUL = '';
			$endUL = '';
		}
		
		echo $startUL;
		
		foreach ( $archivedQuery as $archived ){
			?>
            <li><p><b><a href="<?php echo $_SERVER['REQUEST_URI'] . $connect; ?>postingID=<?php echo $archived->id; ?>"><?php echo $archived->title; ?></a></b> - <i style="font-size:12px;"><?php _e( 'Posted:' ); ?> <?php echo date( 'M j, Y g:ia', strtotime( $archived->pubDate ) ); ?></i></p></li>
            <?php
		}
		
		echo $endUL;
		?>
        
        <p style="text-align:center;">
		<?php
		if ( $numRows > 0 ){
			if ( $currentPage > 1 ) {
			   echo ' <a href="' . $_SERVER['PHP_SELF'] . '&view=archive&currentPage=1">' . _e( 'First' ) . '</a> ';
			   $prevPage = $currentPage - 1;
			   echo ' <a href="{' . $_SERVER['PHP_SELF'] . '&view=archive&currentPage=' . $prevPage . '">«</a> ';
			} 
			
			for ( $x = ( $currentPage - $range ); $x < ( ( $currentPage + $range ) + 1 ); $x++ ) {
			   
			   if ( ( $x > 0 ) && ( $x <= $totalPages ) ) {
				  if ( $x == $currentPage ) {
					 echo ' <b>$x</b> ';
				  } else {
					 echo ' <a href="' . $_SERVER['PHP_SELF'] . '&view=archive&currentPage=$x">' . $x . '</a> ';
				  } 
			   } 
			}
							 
			if ( $currentPage != $totalPages ) {
			   $nextPage = $currentPage + 1;
			   echo ' <a href="' . $_SERVER['PHP_SELF'] . '&view=archive&currentPage=' . $nextPage . '">»</a> ';
			   echo ' <a href="' . $_SERVER['PHP_SELF'] . '&view=archive&currentPage=' . $totalPages . '">' . _e( 'Last' ) . '</a> ';
			}
		}
		?>
        </p>
		
        <?php
		if ( $numRows == 0 ){
			?>
            <p><?php _e( 'There are no archived job postings at this time.' ); ?></p>
            <?php
		}

    } elseif ( $postingID ){
		
		// Display the single job
        $jobPosting = $wpdb->get_row( 'SELECT * FROM ' . JOBTABLE . ' WHERE id = ' . $postingID );
        ?>
        <h2><?php echo $jobPosting->title; ?></h2>
        <p style="padding:0; margin:0;"><?php echo $jobPosting->subTitle; ?></p>
        <p><i style="font-size:12px;"><?php _e( 'Posted:' ); ?> <?php echo date( 'M j, Y g:ia', strtotime( $jobPosting->pubDate ) ); ?></i></p>
        <p><?php echo $jobPosting->description; ?></p>
        <br />
        <?php
		if ( $jobPosting->archive != 1 ){
			?>
            <form method="post" name="goToResume" action="<?php echo get_option( 'resume_form_page' ); ?>">
                <input type="hidden" name="fromPosting" value="<?php echo $jobPosting->title; ?>" />
                <input type="submit" name="fromPostingSubmit" value="<?php _e( 'Submit Resume For This Job' ); ?>" />
            </form>
            <?php
		}
		?>
        
    
        
        
        <?php
    } else {
		
		$search = esc_html( $_POST['rsjpSearch'] );
		
		if ( $search && get_option( 'resume_show_job_search' ) == "Enabled" ) {
			$modQuery = ' AND ( title LIKE "%' . $search . '%" OR subTitle LIKE "%' . $search . '%" OR description LIKE "%' . $search . '%" )';
		} else {
			$modQuery = '';
		}
		// Display all the current jobs 
        $jobPostingQuery = $wpdb->get_results( 'SELECT * FROM ' . JOBTABLE . ' WHERE archive != 1' . $modQuery . ' ORDER BY pubDate DESC' );
        
		// Add search ability
		if ( get_option( 'resume_show_job_search' ) == "Enabled" ){
			?>
            <div id="rsjpSearch">
            <form method="post" name="rsjpSearch" enctype="multipart/form-data">
            	<input type="text" name="rsjpSearch" value="" />
                <input type="submit" name="submitSearch" value="<?php _e( 'Search' ); ?>" />
            </form> 
            </div>
            <?php
			
		}
		
		?>
        
                
        <ul>
        <?php
		if ( $search ) {
			echo '<p><b>' . __( 'Showing results for' ) . ':</b> &quot;' . $search . '&quot;</p>';
		}
        foreach ( $jobPostingQuery as $jobPosting ){
            ?>
            <li><p><b><a href="<?php echo $_SERVER['REQUEST_URI'] . $connect; ?>postingID=<?php echo $jobPosting->id; ?>"><?php echo $jobPosting->title; ?></a></b> - <i style="font-size:12px;"><?php _e( 'Posted:' ); ?> <?php echo date( 'M j, Y g:ia', strtotime( $jobPosting->pubDate ) ); ?></i></p></li>
            <?php
        }
        ?>
        </ul>
        <?php
		
		if ( !$jobPostingQuery ){
			if ( $search ) {
				?>
                <p><i><?php _e( 'Sorry, there were no jobs where found.' ); ?></i></p>
                <?php
			} else {
				?>
                <p><i><?php _e( 'There are no jobs available at this time.' ); ?></i></p>
                <?php
			}
		}
    }
    ?>
</div>