<?php 
if( $archive == 'Show' ) 
	$compare = '<=';
else 
	$compare = 'NOT LIKE';
	
$getJobsArg = array( 'post_type'   => 'rsjp_job_postings',
					 'orderby'     => $order_by,
					 'order'       => $order,
					 'numberposts' => $limit,
					 'meta_query'  => array(
										  array( 'key' => 'rsjp_archive_posting',
											     'value' => 1,
											     'compare' => $compare
										   ) ) ); 
$jobs = get_posts( $getJobsArg );
?>

<div id="jobPostings">
	<?php 
	$jobs = new WP_Query( $getJobsArg );
	
	while ( $jobs->have_posts() ) : $jobs->the_post();
		?>
        <article id="post-<?php the_ID(); ?>" class="post-<?php the_ID(); ?> rsjp_job_postings type-rsjp_job_postings status-publish">
            <header class="entry-header">
                <h2 class="entry-title"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
            </header>
            <div class="entry-content">
                <i><?php the_date(); ?></i>
                <div class="category">
                	<?php _e( 'Category' ); ?>: 
					<?php
					$category = get_the_category();
					if( $category[0] ){
						echo '<a href="' . get_category_link( $category[0]->term_id ).'">' . $category[0]->cat_name . '</a>';
					}
					?>
                </div>
                <?php the_excerpt(); ?>
            </div>
        </article>
		<?php
	endwhile;
	
	// Reset Post Data
	wp_reset_postdata();
	
	
	?>
</div>