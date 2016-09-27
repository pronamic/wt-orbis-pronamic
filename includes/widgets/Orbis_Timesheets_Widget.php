<?php

/**
 * Timesheets widget
 */
class Orbis_Timesheets_Widget extends WP_Widget {
	/**
	 * Register this widget
	 */
	public static function register() {
		register_widget( __CLASS__ );
	}

	////////////////////////////////////////////////////////////

	/**
	 * Constructs and initializes this widget
	 */
	public function Orbis_Timesheets_Widget() {
		parent::WP_Widget( 'orbis-timesheets', __( 'Orbis Timesheets', 'orbis_pronamic' ) );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		?>

		<?php echo $before_widget; ?>

		<?php if ( ! empty( $title ) ) : ?>

			<?php echo $before_title . $title . $after_title; ?>

		<?php endif; ?>

		<div class="content">
			<?php

			// Globals
			global $wpdb;

			// User
			$user = get_current_user_id();

			// Functions
			function orbis_format_timestamps( array $timestamps, $format ) {
				$dates = array();

				foreach( $timestamps as $key => $value ) {
					$dates[$key] = date( $format, $value );
				}

				return $dates;
			}

			// This week
			$week_this = array(
				'start_date' => strtotime( 'sunday this week -1 week' ),
				'end_date'   => strtotime( 'sunday this week' ),
			);

			// Startdate and enddate
			$start_date = $week_this['start_date'];
			$end_date = $week_this['end_date'];

			// Build query
			$query = 'WHERE 1 = 1';

			if ( $start_date ) {
				$query .= $wpdb->prepare( ' AND date >= %s', date( 'Y-m-d', $start_date ) );
			}

			if ( $end_date ) {
				$query .= $wpdb->prepare( ' AND date <= %s', date( 'Y-m-d', $end_date ) );
			}

			if ( $user ) {
				$query .= $wpdb->prepare( ' AND user_id = %d', $user );
			}

			$query .= ' ORDER BY date ASC';

			// Get results
			$query_budgets = $wpdb->prepare(
				"SELECT
					project.id,
					project.number_seconds - IFNULL( SUM( registration.number_seconds ), 0 ) AS seconds_available,
					project.invoicable 
				FROM
					$wpdb->orbis_projects AS project
						LEFT JOIN
					$wpdb->orbis_timesheets AS registration
							ON (
								project.id = registration.project_id
									AND
								registration.date <= %s
							)
				GROUP BY
					project.id
				",
				date( 'Y-m-d', $start_date )
			);

			$budgets = $wpdb->get_results( $query_budgets, OBJECT_K );

			$query_hours =  "
				SELECT
					hr.id AS registration_id,
					project.id AS project_id,
					project.name AS project_name,
					project.post_id AS project_post_id,
					client.id AS client_id,
					client.name AS client_name,
					client.post_id AS client_post_id,
					user.display_name AS user_name,
					hr.date AS date,
					hr.description AS description,
					hr.number_seconds AS number_seconds
				FROM
					$wpdb->orbis_timesheets AS hr
						LEFT JOIN
					$wpdb->orbis_companies AS client
							ON hr.company_id = client.id
						LEFT JOIN
					$wpdb->orbis_projects AS project
							ON hr.project_id = project.id
						LEFT JOIN
					$wpdb->users AS user
							ON hr.user_id = user.ID
				$query
			";

			$result = $wpdb->get_results( $query_hours );

			$total_seconds      = 0;
			$billable_seconds   = 0;
			$unbillable_seconds = 0;

			foreach ( $result as $row ) {
				$row->billable_seconds   = 0;
				$row->unbillable_seconds = 0;

				if ( isset( $budgets[$row->project_id] ) ) {
					$project = $budgets[$row->project_id];
	
					if ( $project->invoicable ) {
						if ( $row->number_seconds < $project->seconds_available ) {
							// 1800 seconds registred < 3600 seconds available
							$row->billable_seconds   = $row->number_seconds;
						} else {
							// 3600 seconds registred < 1800 seconds available
							$seconds_avilable        = max( 0, $project->seconds_available );

							$row->billable_seconds   = $seconds_avilable;
							$row->unbillable_seconds = $row->number_seconds - $seconds_avilable;
						}
					} else {
						$row->unbillable_seconds = $row->number_seconds;
					}

					$project->seconds_available -= $row->number_seconds;
				} else {
					$row->unbillable_seconds = $row->number_seconds;
				}

				$total_seconds      += $row->number_seconds;
				$billable_seconds   += $row->billable_seconds;
				$unbillable_seconds += $row->unbillable_seconds;
			}

			$unbillable_hours = $unbillable_seconds / 60 / 60;
			$billable_hours   = $billable_seconds / 60 / 60;
			$total_hours      = $total_seconds / 60 / 60;

			if ( $total_seconds > 0 ) {
				$total = $billable_seconds / $total_seconds  * 100;
			} else {
				$total = 0;
			}

			$amount = $billable_hours * 75;

			?>

			<div class="row">
				<div class="col-md-12">
					<?php

					if ( $total < 50 ) {
						$progress_bar = 'progress-danger';
					} elseif ( $total < 60 ) {
						$progress_bar = 'progress-warning';
					} else {
						$progress_bar = 'progress-success';
					}

					?>

					<p class="h1" style="margin-top: 0;"><?php echo round( $total ) . '%'; ?> <span style="font-size: 16px; font-weight: normal; color: #999;">of the hours are billable</span> </p>

					<progress class="progress progress-striped <?php echo $progress_bar; ?>" value="<?php echo round( $total ); ?>" max="100">
						<span class="sr-only"><?php echo round( $total ) . '%'; ?> Complete</span>
					</progress>
				</div>
			</div>

			<div class="row">
				<div class="col-md-4">
					<p class="alt" style="margin-bottom: 5px;"><?php _e( 'Total tracked hours', 'orbis_pronamic' ); ?></p>
					<p class="h3"><?php echo round( $total_hours, 2 ); ?></p>
				</div>

				<div class="col-md-4">
					<p class="alt" style="margin-bottom: 5px;"><?php _e( 'Billabale hours', 'orbis_pronamic' ); ?></p>
					<p class="h3"><?php echo round( $billable_hours, 2 ); ?></p>
				</div>

				<div class="col-md-4">
					<p class="alt" style="margin-bottom: 5px;"><?php _e( 'Unbillabale hours', 'orbis_pronamic' ); ?></p>
					<p class="h3"><?php echo round( $unbillable_hours, 2 ); ?></p>
				</div>
			</div>
		</div>

		<?php echo $after_widget; ?>
		
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = $new_instance['title'];

		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr($instance['title'] ) : '';

		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _e( 'Title:', 'orbis_pronamic' ); ?>
			</label>

			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		
		<?php
	}
}
