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
}

add_action( 'widgets_init', 'orbis_pronamic_widgets_init' );