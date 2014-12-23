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

?>
<div class="panel">
	<header>
		<h3>Werkregistraties</h3>
	</header>

	<table class="table table-striped table-bordered panel">
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
