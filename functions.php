<?php

/**
 * Includes
 */
require_once get_stylesheet_directory() . '/includes/widgets/Orbis_Timesheets_Widget.php';

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function orbis_pronamic_setup() {
	/* Make theme available for translation */
	load_theme_textdomain( 'orbis_pronamic', get_stylesheet_directory() . '/languages' );
}

add_action( 'after_setup_theme', 'orbis_pronamic_setup' );

/**
 * Register our sidebars and widgetized areas.
 */
function orbis_pronamic_widgets_init() {
	register_widget( 'Orbis_Timesheets_Widget' );

	register_sidebar( array(
		'name'          => __( 'Shop Widget Area', 'orbis_pronamic' ),
		'id'            => 'shop-widget',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}

add_action( 'widgets_init', 'orbis_pronamic_widgets_init' );

/**
 * Woocommerce
 */

/* Number products */
function orbis_number_products() {
	return 36;
}

add_filter( 'loop_shop_per_page', 'orbis_number_products', 20 );

/* Number columns */
function orbis_products_per_row() {
	return 4;
}

add_filter( 'loop_shop_columns', 'orbis_products_per_row', 20 );

/* Thumbnail */
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );

/* Grid */
function orbis_woocommerce_grid() {
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	add_action( 'woocommerce_before_main_content', 'woocommerce_get_sidebar', 20 );
}

add_action( 'template_redirect', 'orbis_woocommerce_grid' );

/**
 * Checklist
 */
function orbis_checklist_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'number' => 50,
		'cols'   => 3,
	), $atts );

	ob_start();

	$categories = get_terms( 'orbis_checklist_category', array(
		'orderby'    => 'count',
		'hide_empty' => 0,
	) );

	if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :

		foreach ( $categories as $category ) :

			$query = new WP_Query( array(
				'post_type'      => 'orbis_checklist_item',
				'posts_per_page' => $atts['number'],
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => 'orbis_checklist_category',
						'field'    => 'slug',
						'terms'    => $category->slug,
					),
				),
			) );

			if ( $query->have_posts() ) : ?>

				<h4><?php echo esc_html( $category->name ); ?></h4>

				<div class="panel-group" id="<?php echo esc_attr( $category->slug ); ?>" role="tablist" aria-multiselectable="true">
					<?php
					while ( $query->have_posts() ) :
						$query->the_post();
					?>

						<div class="panel panel-default">
							<div class="panel-heading" role="tab" id="heading-<?php the_ID(); ?>">
								<a class="collapsed" class="collapse" role="button" data-toggle="collapse" data-parent="#<?php echo esc_attr( $category->slug ); ?>" href="#collapse-<?php the_ID(); ?>" aria-expanded="true" aria-controls="collapse-<?php the_ID(); ?>">
									<?php the_title(); ?>
								</a>
							</div>

							<div id="collapse-<?php the_ID(); ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-<?php the_ID(); ?>">
								<div class="panel-body">
									<?php the_content(); ?>
								</div>
							</div>
						</div>

					<?php endwhile; ?>
				</div>

			<?php
				wp_reset_postdata();
			endif;
			?>

		<?php endforeach; ?>

	<?php endif; ?>

	<?php

	$output = ob_get_contents();

	ob_end_clean();

	return $output;
}

add_shortcode( 'checklist', 'orbis_checklist_shortcode' );
