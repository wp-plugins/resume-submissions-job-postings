<?php
add_action( 'widgets_init', array( 'resume_job_postings', 'register' ) );
register_activation_hook( __FILE__, array( 'resume_job_postings', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'resume_job_postings', 'deactivate' ) );
class resume_job_postings {
	function activate(){
		$data = array( 'title' => '' ,'display' => 5 );
		if ( !get_option( 'resume_job_postings_widget' ) ){
			add_option( 'resume_job_postings_widget' , $data );
		} else {
			update_option( 'resume_job_postings_widget' , $data );
		}
	}
	function deactivate(){
		delete_option( 'resume_job_postings_widget' );
	}
	function control(){
		$data          = get_option( 'widget_name' );
		$widgetTitle   = $data['title'];
		$widgetDisplay = $data['display'];
		?>
        <label for="job_display_title">Title: <input type="text" name="job_display_title" value="<?php echo $widgetTitle; ?>" size="33" /></label><br />
        <label for="job_display_amount">Posts to Display: <input type="text" name="job_display_amount" value="<?php echo $widgetDisplay; ?>" size="5" /></label>	
    	<?php
		if ( isset( $_POST['job_display_title'] ) ){
			$data['title']   = attribute_escape( $_POST['job_display_title'] );
			$data['display'] = attribute_escape( $_POST['job_display_amount'] );
			update_option( 'resume_job_postings_display', $data );
		}
    }
	function widget( $args ){
		global $wpdb;
		
		$data          = get_option( 'resume_job_postings_widget' );
		$widgetTitle   = $data['title'];
		$widgetDisplay = $data['display'];
		
		if ( !$widgetDisplay ){
			$widgetDisplay = 5;
		}
		
		echo $args['before_widget'];
		echo $args['before_title'] . $widgetTitle . $args['after_title'];
		
		$jobs = $wpdb->get_results( 'SELECT id, title, pubDate FROM ' . JOBTABLE . ' ORDER BY pubDate DESC, title DESC LIMIT ' . $widgetDisplay );
		?>
        <ul>
        	<?php
			foreach ($jobs as $job){
				// Check to see if there are other variables
				if ( strpos( get_option( 'resume_jobs_page' ), '?' ) ) {
					$connect = '&';
				} else {
					$connect = '?';
				}
				?>
                <li><a href="<?php echo get_option( 'resume_jobs_page' ) . $connect; ?>postingID=<?php echo $job->id; ?>"><?php echo $job->title; ?></a><br />
                &nbsp;&nbsp; - <i style="font-size:10px;">Posted: <?php echo date( 'M j, Y', strtotime( $job->pubDate ) ); ?></i></li>
                <?php
			}
		?>
        </ul>
        <?php
		echo $args['after_widget'];
	}
	function register(){
		register_sidebar_widget( 'Current Job Postings', array( 'resume_job_postings', 'widget' ) );
		register_widget_control( 'Current Job Postings', array( 'resume_job_postings', 'control' ) );
	}
}