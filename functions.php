<?php

/**
 * Includes
 */
require_once get_stylesheet_directory() . '/includes/widgets/Orbis_Timesheets_Widget.php';

/**
 * Register our sidebars and widgetized areas.
 */
function orbis_pronamic_widgets_init() {
	register_widget( 'Orbis_Timesheets_Widget' );

	register_sidebar( array(
		'name'          => __( 'Shop Widget Area', 'orbis' ),
		'id'            => 'shop-widget',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>'
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
