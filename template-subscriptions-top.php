<?php 
/**
 * Template Name: Subscription Top
 */

get_header();

// Globals
global $wpdb;

$query =  "
	SELECT
		subscription.id AS subscription_id,
		subscription.post_id AS subscription_post_id,
		company.name AS company_name,
		company.post_id AS company_post_id,
		product.name AS product_name,
		subscription.name AS subscription_name,
		SUM( timesheet.number_seconds ) AS subscription_seconds
	FROM
		$wpdb->orbis_subscriptions AS subscription
			LEFT JOIN
		$wpdb->orbis_subscription_products AS product
				ON subscription.type_id = product.id
			LEFT JOIN
		$wpdb->orbis_timesheets AS timesheet
				ON subscription.id = timesheet.subscription_id
			LEFT JOIN
		$wpdb->orbis_companies AS company
				ON subscription.company_id = company.id
	WHERE subscription.cancel_date IS NULL
	GROUP BY
		subscription.id
	ORDER BY
		subscription_seconds DESC
	LIMIT
		0, 50
	;
";

$results = $wpdb->get_results( $query );

?>

<h1>Abonnement uren</h1>

<hr />

<table class="table table-striped table-bordered panel">
	<thead>
		<tr>
			<th><?php _e( 'Company', 'orbis_pronamic' ); ?></th>
			<th><?php _e( 'Subscription', 'orbis_pronamic' ); ?></th>
			<th><?php _e( 'Name', 'orbis_pronamic' ); ?></th>
			<th><?php _e( 'Time', 'orbis_pronamic' ); ?></th>
		</tr>
	</thead>
	<tbody>

		<?php foreach( $results as $row ) : ?>
		
			<tr>
				<td>
					<a href="<?php echo add_query_arg( 'p', $row->company_post_id, home_url( '/' ) ); ?>">
						<?php echo $row->company_name; ?>
					</a>
				</td>
				<td>
					<?php echo $row->product_name; ?>
				</td>
				<td>
					<a href="<?php echo add_query_arg( 'p', $row->subscription_post_id, home_url( '/' ) ); ?>">
						<?php echo $row->subscription_name; ?>
					</a>
				</td>
				<td>
					<?php echo orbis_time( $row->subscription_seconds ); ?>
				</td>
			</tr>

		<?php endforeach; ?>
	</tbody>
</table>

<?php get_footer(); ?>
