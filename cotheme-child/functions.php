<?php
/**
 * CoTheme Child - Functions
 *
 * Plats for kundspecifika funktioner och anpassningar.
 *
 * @package CoTheme_Child
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

// Ladda foraldra-temats stilar forst, sedan child-temats
add_action('wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'cotheme-child-style',
		get_stylesheet_uri(),
		['cotheme-style'],
		wp_get_theme()->get('Version')
	);
});
