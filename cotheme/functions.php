<?php
/**
 * CoTheme Theme Functions
 *
 * @package CoTheme
 * @version 1.2.0
 */

defined('ABSPATH') || exit;

define('COTHEME_VERSION', '1.4.0');
define('COTHEME_DIR', get_template_directory());
define('COTHEME_URI', get_template_directory_uri());

// Inkludera delfiler
require_once COTHEME_DIR . '/inc/fonts.php';
require_once COTHEME_DIR . '/inc/customizer.php';
require_once COTHEME_DIR . '/inc/custom-fonts-admin.php';
require_once COTHEME_DIR . '/inc/styleguide.php';
require_once COTHEME_DIR . '/inc/performance.php';
require_once COTHEME_DIR . '/inc/page-layout.php';
require_once COTHEME_DIR . '/inc/updater.php';
require_once COTHEME_DIR . '/inc/child-theme-creator.php';


// ==========================================================================
// Theme Setup
// ==========================================================================

add_action('after_setup_theme', function () {
	// Översättningar. Temats strängar är skrivna på svenska; andra språk
	// ligger i languages/ (cotheme-nb_NO för norska sajter, fler vid behov).
	// Flerspråksplugin (WPML/Polylang) byter locale per språk, och då plockar
	// WordPress rätt fil här. Utan den här raden når inget språkval
	// strängarna — "Hoppa till innehåll" stod kvar på en nb_NO-sajt.
	load_theme_textdomain('cotheme', get_template_directory() . '/languages');

	// Beaver Builder stöd
	add_theme_support('fl-theme-builder');
	add_theme_support('fl-theme-builder-headers');
	add_theme_support('fl-theme-builder-footers');
	add_theme_support('fl-theme-builder-parts');

	// WordPress grundfunktioner
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	]);
	add_theme_support('custom-logo');
	add_theme_support('align-wide');
	add_theme_support('responsive-embeds');
	add_theme_support('editor-styles');
	add_theme_support('wp-block-styles');

	// Menyer
	register_nav_menus([
		'primary'   => __('Huvudmeny', 'cotheme'),
		'footer'    => __('Footermeny', 'cotheme'),
	]);

	// Bildstorlekar
	set_post_thumbnail_size(1200, 630, true);
});


// ==========================================================================
// Enqueue Styles & Scripts
// ==========================================================================

add_action('wp_enqueue_scripts', function () {
	wp_enqueue_style('cotheme-style', COTHEME_URI . '/style.css', [], COTHEME_VERSION);

	// Google Fonts
	$google_fonts_url = cotheme_build_google_fonts_url();
	if ($google_fonts_url) {
		wp_enqueue_style('cotheme-google-fonts', $google_fonts_url, [], null);
	}

	// Adobe Fonts
	$adobe_fonts_url = cotheme_get_adobe_fonts_url();
	if ($adobe_fonts_url) {
		wp_enqueue_style('cotheme-adobe-fonts', $adobe_fonts_url, [], null);
	}
});


// ==========================================================================
// Resurshintar — preconnect och preload för typsnitt
// ==========================================================================

add_action('wp_head', function () {
	$heading_source = get_theme_mod('cotheme_font_heading_source', 'system');
	$body_source    = get_theme_mod('cotheme_font_body_source', 'system');

	// Google Fonts preconnect
	if ($heading_source === 'google' || $body_source === 'google') {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	}

	// Adobe Fonts preconnect
	if ($heading_source === 'adobe' || $body_source === 'adobe') {
		$project_id = get_theme_mod('cotheme_adobe_fonts_id', '');
		if (!empty($project_id)) {
			echo '<link rel="preconnect" href="https://use.typekit.net" crossorigin>' . "\n";
		}
	}

	// Preload kritiska egna typsnitt (WOFF2)
	$custom_fonts = cotheme_get_custom_fonts();
	foreach ($custom_fonts as $variants) {
		foreach ($variants as $variant) {
			$url = $variant['url'] ?? '';
			if (!empty($url) && pathinfo($url, PATHINFO_EXTENSION) === 'woff2') {
				echo '<link rel="preload" href="' . esc_url($url) . '" as="font" type="font/woff2" crossorigin>' . "\n";
			}
		}
	}
}, 1);


// ==========================================================================
// CSS Custom Properties och @font-face (output i <head>)
// ==========================================================================

/**
 * Bygg den kombinerade CSS-strängen för <head> (font-face + custom properties)
 */
function cotheme_build_head_css(): string {
	$css = '';

	// @font-face för egna typsnitt
	$custom_font_css = cotheme_custom_fonts_css();
	if ($custom_font_css) {
		$css .= wp_strip_all_tags($custom_font_css) . "\n";
	}

	$font_heading   = cotheme_get_font_family('heading');
	$font_body      = cotheme_get_font_family('body');
	$heading_weight = get_theme_mod('cotheme_font_heading_weight', '700');
	$body_weight    = get_theme_mod('cotheme_font_body_weight', '400');
	$container      = absint(get_theme_mod('cotheme_container_width', 1200));

	// Hämta tilldelade färger
	$primary       = cotheme_get_assigned_color('primary', '#0066cc');
	$primary_hover = cotheme_get_assigned_color('primary_hover', '#004999');
	$secondary     = cotheme_get_assigned_color('secondary', '#ff6600');
	$quote         = cotheme_get_assigned_color('quote', $secondary);
	$heading       = cotheme_get_assigned_color('heading', '#222222');
	$text          = cotheme_get_assigned_color('text', '#333333');
	$text_light    = cotheme_get_assigned_color('text_light', '#666666');
	$bg            = cotheme_get_assigned_color('bg', '#ffffff');
	$border        = cotheme_get_assigned_color('border', '#dddddd');
	$focus         = cotheme_get_assigned_color('focus', $primary);

	$css .= ":root {\n";
	$css .= "\t--cotheme-primary: " . esc_attr($primary) . ";\n";
	$css .= "\t--cotheme-primary-hover: " . esc_attr($primary_hover) . ";\n";
	$css .= "\t--cotheme-secondary: " . esc_attr($secondary) . ";\n";
	$css .= "\t--cotheme-quote: " . esc_attr($quote) . ";\n";
	$css .= "\t--cotheme-text: " . esc_attr($text) . ";\n";
	$css .= "\t--cotheme-text-light: " . esc_attr($text_light) . ";\n";
	$css .= "\t--cotheme-heading: " . esc_attr($heading) . ";\n";
	$css .= "\t--cotheme-bg: " . esc_attr($bg) . ";\n";
	$css .= "\t--cotheme-border: " . esc_attr($border) . ";\n";
	$css .= "\t--cotheme-focus: " . esc_attr($focus) . ";\n";
	// Saniterat via cotheme_sanitize_font_family vid lagring; wp_kses bevarar citattecken för CSS
	$css .= "\t--cotheme-font-heading: " . wp_kses($font_heading, []) . ";\n";
	$css .= "\t--cotheme-font-body: " . wp_kses($font_body, []) . ";\n";
	$css .= "\t--cotheme-font-heading-weight: " . esc_attr($heading_weight) . ";\n";
	$css .= "\t--cotheme-font-body-weight: " . esc_attr($body_weight) . ";\n";
	$css .= "\t--cotheme-container-width: " . esc_attr($container) . "px;\n";

	// Exportera alla varumärkesfärger som CSS-variabler
	$count = absint(get_theme_mod('cotheme_brand_color_count', 3));
	for ($i = 1; $i <= $count; $i++) {
		$c = get_theme_mod("cotheme_brand_color_{$i}", '#cccccc');
		$css .= "\t--cotheme-brand-" . (int) $i . ": " . esc_attr($c) . ";\n";
	}

	// Typsnittsstorlekar — brödtext
	$body_size_desktop = absint(get_theme_mod('cotheme_body_size_desktop', 16));
	$body_size_tablet  = absint(get_theme_mod('cotheme_body_size_tablet', 16));
	$body_size_mobile  = absint(get_theme_mod('cotheme_body_size_mobile', 15));
	$body_line_height  = (float) get_theme_mod('cotheme_body_line_height', '1.6');

	$css .= "\t--cotheme-body-size: {$body_size_desktop}px;\n";
	$css .= "\t--cotheme-body-line-height: {$body_line_height};\n";

	// Typsnittsstorlekar — rubriker (desktop)
	$heading_sizes = [
		'h1' => absint(get_theme_mod('cotheme_h1_size_desktop', 44)),
		'h2' => absint(get_theme_mod('cotheme_h2_size_desktop', 36)),
		'h3' => absint(get_theme_mod('cotheme_h3_size_desktop', 28)),
		'h4' => absint(get_theme_mod('cotheme_h4_size_desktop', 22)),
		'h5' => absint(get_theme_mod('cotheme_h5_size_desktop', 18)),
		'h6' => absint(get_theme_mod('cotheme_h6_size_desktop', 16)),
	];

	foreach ($heading_sizes as $tag => $size) {
		$css .= "\t--cotheme-{$tag}-size: {$size}px;\n";
	}

	$css .= "}\n";

	// Grundstilar
	$css .= "body { font-size: var(--cotheme-body-size); font-weight: var(--cotheme-font-body-weight); line-height: var(--cotheme-body-line-height); }\n";
	$css .= "h1, h2, h3, h4, h5, h6 { font-weight: var(--cotheme-font-heading-weight); }\n";
	$css .= "h1 { font-size: var(--cotheme-h1-size); }\n";
	$css .= "h2 { font-size: var(--cotheme-h2-size); }\n";
	$css .= "h3 { font-size: var(--cotheme-h3-size); }\n";
	$css .= "h4 { font-size: var(--cotheme-h4-size); }\n";
	$css .= "h5 { font-size: var(--cotheme-h5-size); }\n";
	$css .= "h6 { font-size: var(--cotheme-h6-size); }\n";

	// Tablet (max-width: 1024px)
	$tablet_scale = absint(get_theme_mod('cotheme_heading_scale_tablet', 85));
	$css .= "@media (max-width: 1024px) {\n";
	$css .= "\t:root {\n";
	$css .= "\t\t--cotheme-body-size: {$body_size_tablet}px;\n";
	foreach ($heading_sizes as $tag => $size) {
		$scaled = round($size * $tablet_scale / 100);
		$css .= "\t\t--cotheme-{$tag}-size: {$scaled}px;\n";
	}
	$css .= "\t}\n";
	$css .= "}\n";

	// Mobil (max-width: 768px)
	$mobile_scale = absint(get_theme_mod('cotheme_heading_scale_mobile', 75));
	$css .= "@media (max-width: 768px) {\n";
	$css .= "\t:root {\n";
	$css .= "\t\t--cotheme-body-size: {$body_size_mobile}px;\n";
	foreach ($heading_sizes as $tag => $size) {
		$scaled = round($size * $mobile_scale / 100);
		$css .= "\t\t--cotheme-{$tag}-size: {$scaled}px;\n";
	}
	$css .= "\t}\n";
	$css .= "}\n";

	return $css;
}

add_action('wp_head', function () {
	// Använd transient-cache utanför Customizer-förhandsgranskning
	$css = false;
	if (!is_customize_preview()) {
		$css = get_transient('cotheme_head_css');
	}

	if ($css === false) {
		$css = cotheme_build_head_css();
		if (!is_customize_preview()) {
			set_transient('cotheme_head_css', $css, DAY_IN_SECONDS);
		}
	}

	echo '<style id="cotheme-custom-properties">' . "\n" . $css . '</style>' . "\n";
}, 5);

// Invalidera transient-cache vid Customizer-sparning
add_action('customize_save_after', function () {
	delete_transient('cotheme_head_css');
});


// ==========================================================================
// Beaver Builder – Integration
// ==========================================================================

// Registrera alla varumärkesfärger i BB:s färgväljare
add_filter('fl_builder_color_presets', function ($presets) {
	$count = absint(get_theme_mod('cotheme_brand_color_count', 3));
	$brand = [];

	for ($i = 1; $i <= $count; $i++) {
		$color = get_theme_mod("cotheme_brand_color_{$i}", '');
		if ($color) {
			$brand[] = str_replace('#', '', $color);
		}
	}

	return array_merge($brand, $presets);
});

// Registrera egna typsnitt, Adobe Fonts och Google Fonts i BB:s typsnittsväljare
// Prioritet 99 säkerställer att vår registrering inte skrivs över
add_filter('fl_builder_font_families_system', function ($fonts) {
	$custom_fonts = cotheme_get_bb_font_data();
	foreach ($custom_fonts as $name => $data) {
		$fonts[$name] = $data;
	}
	return $fonts;
}, 99);

// Ladda Adobe Fonts och egna typsnitt i BB:s editor (iframe)
add_action('wp_enqueue_scripts', function () {
	if (!class_exists('FLBuilderModel') || !FLBuilderModel::is_builder_active()) {
		return;
	}

	// Adobe Fonts i editorn
	$adobe_url = cotheme_get_adobe_fonts_url();
	if ($adobe_url) {
		wp_enqueue_style('cotheme-adobe-fonts-bb', $adobe_url, [], null);
	}

	// Egna typsnitt @font-face i editorn
	$custom_css = cotheme_custom_fonts_css();
	if ($custom_css) {
		wp_add_inline_style('cotheme-style', wp_strip_all_tags($custom_css));
	}
}, 20);

/**
 * Bridge mot WP Font Library (introducerad i WP 6.5, utökad till alla teman i WP 7.0).
 *
 * Läser fontfamiljer som användaren installerat via editorns Fontbibliotek
 * (custom post types wp_font_family + wp_font_face) och returnerar dem i samma
 * format som cotheme_get_bb_font_data() använder. Detta gör att fonter som
 * installerats den "moderna" WP-vägen blir tillgängliga i Beaver Builders
 * typsnittsväljare utan att användaren behöver dubbel-administrera fonter.
 *
 * WP outputtar själv @font-face-CSS för dessa fonter på frontend i WP 7.0+,
 * så vi behöver bara registrera namn och vikter mot BB.
 *
 * Filter cotheme_disable_font_library_bridge → __return_true för att stänga av.
 *
 * @return array{string: array{fallback: string, weights: array<string>}}
 */
function cotheme_get_font_library_fonts(): array {
	static $cache = null;
	if ($cache !== null) {
		return $cache;
	}
	$cache = [];

	if (apply_filters('cotheme_disable_font_library_bridge', false)) {
		return $cache;
	}

	// Font Library-post types finns från WP 6.5
	if (!post_type_exists('wp_font_family')) {
		return $cache;
	}

	$families = get_posts([
		'post_type'      => 'wp_font_family',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	]);

	foreach ($families as $family) {
		// Font-data ligger som theme.json-formaterad JSON i post_content
		$data = json_decode($family->post_content, true);
		if (empty($data) || empty($data['fontFamily'])) {
			continue;
		}

		// fontFamily kan vara t.ex. "Inter, sans-serif" — vi vill ha rena namnet
		$first = trim(explode(',', $data['fontFamily'])[0]);
		$name  = trim($first, "\"' ");
		if ($name === '') {
			continue;
		}

		// Hämta varianter (font faces) som child-posts
		$faces = get_posts([
			'post_type'      => 'wp_font_face',
			'post_parent'    => $family->ID,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		]);

		$weights = [];
		foreach ($faces as $face) {
			$face_data = json_decode($face->post_content, true);
			if (!empty($face_data['fontWeight'])) {
				// fontWeight kan vara "400", "400 700" (range) eller siffra
				foreach (preg_split('/\s+/', (string) $face_data['fontWeight']) as $w) {
					if (ctype_digit($w)) {
						$weights[] = $w;
					}
				}
			}
		}
		$weights = array_values(array_unique($weights));
		if (empty($weights)) {
			$weights = ['400'];
		}

		$cache[$name] = [
			'fallback' => 'sans-serif',
			'weights'  => $weights,
		];
	}

	return $cache;
}

/**
 * Bygg fontdata-array för BB-integration (används av både PHP-filter och JS-patch)
 */
function cotheme_get_bb_font_data(): array {
	$fonts_data = [];

	// Egna uppladdade typsnitt
	$custom_fonts = cotheme_get_custom_fonts();
	foreach ($custom_fonts as $name => $variants) {
		$weights = array_values(array_unique(array_column($variants, 'weight')));
		if (empty($weights)) {
			$weights = ['400'];
		}
		$fonts_data[$name] = [
			'fallback' => 'sans-serif',
			'weights'  => $weights,
		];
	}

	// Adobe Fonts
	$adobe_list = get_theme_mod('cotheme_adobe_fonts_list', '');
	if (!empty($adobe_list)) {
		$adobe_fonts = array_map('trim', explode(',', $adobe_list));
		foreach ($adobe_fonts as $font) {
			if (!empty($font)) {
				$fonts_data[$font] = [
					'fallback' => 'sans-serif',
					'weights'  => ['100', '200', '300', '400', '500', '600', '700', '800', '900'],
				];
			}
		}
	}

	// Google Fonts från Customizern
	foreach (['heading', 'body'] as $ctx) {
		if (get_theme_mod("cotheme_font_{$ctx}_source", 'system') === 'google') {
			$gfont = get_theme_mod("cotheme_font_{$ctx}_google", '');
			if (!empty($gfont) && !isset($fonts_data[$gfont])) {
				$fonts_data[$gfont] = [
					'fallback' => 'sans-serif',
					'weights'  => ['300', '400', '500', '600', '700'],
				];
			}
		}
	}

	// Fonter installerade via WP:s Fontbibliotek (WP 6.5+, alla teman i WP 7.0+).
	// Egna keys från CoTheme:s admin-sida vinner vid namnkollision för att inte
	// trampa på explicit konfiguration.
	foreach (cotheme_get_font_library_fonts() as $name => $data) {
		if (!isset($fonts_data[$name])) {
			$fonts_data[$name] = $data;
		}
	}

	return $fonts_data;
}

// Patcha BB:s JavaScript-konfiguration med fontdata och vikter
// Körs via wp_footer så att FLBuilderConfig redan är definierad
add_action('wp_footer', function () {
	if (!class_exists('FLBuilderModel') || !FLBuilderModel::is_builder_active()) {
		return;
	}

	$fonts_data = cotheme_get_bb_font_data();
	if (empty($fonts_data)) {
		return;
	}
	?>
	<script id="cotheme-bb-fonts">
	(function() {
		var fonts = <?php echo wp_json_encode($fonts_data); ?>;

		function patchFonts() {
			if (typeof FLBuilderConfig === 'undefined') return false;
			if (!FLBuilderConfig.fonts) return false;
			if (!FLBuilderConfig.fonts.system) FLBuilderConfig.fonts.system = {};

			for (var name in fonts) {
				FLBuilderConfig.fonts.system[name] = fonts[name];
			}
			return true;
		}

		// Kör direkt om FLBuilderConfig redan finns
		if (!patchFonts()) {
			// Annars vänta på DOMContentLoaded
			document.addEventListener('DOMContentLoaded', patchFonts);
		}
	})();
	</script>
	<?php
}, 5);

// Ladda Adobe Fonts i admin enbart på relevanta sidor (Customizer, BB-editor)
add_action('admin_enqueue_scripts', function ($hook) {
	if ($hook !== 'customize.php' && $hook !== 'post.php' && $hook !== 'post-new.php') {
		return;
	}
	$adobe_url = cotheme_get_adobe_fonts_url();
	if ($adobe_url) {
		wp_enqueue_style('cotheme-adobe-fonts-admin', $adobe_url, [], null);
	}
});


// ==========================================================================
// Block Editor / Gutenberg – Fontstöd i editor-iframen
// ==========================================================================
//
// Sedan WP 5.9/6.0 renderar Gutenberg inläggsinnehåll i en <iframe>, vilket
// gör att vanliga admin-enqueue inte når in i iframens dokument. Filtret
// nedan injicerar samma typsnitts-CSS som frontend direkt i iframen så att
// rubriker och brödtext får rätt typsnitt redan vid redigering:
//   - Google Fonts via @import
//   - Adobe Fonts via @import
//   - CSS-variabler (--cotheme-font-*, --cotheme-h*-size, m.fl.)
//   - @font-face-deklarationer för egna uppladdade typsnitt
//
// @import måste ligga först i CSS-strängen för att webbläsaren ska respektera
// dem — därför byggs strängen i den ordningen.

add_filter('block_editor_settings_all', function (array $settings, $context): array {
	$css_parts = [];

	// Google Fonts via @import
	$google_fonts_url = cotheme_build_google_fonts_url();
	if ($google_fonts_url) {
		$css_parts[] = "@import url('" . esc_url_raw($google_fonts_url) . "');";
	}

	// Adobe Fonts via @import
	$adobe_fonts_url = cotheme_get_adobe_fonts_url();
	if ($adobe_fonts_url) {
		$css_parts[] = "@import url('" . esc_url_raw($adobe_fonts_url) . "');";
	}

	// CSS-variabler och @font-face för egna typsnitt
	$head_css = cotheme_build_head_css();
	if (!empty($head_css)) {
		$css_parts[] = $head_css;
	}

	if (!empty($css_parts)) {
		if (!isset($settings['styles']) || !is_array($settings['styles'])) {
			$settings['styles'] = [];
		}
		$settings['styles'][] = ['css' => implode("\n", $css_parts)];
	}

	return $settings;
}, 10, 2);


// ==========================================================================
// Beaver Themer – Fullständig integration
// ==========================================================================

// Registrera alla hook-positioner för Themer
add_filter('fl_theme_builder_part_hooks', function () {
	return [
		[
			'label' => 'Header',
			'hooks' => [
				'cotheme_header' => 'Header',
			],
		],
		[
			'label' => 'Footer',
			'hooks' => [
				'cotheme_footer' => 'Footer',
			],
		],
		[
			'label' => 'Page',
			'hooks' => [
				'cotheme_before_content'      => 'Före innehåll',
				'cotheme_after_content'       => 'Efter innehåll',
				'cotheme_before_post'         => 'Före inlägg',
				'cotheme_after_post'          => 'Efter inlägg',
				'cotheme_before_post_content' => 'Före inläggsinnehåll',
				'cotheme_after_post_content'  => 'Efter inläggsinnehåll',
			],
		],
		[
			'label' => 'Sidebar',
			'hooks' => [
				'cotheme_sidebar' => 'Sidebar',
			],
		],
		[
			'label' => 'WooCommerce',
			'hooks' => [
				'cotheme_before_shop'         => 'Före shop-innehåll',
				'cotheme_after_shop'          => 'Efter shop-innehåll',
				'cotheme_before_product'      => 'Före produkt',
				'cotheme_after_product'       => 'Efter produkt',
			],
		],
	];
});

/**
 * Hjälpfunktion: Kolla om Themer har en layout för header/footer
 */
function cotheme_themer_header_enabled(): bool {
	if (class_exists('FLThemeBuilderLayoutData')) {
		$ids = FLThemeBuilderLayoutData::get_current_page_header_ids();
		return !empty($ids);
	}
	return false;
}

function cotheme_themer_footer_enabled(): bool {
	if (class_exists('FLThemeBuilderLayoutData')) {
		$ids = FLThemeBuilderLayoutData::get_current_page_footer_ids();
		return !empty($ids);
	}
	return false;
}

/**
 * Hjälpfunktion: Kolla om Themer har en content-layout för aktuell sida
 */
function cotheme_themer_content_enabled(): bool {
	if (class_exists('FLThemeBuilderLayoutData')) {
		$ids = FLThemeBuilderLayoutData::get_current_page_content_ids();
		return !empty($ids);
	}
	return false;
}

/**
 * Rendera Themer header (om den finns), annars fallback
 */
function cotheme_render_header(): void {
	if (cotheme_themer_header_enabled()) {
		FLThemeBuilderLayoutRenderer::render_header('cotheme_header');
	} else {
		cotheme_render_default_header();
	}
}

/**
 * Rendera Themer footer (om den finns), annars fallback.
 * BB Themer-outputen wrappas i en semantisk <footer> så vi får en stabil
 * selector (.cotheme-footer--themer) och kan applicera reveal-effekten.
 */
function cotheme_render_footer(): void {
	if (cotheme_themer_footer_enabled()) {
		echo '<footer class="cotheme-footer cotheme-footer--themer" role="contentinfo">';
		FLThemeBuilderLayoutRenderer::render_footer('cotheme_footer');
		echo '</footer>';
	} else {
		cotheme_render_default_footer();
	}
}

/**
 * Footer reveal — opt-in via filter.
 * Aktivera i child theme med:
 *   add_filter('cotheme_enable_footer_reveal', '__return_true');
 *
 * När påslagen:
 *  - <body> får klassen "has-footer-reveal" (CSS aktiveras)
 *  - footer-reveal.js enqueueas (mäter footerns höjd → CSS-variabel)
 */
function cotheme_footer_reveal_enabled(): bool {
	return (bool) apply_filters('cotheme_enable_footer_reveal', false);
}

add_filter('body_class', function (array $classes): array {
	if (cotheme_footer_reveal_enabled()) {
		$classes[] = 'has-footer-reveal';
	}
	return $classes;
});

add_action('wp_enqueue_scripts', function () {
	if (cotheme_footer_reveal_enabled()) {
		wp_enqueue_script(
			'cotheme-footer-reveal',
			COTHEME_URI . '/assets/js/footer-reveal.js',
			[],
			COTHEME_VERSION,
			true
		);
	}
});

/**
 * Standard-header (fallback om ingen Themer-layout finns)
 */
function cotheme_render_default_header(): void {
	?>
	<header class="cotheme-header">
		<div class="cotheme-container cotheme-header-inner">
			<div class="cotheme-logo">
				<?php if (has_custom_logo()) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a href="<?php echo esc_url(home_url('/')); ?>">
						<?php echo esc_html(get_bloginfo('name')); ?>
					</a>
				<?php endif; ?>
			</div>
			<?php if (has_nav_menu('primary')) : ?>
				<nav class="cotheme-nav" role="navigation" aria-label="<?php esc_attr_e('Huvudmeny', 'cotheme'); ?>">
					<?php wp_nav_menu([
						'theme_location' => 'primary',
						'container'      => false,
						'depth'          => 3,
						'fallback_cb'    => false,
					]); ?>
				</nav>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/**
 * Standard-footer (fallback om ingen Themer-layout finns)
 */
function cotheme_render_default_footer(): void {
	?>
	<footer class="cotheme-footer">
		<div class="cotheme-container cotheme-footer-inner">
			<?php if (is_active_sidebar('footer-widgets')) : ?>
				<div class="cotheme-footer-widgets">
					<?php dynamic_sidebar('footer-widgets'); ?>
				</div>
			<?php endif; ?>
			<?php if (has_nav_menu('footer')) : ?>
				<nav aria-label="<?php esc_attr_e('Footermeny', 'cotheme'); ?>">
					<?php wp_nav_menu([
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => false,
					]); ?>
				</nav>
			<?php endif; ?>
			<p class="cotheme-footer-copy">
				&copy; <?php echo esc_html(wp_date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>
			</p>
		</div>
	</footer>
	<?php
}

// Beaver Builder – aktivera på alla post types (inkl. WooCommerce-produkter)
add_filter('fl_builder_post_types', function ($post_types) {
	if (!in_array('page', $post_types)) {
		$post_types[] = 'page';
	}
	if (!in_array('post', $post_types)) {
		$post_types[] = 'post';
	}
	if (!in_array('product', $post_types)) {
		$post_types[] = 'product';
	}
	return $post_types;
});

// Beaver Themer – WooCommerce stöd (om WooCommerce är aktivt)
add_action('after_setup_theme', function () {
	if (class_exists('WooCommerce')) {
		add_theme_support('woocommerce');
		add_theme_support('wc-product-gallery-zoom');
		add_theme_support('wc-product-gallery-lightbox');
		add_theme_support('wc-product-gallery-slider');
	}
}, 20);


// ==========================================================================
// Kodsnuttar — head och footer
// ==========================================================================

// Head-kodsnutt (före </head>)
// OBS: Snutten saneras redan vid sparning (cotheme_sanitize_code_snippet) —
// bara användare med unfiltered_html kan lagra råkod. Därför renderas den för
// ALLA besökare, vilket krävs för t.ex. analytics och verifieringstaggar.
add_action('wp_head', function () {
	$snippet = get_theme_mod('cotheme_snippet_head', '');
	if (!empty($snippet)) {
		echo $snippet . "\n";
	}
}, 999);

// Footer-kodsnutt (före </body>) — se kommentar ovan om sanering/rendering.
add_action('wp_footer', function () {
	$snippet = get_theme_mod('cotheme_snippet_footer', '');
	if (!empty($snippet)) {
		echo $snippet . "\n";
	}
}, 999);


// ==========================================================================
// Inaktivera webbläsarens scroll restoration
// ==========================================================================
//
// Förhindrar att webbläsaren automatiskt scrollar tillbaka till en tidigare
// ankar-position när en sida besöks utan hash i URL:en. Standardläget
// (history.scrollRestoration = 'auto') gör att webbläsaren minns var
// användaren tidigare befunnit sig på samma URL — vilket ger upplevelsen
// att en sida "hoppar" ner till ett ankare även när hashen är borttagen.
//
// Skriptet körs inline i <head> med prioritet 1 så att inställningen
// sätts innan webbläsaren hinner återställa scroll-positionen.
add_action('wp_head', function () {
	echo "<script>history.scrollRestoration='manual';</script>\n";
}, 1);


// ==========================================================================
// Favicon – /favicon.ico i webbroten
// ==========================================================================
//
// WordPress egen webbplatsikon skriver ut 32x32 först, men Google
// rekommenderar favicon-storlekar som är multiplar av 48 px och hämtar
// dessutom /favicon.ico direkt från webbroten. Saknas den filen kan Google
// visa en generisk jordglob i sökresultatet i stället för sajtens ikon.
//
// Taggen skrivs ut med prioritet 5, alltså före wp_site_icon() (prioritet 99),
// så att 48x48-varianten står först i <head>. URL:en hålls stabil utan
// cache-buster eftersom Google vill se samma favicon-URL över tid.
add_action('wp_head', function () {
	if (!file_exists(ABSPATH . 'favicon.ico')) {
		return;
	}

	printf(
		'<link rel="icon" href="%s" sizes="48x48" />' . "\n",
		esc_url(site_url('/favicon.ico'))
	);
}, 5);


// ==========================================================================
// Cleanup – ta bort onödig WP-output
// ==========================================================================

add_action('init', function () {
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'wp_shortlink_wp_head');
	remove_action('wp_head', 'rest_output_link_wp_head');
	remove_action('wp_head', 'wp_oembed_add_discovery_links');
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('admin_print_styles', 'print_emoji_styles');
});

// Ta bort WP:s globala styles (behåll block-library om WooCommerce är aktivt)
add_action('wp_enqueue_scripts', function () {
	if (!class_exists('WooCommerce')) {
		wp_dequeue_style('wp-block-library');
		wp_dequeue_style('wp-block-library-theme');
	}
	wp_dequeue_style('global-styles');
	wp_dequeue_style('classic-theme-styles');
}, 100);


// ==========================================================================
// Inläggsinställningar — dölj rubrik och navigering per inlägg
// ==========================================================================

/**
 * Registrera meta box för inläggsinställningar
 */
add_action('add_meta_boxes', function () {
	$post_types = get_post_types(['public' => true], 'names');
	foreach ($post_types as $post_type) {
		add_meta_box(
			'cotheme_post_settings',
			__('Visningsinställningar', 'cotheme'),
			'cotheme_post_settings_meta_box',
			$post_type,
			'side',
			'default'
		);
	}
});

/**
 * Rendera meta box med kryssrutor
 */
function cotheme_post_settings_meta_box($post): void {
	wp_nonce_field('cotheme_post_settings', 'cotheme_post_settings_nonce');

	$hide_title = get_post_meta($post->ID, '_cotheme_hide_title', true);
	$hide_nav   = get_post_meta($post->ID, '_cotheme_hide_nav', true);
	?>
	<p>
		<label>
			<input type="checkbox" name="cotheme_hide_title" value="1" <?php checked($hide_title, '1'); ?>>
			<?php esc_html_e('Dölj rubrik', 'cotheme'); ?>
		</label>
	</p>
	<p>
		<label>
			<input type="checkbox" name="cotheme_hide_nav" value="1" <?php checked($hide_nav, '1'); ?>>
			<?php esc_html_e('Dölj inläggsnavigering', 'cotheme'); ?>
		</label>
	</p>
	<?php
}

/**
 * Spara meta box-data
 */
add_action('save_post', function ($post_id) {
	if (!isset($_POST['cotheme_post_settings_nonce'])) {
		return;
	}
	if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cotheme_post_settings_nonce'])), 'cotheme_post_settings')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	$hide_title = isset($_POST['cotheme_hide_title']) ? '1' : '';
	$hide_nav   = isset($_POST['cotheme_hide_nav']) ? '1' : '';

	update_post_meta($post_id, '_cotheme_hide_title', $hide_title);
	update_post_meta($post_id, '_cotheme_hide_nav', $hide_nav);
});

/**
 * Hjälpfunktioner för att kolla om rubrik/navigering ska döljas
 *
 * Kontrollerar först per-post-meta, sedan global inställning per inläggstyp.
 */
function cotheme_is_title_hidden(): bool {
	$post_id = get_the_ID();
	if (get_post_meta($post_id, '_cotheme_hide_title', true) === '1') {
		return true;
	}
	$post_type = get_post_type($post_id);
	return (bool) get_theme_mod("cotheme_hide_title_{$post_type}", false);
}

function cotheme_is_nav_hidden(): bool {
	$post_id = get_the_ID();
	if (get_post_meta($post_id, '_cotheme_hide_nav', true) === '1') {
		return true;
	}
	$post_type = get_post_type($post_id);
	return (bool) get_theme_mod("cotheme_hide_nav_{$post_type}", false);
}


// ==========================================================================
// Widget Areas
// ==========================================================================

add_action('widgets_init', function () {
	register_sidebar([
		'name'          => 'Footer Widget Area',
		'id'            => 'footer-widgets',
		'before_widget' => '<div class="cotheme-footer-widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="cotheme-footer-widget-title">',
		'after_title'   => '</h3>',
	]);

	// WooCommerce shop-sidebar
	if (class_exists('WooCommerce')) {
		register_sidebar([
			'name'          => 'Shop Sidebar',
			'id'            => 'shop-sidebar',
			'description'   => __('Widgetområde för WooCommerce-sidor', 'cotheme'),
			'before_widget' => '<div class="cotheme-shop-widget">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="cotheme-shop-widget-title">',
			'after_title'   => '</h3>',
		]);
	}
});
