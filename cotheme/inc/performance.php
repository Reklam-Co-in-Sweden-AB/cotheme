<?php
/**
 * Prestandaoptimeringar — render blocking, bildoptimering och cachehantering
 *
 * @package CoTheme
 */

defined('ABSPATH') || exit;


// ==========================================================================
// Flytta jQuery till footer
// ==========================================================================

add_action('wp_enqueue_scripts', function () {
	// Flytta inte jQuery i BB-editorn (BB behöver jQuery i head)
	if (class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active()) {
		return;
	}

	if (!is_admin()) {
		wp_scripts()->add_data('jquery', 'group', 1);
		wp_scripts()->add_data('jquery-core', 'group', 1);
		wp_scripts()->add_data('jquery-migrate', 'group', 1);
	}
}, 9999);


// ==========================================================================
// Defer icke-kritisk CSS (animate.css, ikonbibliotek m.m.)
// ==========================================================================

add_filter('style_loader_tag', function (string $html, string $handle): string {
	// Defer inte i BB-editorn
	if (class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active()) {
		return $html;
	}

	// Enbart CSS som aldrig syns above-the-fold
	$defer_handles = [
		'jquery-bxslider',       // bxslider CSS
	];

	// Defer alla animate-relaterade stilar (enbart animationer, inte layout)
	if (strpos($handle, 'animate') !== false) {
		$defer_handles[] = $handle;
	}

	if (in_array($handle, $defer_handles, true)) {
		// Byt till asynkron laddning med fallback
		$html = str_replace(
			"media='all'",
			"media='print' onload=\"this.media='all'\"",
			$html
		);
	}

	return $html;
}, 10, 2);


// ==========================================================================
// Förhindra dubbeladdning av animate.css
// ==========================================================================

add_action('wp_enqueue_scripts', function () {
	if (class_exists('FLBuilderModel') && FLBuilderModel::is_builder_active()) {
		return;
	}

	// Hitta alla registrerade stilar som laddar animate.css
	$wp_styles    = wp_styles();
	$animate_found = [];

	foreach ($wp_styles->registered as $handle => $dep) {
		if (isset($dep->src) && strpos($dep->src, 'animate') !== false && strpos($dep->src, '.css') !== false) {
			$animate_found[$handle] = $dep->src;
		}
	}

	// Om fler än en animate-stil laddas, behåll minifierad version
	if (count($animate_found) > 1) {
		$keep = null;
		foreach ($animate_found as $handle => $src) {
			if (strpos($src, '.min.css') !== false) {
				$keep = $handle;
				break;
			}
		}
		// Om ingen minifierad hittades, behåll den första
		if (!$keep) {
			$keep = array_key_first($animate_found);
		}

		foreach ($animate_found as $handle => $src) {
			if ($handle !== $keep && wp_style_is($handle, 'enqueued')) {
				wp_dequeue_style($handle);
				wp_deregister_style($handle);
			}
		}
	}
}, 100);


// ==========================================================================
// Lägg till font-display: swap på Adobe Fonts (TypeKit)
// ==========================================================================

add_filter('style_loader_tag', function (string $html, string $handle): string {
	// Lägg till font-display parameter för TypeKit
	if (strpos($handle, 'adobe-fonts') !== false || strpos($handle, 'typekit') !== false) {
		// TypeKit stödjer font-display via URL-parameter
		if (strpos($html, 'use.typekit.net') !== false && strpos($html, 'font-display') === false) {
			$html = str_replace('.css', '.css?font-display=swap', $html);
			// Undvik dubbla ?
			$html = str_replace('.css?font-display=swap?', '.css?font-display=swap&', $html);
		}
	}

	return $html;
}, 10, 2);


// ==========================================================================
// Logotyp — aldrig lazy-laddad
// ==========================================================================
//
// Logotypen får varken fetchpriority="high" eller preload. På BB-sidor är
// hero-bilden nästan alltid LCP-elementet, och en högprioriterad logotyp
// konkurrerar då om bandbredd med den. Webbläsaren hittar logotypen tidigt
// i headern ändå.

add_filter('get_custom_logo', function (string $html): string {
	// Ta bort loading="lazy" från logotypen (den är alltid above-the-fold)
	return str_replace(' loading="lazy"', '', $html);
});


// ==========================================================================
// Sätt width/height på SVG-bilder som saknar det
// ==========================================================================

add_filter('wp_get_attachment_image_attributes', function (array $attr, WP_Post $attachment): array {
	$mime = get_post_mime_type($attachment->ID);

	if ($mime === 'image/svg+xml') {
		// Om width eller height saknas, försök hämta från filens metadata
		if (empty($attr['width']) || empty($attr['height'])) {
			$file = get_attached_file($attachment->ID);
			if ($file && file_exists($file)) {
				$svg_content = file_get_contents($file);
				if ($svg_content) {
					// Försök hämta viewBox
					if (preg_match('/viewBox=["\'](\d+(?:\.\d+)?)\s+(\d+(?:\.\d+)?)\s+(\d+(?:\.\d+)?)\s+(\d+(?:\.\d+)?)/', $svg_content, $m)) {
						$attr['width']  = (int) round((float) $m[3]);
						$attr['height'] = (int) round((float) $m[4]);
					}
					// Eller explicit width/height i SVG-taggen
					if (empty($attr['width']) && preg_match('/\bwidth=["\'](\d+)/', $svg_content, $m)) {
						$attr['width'] = (int) $m[1];
					}
					if (empty($attr['height']) && preg_match('/\bheight=["\'](\d+)/', $svg_content, $m)) {
						$attr['height'] = (int) $m[1];
					}
				}
			}
		}
	}

	return $attr;
}, 10, 2);


