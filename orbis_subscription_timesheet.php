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

	<?php if ( empty( $results ) ) : ?>

		<div class="card-body">
			<p class="text-muted m-0">
				<?php _e( 'There are no time registrations for this subscription.', 'orbis_pronamic' ); ?>
			</p>
		</div>

	<?php else : ?>

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

	<?php endif; ?>

</div>

<?php

// Query
$query =  "
	SELECT
		MONTH( timesheet.date ) AS month,
		SUM( timesheet.number_seconds ) AS number_seconds
	FROM
		$wpdb->orbis_timesheets AS timesheet
			INNER JOIN
		$wpdb->orbis_subscriptions AS subscription
				ON timesheet.subscription_id = subscription.id
	WHERE 
		subscription.post_id = %d
			AND
		YEAR( timesheet.date ) = %d
	GROUP BY
		MONTH( timesheet.date )
	ORDER BY 
		MONTH( timesheet.date ) ASC
";

$year = \intval( date( 'Y' ) );

$query = $wpdb->prepare( $query, get_the_ID(), $year );

$data = $wpdb->get_results( $query );

if ( $results ) : ?>

	<div class="card mb-3">
		<div class="card-header">
			Werkregistraties per maand
		</div>

		<div class="table-responsive" id="orbis-subscription-timesheet-per-month">
			<table class="table table-striped table-bordered mb-0">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Month', 'orbis_pronamic' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Time', 'orbis_pronamic' ); ?></th>
					</tr>
				</thead>

				<tfoot>
					<tr>
						<th scope="row"><?php esc_html_e( 'Total', 'orbis_pronamic' ); ?></th>
						<td>
							<?php 

							$total = array_sum( wp_list_pluck( $data, 'number_seconds' ) );

							echo esc_html( orbis_time( $total ) ); 

							?>
						</td>
					</tr>
				</tfoot>

				<tbody>

					<?php foreach ( $data as $item ) : ?>

						<tr>
							<th scope="row">
								<?php 

								$date = new \Pronamic\WordPress\DateTime\DateTime();
								$date->setDate( $year, $item->month, 1 );

								echo esc_html( ucfirst( $date->format_i18n( 'F Y' ) ) );

								?>
							</th>
							<td>
								<?php echo esc_html( orbis_time( $item->number_seconds ) ); ?>
							</td>
						</tr>

					<?php endforeach; ?>

				</tbody>
			</table>
		</div>

		<div class="card-footer">
			<button type="button" class="btn btn-secondary btn-sm float-right orbis-copy" data-clipboard-target="#orbis-subscription-timesheet-per-month"><i class="fas fa-paste"></i> Kopieer HTML-tabel</button>

			<script src="https://unpkg.com/popper.js@1"></script>
			<script src="https://unpkg.com/tippy.js@5"></script>

			<script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.4/dist/clipboard.min.js" integrity="sha256-FiZwavyI2V6+EXO1U+xzLG3IKldpiTFf3153ea9zikQ=" crossorigin="anonymous"></script>

			<script type="text/javascript">
				var clipboard = new ClipboardJS( '.orbis-copy', {
					text: function( trigger ) {
						var selector = trigger.getAttribute( 'data-clipboard-target' );

						var element = document.querySelector( selector );

						return element.innerHTML;
					}
				} );

				clipboard.on( 'success', function( e ) {
					if ( ! e.trigger._tippy ) {
						tippy( e.trigger, {
				  			content: 'Gekopieerd',
				  			trigger: 'manual'
						} );
					}

					e.trigger._tippy.show();
				} );

				// Global config for all <button>s
				
			</script>
		</div>
	</div>

<?php endif; ?>

