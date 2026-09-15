<?php
/**
 * Kundtema – Functions
 *
 * Plats för kundspecifika funktioner och anpassningar.
 * Skapat via CoTheme → Utseende → Skapa kundtema.
 *
 * @package CoTheme_Child
 */

defined('ABSPATH') || exit;

// Ladda föräldratemats stilar först, sedan kundtemats
add_action('wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'cotheme-child-style',
		get_stylesheet_uri(),
		['cotheme-style'],
		wp_get_theme()->get('Version')
	);
});
