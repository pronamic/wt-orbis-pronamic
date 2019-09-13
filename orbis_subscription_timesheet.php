<?php 

// Globals
global $wpdb;

// Query
$query =  "
	SELECT
		timesheet.id AS entry_id,
		timesheet.date AS entry_date,
		timesheet.description AS entry_description,
		timesheet.number_seconds AS entry_number_seoncds,
		user.display_name AS user_display_name
	FROM
		$wpdb->orbis_timesheets AS timesheet
			LEFT JOIN
		$wpdb->orbis_subscriptions AS subscription
				ON timesheet.subscription_id = subscription.id
			LEFT JOIN
		$wpdb->users AS user
				ON timesheet.user_id = user.ID
	WHERE 
		subscription.post_id = %d
	ORDER BY 
		timesheet.date ASC
";

$query = $wpdb->prepare( $query, get_the_ID() );

$results = $wpdb->get_results( $query );

$note = get_option( 'orbis_timesheets_note' );

?>
<div class="card mb-3">
	<div class="card-header">Werkregistraties</div>

	<?php if ( $note ) : ?>

		<div class="card-body">
			<div class="alert alert-warning mb-0" role="alert">
				<i class="fas fa-exclamation-triangle"></i> <?php echo wp_kses_post( $note ); ?>
			</div>
		</div>

	<?php endif; ?>

	<div class="table-responsive">
		<table class="table table-striped table-bordered mb-0">
			<thead>
				<tr>
					<th><?php _e( 'Date', 'orbis_pronamic' ); ?></th>
					<th><?php _e( 'User', 'orbis_pronamic' ); ?></th>
					<th><?php _e( 'Description', 'orbis_pronamic' ); ?></th>
					<th><?php _e( 'Time', 'orbis_pronamic' ); ?></th>
				</tr>
			</thead>
			<tbody>

				<?php foreach( $results as $row ) : ?>

					<tr>
						<td>
							<?php echo $row->entry_date; ?>
						</td>
						<td>
							<?php echo $row->user_display_name; ?>
						</td>
						<td>
							<?php echo $row->entry_description; ?>
						</td>
						<td>
							<?php echo orbis_time( $row->entry_number_seoncds ); ?>
						</td>
					</tr>

				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
