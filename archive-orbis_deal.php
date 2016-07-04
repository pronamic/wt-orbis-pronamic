<?php get_header(); ?>

<header class="section-header clearfix">
	<a class="btn btn-primary pull-right" href="<?php echo esc_url( orbis_get_url_post_new() ); ?>">
		<span class="glyphicon glyphicon-plus"></span> <?php _e( 'Add deal', 'orbis' ); ?>
	</a>
</header>

<?php

/**
 * Date query
 */
$date_query = array();

$date = filter_input( INPUT_GET, 'date', FILTER_SANITIZE_STRING );

if ( ! empty( $date ) ) {
	$date  = explode( '-', $date );

	$year  = $date[0];
	$month = $date[1];
	$day   = $date[2];

	$date_query[] = array(
		'after'     => array(
			'year'  => $year,
			'month' => $month,
			'day'   => $day,
		),
		'inclusive' => true,
	);
} else {
	$date_query[] = array(
		'column' => 'post_date_gmt',
		'after'  => '1 year ago',
	);
}

/**
 * Pending deals
 */
$pending_deals_query = new WP_Query(
	array(
		'post_type'      => 'orbis_deal',
		'posts_per_page' => -1,
		'date_query'     => $date_query,
		'meta_query' => array(
			array(
				'key'     => '_orbis_deal_status',
				'value'   => 'pending',
				'compare' => 'LIKE',
			),
		),
	)
);

$pending_deals = $pending_deals_query->found_posts;

/**
 * Open amount
 */
$total_amount = '';

if ( $pending_deals_query->have_posts() ) {
	while ( $pending_deals_query->have_posts() ) { $pending_deals_query->the_post();
		$total_amount = $total_amount + get_post_meta( $post->ID, '_orbis_deal_price', true );
	}
}

/**
 * Won deals
 */
$won_deals_query = new WP_Query(
	array(
		'post_type'      => 'orbis_deal',
		'posts_per_page' => -1,
		'date_query'     => $date_query,
		'meta_query' => array(
			array(
				'key'     => '_orbis_deal_status',
				'value'   => 'won',
				'compare' => 'LIKE',
			),
		),
	)
);

$won_deals = $won_deals_query->found_posts;

/**
 * Lost deals
 */
$lost_deals_query = new WP_Query(
	array(
		'post_type'      => 'orbis_deal',
		'posts_per_page' => -1,
		'date_query'     => $date_query,
		'meta_query' => array(
			array(
				'key'     => '_orbis_deal_status',
				'value'   => 'lost',
				'compare' => 'LIKE',
			),
		),
	)
);

$lost_deals    = $lost_deals_query->found_posts;

/**
 * Total deals
 */
$total_deals = $pending_deals + $won_deals + $lost_deals;

$percentage = 0;

if ( $total_deals ) {
	$percentage = ( $won_deals / $total_deals ) * 100;
}

?>
<div class="row">
	<div class="col-md-12">
		<h1><?php echo round( $percentage ) . '%'; ?> <span style="font-size: 16px; font-weight: normal;">of the deals have been won</span> </h1>

		<div class="progress progress-striped active">
			<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo round( $total ); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round( $percentage ) . '%'; ?>;">
				<span class="sr-only"><?php echo round( $percentage ) . '%'; ?> Complete</span>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-3">
		<p><?php _e( 'Won deals', 'orbis_pronamic' ); ?></p>
		<h1><?php echo round( $won_deals, 2 ); ?></h1>
	</div>

	<div class="col-md-3">
		<p><?php _e( 'Lost deals', 'orbis_pronamic' ); ?></p>
		<h1><?php echo round( $lost_deals, 2 ); ?></h1>
	</div>

	<div class="col-md-3">
		<p><?php _e( 'Pending deals', 'orbis_pronamic' ); ?></p>
		<h1><?php echo round( $pending_deals, 2 ); ?></h1>
	</div>

	<div class="col-md-3">
		<p><?php _e( 'Total amount open', 'orbis_pronamic' ); ?></p>
		<h1><?php echo orbis_price( $total_amount ); ?></h1>
	</div>
</div>

<hr />

<div class="panel">
	<?php get_template_part( 'templates/search_form' ); ?>

	<?php if ( have_posts() ) : ?>

		<div class="table-responsive">
			<table class="table table-striped table-condense table-hover">
				<thead>
					<tr>
						<th><?php _e( 'Date', 'orbis' ); ?></th>
						<th><?php _e( 'Company', 'orbis' ); ?></th>
						<th><?php _e( 'Title'  , 'orbis' ); ?></th>
						<th><?php _e( 'Price'  , 'orbis' ); ?></th>
						<th><?php _e( 'Status' , 'orbis' ); ?></th>
						<th><?php _e( 'Author' , 'orbis' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php while ( have_posts() ) : the_post(); ?>

						<tr id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<td>
								<?php the_date(); ?>
							</td>
							<td>
								<?php orbis_deal_the_company_name(); ?>
							</td>
							<td>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>

								<?php if ( get_comments_number() != 0  ) : ?>

									<div class="comments-number">
										<span class="glyphicon glyphicon-comment"></span>
										<?php comments_number( '0', '1', '%' ); ?>
									</div>

								<?php endif; ?>
							</td>
							<td>
								<?php orbis_deal_the_price(); ?>
							</td>
							<td>
								<?php orbis_deal_the_status(); ?>
							</td>
							<td>
								<?php the_author(); ?>
							</td>
							<td>
								<div class="actions">
									<div class="nubbin">
										<?php orbis_edit_post_link(); ?>
									</div>
								</div>
							</td>
						</tr>
	
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>

	<?php else : ?>

		<div class="content">
			<p class="alt">
				<?php _e( 'No results found.', 'orbis' ); ?>
			</p>
		</div>

	<?php endif; ?>
</div>

<?php orbis_content_nav(); ?>

<?php get_footer(); ?>
