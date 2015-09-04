<?php 
/**
 * Template Name: Subscriptions Twinfield
 */

get_header();

global $wpdb;
global $orbis_subscriptions_plugin;
$date = strtotime( filter_input( INPUT_GET, 'date', FILTER_SANITIZE_STRING ) );
if ( false === $date ) {
	$date = time();
}
// Interval
$interval = filter_input( INPUT_GET, 'interval', FILTER_SANITIZE_STRING );
$interval = empty( $interval ) ? 'Y' : $interval;

switch ( $interval ) {
	case 'M':
		$day_function = 'DAYOFMONTH';
		$join_condition = $wpdb->prepare( '( YEAR( invoice.start_date ) = %d AND MONTH( invoice.start_date ) = %d )', date( 'Y', $date ), date( 'n', $date ) );
		$where_condition = $wpdb->prepare( 'subscription.activation_date <= %s', date( 'Y-m-d', $date ) );
		break;
	case 'Y':
	default:
		$day_function = 'DAYOFYEAR';
		$join_condition = $wpdb->prepare( 'YEAR( invoice.start_date ) = %d', date( 'Y', $date ) );
		$where_condition = $wpdb->prepare( '
			(
				YEAR( subscription.activation_date ) <= %d
					AND 
				MONTH( subscription.activation_date ) < ( MONTH( NOW() ) + 2 )
			)',
			date( 'Y', $date )
		);
		break;
}
$query = $wpdb->prepare(
	"
		SELECT
			company.id AS company_id,
			company.name AS company_name,
			company.post_id AS company_post_id,
			product.name AS subscription_name,
			product.price,
			product.twinfield_article,
			product.interval,
			product.post_id AS product_post_id,
			subscription.id,
			subscription.type_id,
			subscription.post_id,
			subscription.name,
			subscription.activation_date,
			DAYOFYEAR( subscription.activation_date ) AS activation_dayofyear,
			invoice.invoice_number,
			invoice.start_date,
			(
				invoice.id IS NULL
					AND
				$day_function( subscription.activation_date ) < $day_function( NOW() )
			) AS too_late
		FROM
			$wpdb->orbis_subscriptions AS subscription
				LEFT JOIN
			$wpdb->orbis_companies AS company
					ON subscription.company_id = company.id
				LEFT JOIN
			$wpdb->orbis_subscription_products AS product
					ON subscription.type_id = product.id
				LEFT JOIN
			$wpdb->orbis_subscriptions_invoices AS invoice
					ON
						subscription.id = invoice.subscription_id
							AND
						$join_condition
		WHERE
			cancel_date IS NULL
				AND
			invoice_number IS NULL
				AND
			product.auto_renew
				AND
			product.interval = %s
				AND
			$where_condition
		ORDER BY
			DAYOFYEAR( subscription.activation_date )
		;
	",
	$interval
);

$subscriptions = $wpdb->get_results( $query );

$companies = array();

foreach ( $subscriptions as $subscription ) {
	$company_id = $subscription->company_id;

	if ( ! isset( $companies[ $company_id ] ) ) {
		$company = new stdClass();
		$company->id            = $subscription->company_id;
		$company->name          = $subscription->company_name;
		$company->post_id       = $subscription->company_post_id;
		$company->subscriptions = array();

		$companies[ $company_id ] = $company;
	}

	$companies[ $company_id ]->subscriptions[] = $subscription;
}

$date = array(
	'year'  => date( 'Y' ),
	'month' => date( 'm' )
);

$interval = 'Y';

foreach ( $companies as $company ) : ?>

	<?php

	$twinfield_customer = get_post_meta( $company->post_id, '_twinfield_customer_id', true );

	$sales_invoice = new Pronamic\WP\Twinfield\SalesInvoices\SalesInvoice();

	$header = $sales_invoice->get_header();

	$header->set_office( get_option( 'twinfield_default_office_code' ) );
	$header->set_type( get_option( 'twinfield_default_invoice_type' ) );
	$header->set_customer( $twinfield_customer );
	$header->set_status( Pronamic\WP\Twinfield\SalesInvoices\SalesInvoiceStatus::STATUS_CONCEPT );
	$header->set_payment_method( Pronamic\WP\Twinfield\PaymentMethods::BANK );
	$header->set_footer_text( sprintf(
		__( 'Invoice created by Orbis on %s.', 'orbis_pronamic' ),
		date_i18n( 'D j M Y @ H:i' )
	) );

	?>

	<form method="post" action="">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					<a href="<?php echo esc_attr( get_permalink( $company->post_id ) ); ?>"><?php echo esc_html( $company->name ); ?></a>
				</h3>
			</div>

			<div class="panel-body">
				<dl class="dl-horizontal">
					<dt>Customer</dt>
					<dd><?php echo esc_html( $twinfield_customer ); ?></dd>
				</dl>
			</div>

			<!-- Table -->
			<table class="table table-striped">
				<thead>
					<tr>
						<th scope="col">ID</th>
						<th scope="col">Abonnement</th>
						<th scope="col">Prijs</th>
						<th scope="col">Naam</th>
						<th scope="col">Startdatum</th>
						<th scope="col">Einddatum</th>
						<th scope="col">Twinfield</th>
					</tr>
				</thead>

				<tbody>
		
					<?php foreach ( $company->subscriptions as $i => $result ) : ?>

						<?php
						
						$name = 'subscriptions[%d][%s]';
						
						$date_start = new DateTime( $result->activation_date );
						$date_end   = new DateTime( $result->activation_date );
						$day   = $date_start->format( 'd' );
						$month = $date_start->format( 'm' );
						if ( $result->interval === 'Y' ) {
							$date_start->setDate( $date['year'], $month, $day );
							$date_end_timestamp = strtotime( $date['year'] . '-' . $month . '-' . $day . ' + 1 year' );
						} else if ( $result->interval === 'M' ) {
							$date_start->setDate( $date['year'], $date['month'], $day );
							$date_end_timestamp = strtotime( $date['year'] . '-' . $date['month'] . '-' . $day . ' + 1 month' );
						} else {
							$date_end_timestamp = strtotime( $date_string );
						}
						$date_end->setDate( date( 'Y', $date_end_timestamp ), date( 'm', $date_end_timestamp ), $day );

						$date_start_object = $date_start;
						$date_end_object   = $date_end;

						$date_start = $date_start->format( 'Y-m-d H:i:s' );
						$date_end   = $date_end->format( 'Y-m-d H:i:s' );

						$twinfield_article_code    = get_post_meta( $result->product_post_id, '_twinfield_article_code', true );
						$twinfield_subarticle_code = get_post_meta( $result->product_post_id, '_twinfield_subarticle_code', true );

						$line = $sales_invoice->new_line();
						$line->set_article( $twinfield_article_code );
						$line->set_subarticle( $twinfield_subarticle_code );
						$line->set_description( $result->subscription_name );
						$line->set_value_excl( (float) $result->price );
						$line->set_free_text_1( $result->name );
						$line->set_free_text_2( sprintf(
							'%s - %s',
							date_i18n( 'D j M Y', $date_start_object->getTimestamp() ),
							date_i18n( 'D j M Y', $date_end_object->getTimestamp() )
						) );
						$line->set_free_text_3( sprintf(
							__( 'Orbis ID: %s', 'orbis_pronamic' ),
							$result->id
						) );
						
						$classes = array();
						if ( $result->too_late ) {
							$classes[] = 'warning';
						}
						
						?>
						<tr class="<?php echo implode( ' ', $classes ); ?>">
							<?php 
							?>
							<td>
								<?php echo $result->id; ?>
							</td>
							<td>
								<a href="<?php echo get_permalink( $result->post_id ); ?>">
									<?php echo $result->subscription_name; ?>
								</a>
							</td>
							<td>
								<?php echo orbis_price( $result->price ); ?>
							</td>
							<td>
								<?php echo $result->name; ?>
							</td>
							<td>
								<?php echo $date_start; ?>
							</td>
							<td>
								<?php echo $date_end; ?>
							</td>
							<td>
								<?php

								$items = array();

								if ( ! empty( $twinfield_article_code ) ) {
									$items[] = sprintf(
										'<strong>%s</strong>: %s',
										esc_html__( 'Article', 'orbis_pronamic' ),
										esc_html( $twinfield_article_code )
									);
								}

								if ( ! empty( $twinfield_article_code ) ) {
									$items[] = sprintf(
										'<strong>%s</strong>: %s',
										esc_html__( 'Subarticle', 'orbis_pronamic' ),
										esc_html( $twinfield_subarticle_code )
									);
								}

								echo implode( '<br />', $items );

								?>
							</td>
						</tr>

					<?php endforeach; ?>
		
				</tbody>
			</table>

			<div class="panel-footer">
				<?php

				$posted_company = filter_input( INPUT_POST, 'company', FILTER_SANITIZE_STRING );

				if ( $company->id === $posted_company ) {
					$client = new Pronamic\WP\Twinfield\Client();

					$user         = get_option( 'twinfield_username' );
					$password     = get_option( 'twinfield_password' );
					$organisation = get_option( 'twinfield_organisation' );
					$office       = get_option( 'twinfield_default_office_code' );
					$type         = get_option( 'twinfield_default_invoice_type' );

					$credentials = new Pronamic\WP\Twinfield\Credentials( $user, $password, $organisation );

					$logon_response = $client->logon( $credentials );

					$session = $client->get_session( $logon_response );

					$xml_processor = new Pronamic\WP\Twinfield\XMLProcessor( $session );

					$service = new Pronamic\WP\Twinfield\SalesInvoices\SalesInvoiceService( $xml_processor );

					echo '<pre>';
					$service->insert_sales_invoice( $sales_invoice );
					echo '</pre>';
				}

				?>

				<input name="company" value="<?php echo esc_attr( $company->id ); ?>" type="text" />

				<p class="text-right">
					<button class="btn btn-default" type="submit">Factuur maken</button>
				</p>
			</div>
		</div>
	</form>

<?php endforeach; ?>

<?php get_footer(); ?>
