<?php
/**
 * Typsnittshantering — Google Fonts, Adobe Fonts och egna typsnitt
 *
 * @package CoTheme
 */

defined('ABSPATH') || exit;

/**
 * Kurerad lista med populära Google Fonts
 */
function cotheme_google_fonts_list(): array {
	return [
		''                   => '— Välj Google Font —',
		'Inter'              => 'Inter',
		'Roboto'             => 'Roboto',
		'Open Sans'          => 'Open Sans',
		'Lato'               => 'Lato',
		'Montserrat'         => 'Montserrat',
		'Poppins'            => 'Poppins',
		'Raleway'            => 'Raleway',
		'Nunito'             => 'Nunito',
		'Nunito Sans'        => 'Nunito Sans',
		'Source Sans 3'      => 'Source Sans 3',
		'Merriweather'       => 'Merriweather',
		'Playfair Display'   => 'Playfair Display',
		'Lora'               => 'Lora',
		'PT Sans'            => 'PT Sans',
		'PT Serif'           => 'PT Serif',
		'Work Sans'          => 'Work Sans',
		'DM Sans'            => 'DM Sans',
		'DM Serif Display'   => 'DM Serif Display',
		'Libre Baskerville'  => 'Libre Baskerville',
		'Josefin Sans'       => 'Josefin Sans',
		'Crimson Text'       => 'Crimson Text',
		'Cormorant Garamond' => 'Cormorant Garamond',
		'Manrope'            => 'Manrope',
		'Space Grotesk'      => 'Space Grotesk',
		'Sora'               => 'Sora',
		'Outfit'             => 'Outfit',
		'Plus Jakarta Sans'  => 'Plus Jakarta Sans',
		'Cabin'              => 'Cabin',
		'Karla'              => 'Karla',
		'Rubik'              => 'Rubik',
		'Barlow'             => 'Barlow',
		'Mulish'             => 'Mulish',
		'Quicksand'          => 'Quicksand',
		'Oswald'             => 'Oswald',
		'Bebas Neue'         => 'Bebas Neue',
		'Archivo'            => 'Archivo',
		'IBM Plex Sans'      => 'IBM Plex Sans',
		'IBM Plex Serif'     => 'IBM Plex Serif',
		'Bitter'             => 'Bitter',
		'Vollkorn'           => 'Vollkorn',
	];
}

/**
 * System-fonts som alltid finns tillgängliga
 */
function cotheme_system_fonts_list(): array {
	return [
		''                  => '— Välj systemtypsnitt —',
		'system-ui'         => 'System UI (standard)',
		'Arial'             => 'Arial',
		'Helvetica'         => 'Helvetica',
		'Georgia'           => 'Georgia',
		'Times New Roman'   => 'Times New Roman',
		'Courier New'       => 'Courier New',
		'Verdana'           => 'Verdana',
	];
}

/**
 * Bygg Google Fonts URL baserat på valda typsnitt
 */
function cotheme_build_google_fonts_url(): string {
	$heading_source = get_theme_mod('cotheme_font_heading_source', 'system');
	$body_source    = get_theme_mod('cotheme_font_body_source', 'system');
	$fonts          = [];

	if ($heading_source === 'google') {
		$font = get_theme_mod('cotheme_font_heading_google', '');
		if ($font) {
			$fonts[$font] = $font;
		}
	}

	if ($body_source === 'google') {
		$font = get_theme_mod('cotheme_font_body_google', '');
		if ($font) {
			$fonts[$font] = $font;
		}
	}

	if (empty($fonts)) {
		return '';
	}

	// Ladda vanliga vikter så att BB-moduler också kan använda dem
	$weight_str = '300;400;500;600;700';

	$families = [];
	foreach ($fonts as $font) {
		$families[] = str_replace(' ', '+', $font) . ':wght@' . $weight_str;
	}

	return 'https://fonts.googleapis.com/css2?family=' . implode('&family=', $families) . '&display=swap';
}

/**
 * Hämta Adobe Fonts embed-URL baserat på projekt-ID
 */
function cotheme_get_adobe_fonts_url(): string {
	$project_id = get_theme_mod('cotheme_adobe_fonts_id', '');
	if (empty($project_id)) {
		return '';
	}
	return 'https://use.typekit.net/' . sanitize_key($project_id) . '.css';
}

/**
 * Hämta Adobe Fonts-typsnitt som dropdown-val
 * Parsar den komma-separerade listan från Customizern
 */
function cotheme_adobe_fonts_choices(): array {
	$list = get_theme_mod('cotheme_adobe_fonts_list', '');
	if (empty($list)) {
		return ['' => '— Ange typsnitt under Adobe Fonts —'];
	}

	$choices = ['' => '— Välj Adobe Font —'];
	$fonts = array_map('trim', explode(',', $list));
	foreach ($fonts as $font) {
		if (!empty($font)) {
			$choices[$font] = $font;
		}
	}

	return $choices;
}

/**
 * Hämta listan av uppladdade egna typsnitt (med statisk cache)
 */
function cotheme_get_custom_fonts(): array {
	static $cache = null;
	if ($cache === null) {
		$cache = get_option('cotheme_custom_fonts', []);
	}
	return $cache;
}

/**
 * Spara ett eget typsnitt
 */
function cotheme_save_custom_font(string $name, array $files): void {
	$fonts = cotheme_get_custom_fonts();
	$fonts[$name] = $files;
	update_option('cotheme_custom_fonts', $fonts);
	delete_transient('cotheme_head_css');
}

/**
 * Ta bort ett eget typsnitt
 */
function cotheme_delete_custom_font(string $name): void {
	$fonts = cotheme_get_custom_fonts();
	if (isset($fonts[$name])) {
		// Ta bort filerna från mediebiblioteket
		foreach ($fonts[$name] as $variant) {
			if (!empty($variant['attachment_id'])) {
				wp_delete_attachment($variant['attachment_id'], true);
			}
		}
		unset($fonts[$name]);
		update_option('cotheme_custom_fonts', $fonts);
		delete_transient('cotheme_head_css');
	}
}

/**
 * Generera @font-face CSS för egna typsnitt
 */
function cotheme_custom_fonts_css(): string {
	$fonts = cotheme_get_custom_fonts();
	if (empty($fonts)) {
		return '';
	}

	$css = '';
	foreach ($fonts as $name => $variants) {
		foreach ($variants as $variant) {
			$weight = $variant['weight'] ?? '400';
			$style  = $variant['style'] ?? 'normal';
			$url    = $variant['url'] ?? '';

			if (empty($url)) {
				continue;
			}

			// Avgör format baserat på filändelse
			$ext = pathinfo($url, PATHINFO_EXTENSION);
			$format_map = [
				'woff2' => 'woff2',
				'woff'  => 'woff',
				'ttf'   => 'truetype',
				'otf'   => 'opentype',
			];
			$format = $format_map[$ext] ?? 'woff2';

			$css .= sprintf(
				"@font-face {\n\tfont-family: '%s';\n\tsrc: url('%s') format('%s');\n\tfont-weight: %s;\n\tfont-style: %s;\n\tfont-display: swap;\n}\n",
				esc_attr($name),
				esc_url($url),
				$format,
				esc_attr($weight),
				esc_attr($style)
			);
		}
	}

	return $css;
}

/**
 * Lista egna typsnitt som val i dropdown
 */
function cotheme_custom_fonts_choices(): array {
	$fonts   = cotheme_get_custom_fonts();
	$choices = [];
	foreach ($fonts as $name => $variants) {
		$choices[$name] = $name;
	}
	return $choices;
}

/**
 * Bygg CSS font-family-sträng för ett typsnitt
 */
function cotheme_get_font_family(string $context = 'heading'): string {
	$source = get_theme_mod("cotheme_font_{$context}_source", 'system');

	switch ($source) {
		case 'google':
			$font = get_theme_mod("cotheme_font_{$context}_google", '');
			return $font ? "'{$font}', system-ui, sans-serif" : 'system-ui, sans-serif';

		case 'adobe':
			$font = get_theme_mod("cotheme_font_{$context}_adobe", '');
			return $font ? "'{$font}', system-ui, sans-serif" : 'system-ui, sans-serif';

		case 'custom':
			$font = get_theme_mod("cotheme_font_{$context}_custom", '');
			return $font ? "'{$font}', system-ui, sans-serif" : 'system-ui, sans-serif';

		case 'system':
		default:
			$font = get_theme_mod("cotheme_font_{$context}_system", 'system-ui');
			if ($font === 'system-ui') {
				return "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif";
			}
			return "'{$font}', system-ui, sans-serif";
	}
}
