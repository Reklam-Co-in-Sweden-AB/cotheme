<?php
/**
 * Customizer — Varumärkesfärger, färgtilldelning, typografi och layout
 *
 * @package CoTheme
 */

defined('ABSPATH') || exit;

// Max antal varumärkesfärger
define('COTHEME_MAX_BRAND_COLORS', 10);

add_action('customize_register', function (WP_Customize_Manager $wp_customize) {

	// ==================================================================
	// Panel: CoTheme
	// ==================================================================
	$wp_customize->add_panel('cotheme_panel', [
		'title'    => 'CoTheme',
		'priority' => 25,
	]);

	// ==================================================================
	// Sektion: Styleguide (navigerar preview till styleguide)
	// ==================================================================
	$wp_customize->add_section('cotheme_styleguide', [
		'title'       => 'Styleguide',
		'panel'       => 'cotheme_panel',
		'priority'    => 1,
		'description' => 'Visar en live-förhandsgranskning av alla temainställningar. Klicka på element i förhandsgranskningen för att redigera.',
	]);

	// ==================================================================
	// Sektion: Varumärkesfärger
	// ==================================================================
	$wp_customize->add_section('cotheme_brand_colors', [
		'title'       => 'Varumärkesfärger',
		'panel'       => 'cotheme_panel',
		'priority'    => 10,
		'description' => 'Definiera dina varumärkesfärger. Dessa kan sedan tilldelas till rubriker, brödtext, bakgrund m.m.',
	]);

	// Palett-presets
	$wp_customize->add_setting('cotheme_palette_preset', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	]);
	$wp_customize->add_control(new CoTheme_Palette_Presets_Control($wp_customize, 'cotheme_palette_preset', [
		'section' => 'cotheme_brand_colors',
	]));

	// Antal aktiva färger
	$wp_customize->add_setting('cotheme_brand_color_count', [
		'default'           => 3,
		'sanitize_callback' => 'absint',
	]);
	$wp_customize->add_control('cotheme_brand_color_count', [
		'label'       => 'Antal varumärkesfärger',
		'section'     => 'cotheme_brand_colors',
		'type'        => 'number',
		'input_attrs' => [
			'min' => 1,
			'max' => COTHEME_MAX_BRAND_COLORS,
		],
	]);

	// Registrera alla färgplatser
	$default_colors = cotheme_brand_color_defaults();

	for ($i = 1; $i <= COTHEME_MAX_BRAND_COLORS; $i++) {
		$wp_customize->add_setting("cotheme_brand_color_{$i}", [
			'default'           => $default_colors[$i] ?? '#cccccc',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'         => 'postMessage',
		]);
		$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, "cotheme_brand_color_{$i}", [
			'label'   => "Färg {$i}",
			'section' => 'cotheme_brand_colors',
		]));

		// Valfritt namn
		$wp_customize->add_setting("cotheme_brand_color_{$i}_name", [
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		]);
		$wp_customize->add_control("cotheme_brand_color_{$i}_name", [
			'label'       => "Namn färg {$i}",
			'section'     => 'cotheme_brand_colors',
			'type'        => 'text',
			'input_attrs' => ['placeholder' => "T.ex. Mörkblå"],
		]);
	}

	// ==================================================================
	// Sektion: Färgtilldelning
	// ==================================================================
	$wp_customize->add_section('cotheme_color_assignments', [
		'title'       => 'Färgtilldelning',
		'panel'       => 'cotheme_panel',
		'priority'    => 11,
		'description' => 'Välj vilken varumärkesfärg som ska användas var.',
	]);

	$assignments = [
		'cotheme_assign_primary'       => ['Primärfärg (knappar, länkar)', '1'],
		'cotheme_assign_primary_hover' => ['Primär hover', '8'],
		'cotheme_assign_secondary'     => ['Sekundärfärg', '2'],
		'cotheme_assign_quote'         => ['Citatfärg (blockquote)', '2'],
		'cotheme_assign_heading'       => ['Rubrikfärg', '3'],
		'cotheme_assign_text'          => ['Brödtextfärg', '4'],
		'cotheme_assign_text_light'    => ['Ljus textfärg', '5'],
		'cotheme_assign_bg'            => ['Bakgrundsfärg', '6'],
		'cotheme_assign_border'        => ['Ramfärg', '7'],
		'cotheme_assign_focus'         => ['Fokusfärg (tangentbordsfokus, textmarkering)', '1'],
	];

	foreach ($assignments as $id => [$label, $default]) {
		$wp_customize->add_setting($id, [
			'default'           => $default,
			'sanitize_callback' => 'cotheme_sanitize_color_slot',
			'transport'         => 'postMessage',
		]);
		$wp_customize->add_control(new CoTheme_Color_Assignment_Control($wp_customize, $id, [
			'label'   => $label,
			'section' => 'cotheme_color_assignments',
		]));
	}

	// ==================================================================
	// Sektion: Typografi — Rubriker
	// ==================================================================
	$wp_customize->add_section('cotheme_typography_heading', [
		'title' => 'Typografi — Rubriker',
		'panel' => 'cotheme_panel',
	]);

	cotheme_register_font_controls($wp_customize, 'heading', 'cotheme_typography_heading');

	// ==================================================================
	// Sektion: Typografi — Brödtext
	// ==================================================================
	$wp_customize->add_section('cotheme_typography_body', [
		'title' => 'Typografi — Brödtext',
		'panel' => 'cotheme_panel',
	]);

	cotheme_register_font_controls($wp_customize, 'body', 'cotheme_typography_body');

	// ==================================================================
	// Sektion: Adobe Fonts
	// ==================================================================
	$wp_customize->add_section('cotheme_adobe_fonts', [
		'title'       => 'Adobe Fonts',
		'panel'       => 'cotheme_panel',
		'description' => 'Klistra in ditt Adobe Fonts-projekt-ID. Typsnitten blir sedan valbara under rubrik- och brödtexttypsnitt.',
	]);

	$wp_customize->add_setting('cotheme_adobe_fonts_id', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_key',
	]);
	$wp_customize->add_control('cotheme_adobe_fonts_id', [
		'label'       => 'Projekt-ID',
		'section'     => 'cotheme_adobe_fonts',
		'type'        => 'text',
		'description' => 'Hittas under ditt projekt på fonts.adobe.com. Exempel: abc1def',
	]);

	$wp_customize->add_setting('cotheme_adobe_fonts_list', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	]);
	$wp_customize->add_control('cotheme_adobe_fonts_list', [
		'label'       => 'Tillgängliga typsnitt',
		'section'     => 'cotheme_adobe_fonts',
		'type'        => 'text',
		'description' => 'Ange typsnittsnamnen komma-separerat, t.ex: "futura-pt, proxima-nova".',
	]);

	// ==================================================================
	// Sektion: Egna typsnitt
	// ==================================================================
	$wp_customize->add_section('cotheme_custom_fonts_section', [
		'title'       => 'Egna typsnitt',
		'panel'       => 'cotheme_panel',
		'description' => 'Ladda upp egna typsnitt (WOFF2, WOFF, TTF, OTF) via Egna typsnitt-sidan under Utseende.',
	]);

	$wp_customize->add_setting('cotheme_custom_fonts_info', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	]);
	$wp_customize->add_control(new CoTheme_Custom_Fonts_Info_Control($wp_customize, 'cotheme_custom_fonts_info', [
		'section' => 'cotheme_custom_fonts_section',
	]));

	// ==================================================================
	// Sektion: Layout
	// ==================================================================
	$wp_customize->add_section('cotheme_layout', [
		'title' => 'Layout',
		'panel' => 'cotheme_panel',
	]);

	$wp_customize->add_setting('cotheme_container_width', [
		'default'           => '1200',
		'sanitize_callback' => 'absint',
		'transport'         => 'postMessage',
	]);
	$wp_customize->add_control('cotheme_container_width', [
		'label'       => 'Container-bredd (px)',
		'section'     => 'cotheme_layout',
		'type'        => 'number',
		'input_attrs' => [
			'min'  => 800,
			'max'  => 1920,
			'step' => 10,
		],
	]);

	// ==================================================================
	// Sektion: Kodsnuttar
	// ==================================================================
	$wp_customize->add_section('cotheme_code_snippets', [
		'title'       => 'Kodsnuttar',
		'panel'       => 'cotheme_panel',
		'priority'    => 200,
		'description' => 'Lägg till egen kod i head, body eller footer. Används t.ex. för spårningskod, Google Tag Manager, chatwidgets m.m.',
	]);

	// Head — före </head>
	$wp_customize->add_setting('cotheme_snippet_head', [
		'default'           => '',
		'sanitize_callback' => 'cotheme_sanitize_code_snippet',
	]);
	$wp_customize->add_control(new CoTheme_Code_Snippet_Control($wp_customize, 'cotheme_snippet_head', [
		'label'       => 'Head-kod',
		'description' => 'Infogas före &lt;/head&gt;. T.ex. meta-taggar, stilmallar, spårningskod.',
		'section'     => 'cotheme_code_snippets',
	]));

	// Body start — efter <body>
	$wp_customize->add_setting('cotheme_snippet_body_open', [
		'default'           => '',
		'sanitize_callback' => 'cotheme_sanitize_code_snippet',
	]);
	$wp_customize->add_control(new CoTheme_Code_Snippet_Control($wp_customize, 'cotheme_snippet_body_open', [
		'label'       => 'Body-kod (start)',
		'description' => 'Infogas direkt efter &lt;body&gt;. T.ex. Google Tag Manager (noscript).',
		'section'     => 'cotheme_code_snippets',
	]));

	// Footer — före </body>
	$wp_customize->add_setting('cotheme_snippet_footer', [
		'default'           => '',
		'sanitize_callback' => 'cotheme_sanitize_code_snippet',
	]);
	$wp_customize->add_control(new CoTheme_Code_Snippet_Control($wp_customize, 'cotheme_snippet_footer', [
		'label'       => 'Footer-kod',
		'description' => 'Infogas före &lt;/body&gt;. T.ex. chattwidgets, analysscript.',
		'section'     => 'cotheme_code_snippets',
	]));
});


// ==================================================================
// Hjälpfunktioner för färgsystemet
// ==================================================================

/**
 * Standardfärger för varumärkesfärgerna (med statisk cache)
 */
function cotheme_brand_color_defaults(): array {
	static $defaults = null;
	if ($defaults === null) {
		$defaults = [
			1 => '#0066cc', 2 => '#ff6600', 3 => '#222222', 4 => '#333333', 5 => '#666666',
			6 => '#ffffff', 7 => '#dddddd', 8 => '#004999', 9 => '#f5f5f5', 10 => '#e0e0e0',
		];
	}
	return $defaults;
}

/**
 * Bygg dropdown-val för varumärkesfärger
 * Visar alla registrerade färger (behövs för att WordPress ska acceptera sparade val)
 */
function cotheme_get_brand_color_choices(): array {
	$choices  = [];
	$defaults = cotheme_brand_color_defaults();

	for ($i = 1; $i <= COTHEME_MAX_BRAND_COLORS; $i++) {
		$name  = get_theme_mod("cotheme_brand_color_{$i}_name", '');
		$color = get_theme_mod("cotheme_brand_color_{$i}", $defaults[$i] ?? '#cccccc');
		$label = $name ? "{$name} ({$color})" : "Färg {$i} ({$color})";
		$choices[(string) $i] = $label;
	}

	return $choices;
}

/**
 * Hämta den faktiska hex-färgen för en tilldelning
 */
function cotheme_get_assigned_color(string $assignment, string $fallback = '#000000'): string {
	$slot = get_theme_mod("cotheme_assign_{$assignment}", '');
	if (empty($slot)) {
		return $fallback;
	}
	$defaults = cotheme_brand_color_defaults();
	return get_theme_mod("cotheme_brand_color_{$slot}", $defaults[(int) $slot] ?? $fallback);
}


// ==================================================================
// Striktare sanitize-callbacks
// ==================================================================

/**
 * Sanera kodsnuttar — tillåter bara för användare med unfiltered_html-behörighet
 */
function cotheme_sanitize_code_snippet(string $value): string {
	if (!current_user_can('unfiltered_html')) {
		return '';
	}
	return $value;
}


/**
 * Validera typsnittsstorlek (px) — tillåter heltal inom rimligt intervall
 */
function cotheme_sanitize_font_size($value): int {
	$size = absint($value);
	return max(10, min(120, $size));
}

/**
 * Validera radavstånd — tillåter decimaltal inom rimligt intervall
 */
function cotheme_sanitize_line_height($value): string {
	$lh = (float) $value;
	$lh = max(1.0, min(2.5, $lh));
	return (string) round($lh, 1);
}

/**
 * Validera font-vikt mot tillåtna värden
 */
function cotheme_sanitize_font_weight(string $value): string {
	$allowed = ['300', '400', '500', '600', '700'];
	return in_array($value, $allowed, true) ? $value : '400';
}

/**
 * Validera färgtilldelningsplats (slot) mot tillåtna heltal
 */
function cotheme_sanitize_color_slot(string $value): string {
	$slot = absint($value);
	if ($slot >= 1 && $slot <= COTHEME_MAX_BRAND_COLORS) {
		return (string) $slot;
	}
	return '1';
}

/**
 * Sanera font-family-strängar — tillåt bara säkra tecken
 */
function cotheme_sanitize_font_family(string $value): string {
	// Tillåt bara alfanumeriska tecken, bindestreck, understreck, mellanslag, komma, apostrof
	$clean = preg_replace('/[^a-zA-Z0-9\s\-_,\'"]/', '', $value);
	return sanitize_text_field($clean);
}


// ==================================================================
// Typsnittskontroller
// ==================================================================

function cotheme_register_font_controls(WP_Customize_Manager $wp_customize, string $context, string $section): void {
	$label = $context === 'heading' ? 'Rubrik' : 'Brödtext';

	// Källa
	$wp_customize->add_setting("cotheme_font_{$context}_source", [
		'default'           => 'system',
		'sanitize_callback' => 'sanitize_text_field',
	]);
	$wp_customize->add_control("cotheme_font_{$context}_source", [
		'label'   => "Typsnittskälla — {$label}",
		'section' => $section,
		'type'    => 'select',
		'choices' => [
			'system' => 'Systemtypsnitt',
			'google' => 'Google Fonts',
			'adobe'  => 'Adobe Fonts',
			'custom' => 'Eget typsnitt',
		],
	]);

	// System font
	$wp_customize->add_setting("cotheme_font_{$context}_system", [
		'default'           => 'system-ui',
		'sanitize_callback' => 'cotheme_sanitize_font_family',
	]);
	$wp_customize->add_control("cotheme_font_{$context}_system", [
		'label'   => "Systemtypsnitt — {$label}",
		'section' => $section,
		'type'    => 'select',
		'choices' => cotheme_system_fonts_list(),
	]);

	// Google Font
	$wp_customize->add_setting("cotheme_font_{$context}_google", [
		'default'           => '',
		'sanitize_callback' => 'cotheme_sanitize_font_family',
	]);
	$wp_customize->add_control("cotheme_font_{$context}_google", [
		'label'   => "Google Font — {$label}",
		'section' => $section,
		'type'    => 'select',
		'choices' => cotheme_google_fonts_list(),
	]);

	// Adobe Font
	$wp_customize->add_setting("cotheme_font_{$context}_adobe", [
		'default'           => '',
		'sanitize_callback' => 'cotheme_sanitize_font_family',
	]);
	$wp_customize->add_control("cotheme_font_{$context}_adobe", [
		'label'   => "Adobe Font — {$label}",
		'section' => $section,
		'type'    => 'select',
		'choices' => cotheme_adobe_fonts_choices(),
	]);

	// Eget typsnitt
	$custom_fonts = cotheme_custom_fonts_choices();
	$choices = !empty($custom_fonts)
		? array_merge(['' => '— Välj eget typsnitt —'], $custom_fonts)
		: ['' => '— Inga egna typsnitt uppladdade —'];

	$wp_customize->add_setting("cotheme_font_{$context}_custom", [
		'default'           => '',
		'sanitize_callback' => 'cotheme_sanitize_font_family',
	]);
	$wp_customize->add_control("cotheme_font_{$context}_custom", [
		'label'   => "Eget typsnitt — {$label}",
		'section' => $section,
		'type'    => 'select',
		'choices' => $choices,
	]);

	// Font-vikt
	$wp_customize->add_setting("cotheme_font_{$context}_weight", [
		'default'           => $context === 'heading' ? '700' : '400',
		'sanitize_callback' => 'cotheme_sanitize_font_weight',
	]);
	$wp_customize->add_control("cotheme_font_{$context}_weight", [
		'label'   => "Font-vikt — {$label}",
		'section' => $section,
		'type'    => 'select',
		'choices' => [
			'300' => 'Light (300)',
			'400' => 'Regular (400)',
			'500' => 'Medium (500)',
			'600' => 'Semi Bold (600)',
			'700' => 'Bold (700)',
		],
	]);

	// Typsnittsstorlekar
	if ($context === 'body') {
		// Brödtext: storlek per skärmtyp + radavstånd
		$body_sizes = [
			'cotheme_body_size_desktop' => ['Storlek — Desktop (px)', 16],
			'cotheme_body_size_tablet'  => ['Storlek — Tablet (px)', 16],
			'cotheme_body_size_mobile'  => ['Storlek — Mobil (px)', 15],
		];

		foreach ($body_sizes as $id => [$size_label, $default]) {
			$wp_customize->add_setting($id, [
				'default'           => $default,
				'sanitize_callback' => 'cotheme_sanitize_font_size',
				'transport'         => 'postMessage',
			]);
			$wp_customize->add_control($id, [
				'label'       => $size_label,
				'section'     => $section,
				'type'        => 'number',
				'input_attrs' => [
					'min'  => 10,
					'max'  => 32,
					'step' => 1,
				],
			]);
		}

		$wp_customize->add_setting('cotheme_body_line_height', [
			'default'           => '1.6',
			'sanitize_callback' => 'cotheme_sanitize_line_height',
			'transport'         => 'postMessage',
		]);
		$wp_customize->add_control('cotheme_body_line_height', [
			'label'       => 'Radavstånd',
			'section'     => $section,
			'type'        => 'number',
			'input_attrs' => [
				'min'  => 1,
				'max'  => 2.5,
				'step' => 0.1,
			],
		]);
	}

	if ($context === 'heading') {
		// Rubriknivåer: storlek desktop
		$heading_defaults = [
			'h1' => ['H1 — Desktop (px)', 44],
			'h2' => ['H2 — Desktop (px)', 36],
			'h3' => ['H3 — Desktop (px)', 28],
			'h4' => ['H4 — Desktop (px)', 22],
			'h5' => ['H5 — Desktop (px)', 18],
			'h6' => ['H6 — Desktop (px)', 16],
		];

		foreach ($heading_defaults as $tag => [$h_label, $default]) {
			$wp_customize->add_setting("cotheme_{$tag}_size_desktop", [
				'default'           => $default,
				'sanitize_callback' => 'cotheme_sanitize_font_size',
				'transport'         => 'postMessage',
			]);
			$wp_customize->add_control("cotheme_{$tag}_size_desktop", [
				'label'       => $h_label,
				'section'     => $section,
				'type'        => 'number',
				'input_attrs' => [
					'min'  => 10,
					'max'  => 120,
					'step' => 1,
				],
			]);
		}

		// Skalfaktor tablet/mobil
		$wp_customize->add_setting('cotheme_heading_scale_tablet', [
			'default'           => 85,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		]);
		$wp_customize->add_control('cotheme_heading_scale_tablet', [
			'label'       => 'Tablet-skalning (%)',
			'description' => 'Rubrikstorlekar skalas med denna procent på tablet.',
			'section'     => $section,
			'type'        => 'number',
			'input_attrs' => [
				'min'  => 50,
				'max'  => 100,
				'step' => 5,
			],
		]);

		$wp_customize->add_setting('cotheme_heading_scale_mobile', [
			'default'           => 75,
			'sanitize_callback' => 'absint',
			'transport'         => 'postMessage',
		]);
		$wp_customize->add_control('cotheme_heading_scale_mobile', [
			'label'       => 'Mobil-skalning (%)',
			'description' => 'Rubrikstorlekar skalas med denna procent på mobil.',
			'section'     => $section,
			'type'        => 'number',
			'input_attrs' => [
				'min'  => 50,
				'max'  => 100,
				'step' => 5,
			],
		]);
	}
}


// ==================================================================
// Custom Control: Info om egna typsnitt
// ==================================================================

if (class_exists('WP_Customize_Control')) {

	/**
	 * Palett-presets — visuella förval för färgscheman
	 */
	class CoTheme_Palette_Presets_Control extends WP_Customize_Control {
		public $type = 'cotheme_palette_presets';

		public function render_content(): void {
			$presets = [
				'default' => [
					'label'  => 'Standard',
					'count'  => 5,
					'colors' => ['#0066cc', '#ff6600', '#222222', '#333333', '#666666'],
				],
				'warm' => [
					'label'  => 'Varm',
					'count'  => 5,
					'colors' => ['#e07a5f', '#3d405b', '#81b29a', '#f2cc8f', '#f4f1de'],
				],
				'cool' => [
					'label'  => 'Sval',
					'count'  => 5,
					'colors' => ['#264653', '#2a9d8f', '#e9c46a', '#f4a261', '#e76f51'],
				],
				'mono' => [
					'label'  => 'Monokrom',
					'count'  => 5,
					'colors' => ['#212529', '#495057', '#adb5bd', '#dee2e6', '#f8f9fa'],
				],
				'forest' => [
					'label'  => 'Skog',
					'count'  => 5,
					'colors' => ['#2d6a4f', '#40916c', '#52b788', '#74c69d', '#1b4332'],
				],
				'ocean' => [
					'label'  => 'Hav',
					'count'  => 5,
					'colors' => ['#03045e', '#0077b6', '#00b4d8', '#90e0ef', '#caf0f8'],
				],
				'sunset' => [
					'label'  => 'Solnedgång',
					'count'  => 5,
					'colors' => ['#ff6b6b', '#ee5a24', '#f0932b', '#ffbe76', '#2c2c54'],
				],
				'elegant' => [
					'label'  => 'Elegant',
					'count'  => 5,
					'colors' => ['#2b2d42', '#8d99ae', '#edf2f4', '#ef233c', '#d90429'],
				],
			];
			?>
			<?php $saved_palettes = get_option('cotheme_saved_palettes', []); ?>
			<div class="cotheme-presets">
				<span class="customize-control-title">Palett-förval</span>
				<div class="cotheme-presets-grid">
					<?php foreach ($presets as $key => $preset) : ?>
					<button type="button"
						class="cotheme-preset-btn"
						data-preset="<?php echo esc_attr($key); ?>"
						data-colors="<?php echo esc_attr(wp_json_encode($preset['colors'])); ?>"
						data-count="<?php echo esc_attr($preset['count']); ?>"
						title="<?php echo esc_attr($preset['label']); ?>"
					>
						<span class="cotheme-preset-swatches">
							<?php foreach ($preset['colors'] as $c) : ?>
							<span style="background:<?php echo esc_attr($c); ?>;"></span>
							<?php endforeach; ?>
						</span>
						<span class="cotheme-preset-name"><?php echo esc_html($preset['label']); ?></span>
					</button>
					<?php endforeach; ?>
				</div>

				<?php if (!empty($saved_palettes)) : ?>
				<span class="customize-control-title" style="margin-top:16px;">Sparade paletter</span>
				<div class="cotheme-presets-grid" id="cotheme-saved-palettes">
					<?php foreach ($saved_palettes as $key => $palette) : ?>
					<div class="cotheme-preset-btn cotheme-saved-palette" style="position:relative;">
						<button type="button"
							class="cotheme-preset-btn-inner"
							data-colors="<?php echo esc_attr(wp_json_encode($palette['colors'])); ?>"
							data-count="<?php echo esc_attr($palette['count']); ?>"
							style="background:none;border:none;padding:0;cursor:pointer;width:100%;"
						>
							<span class="cotheme-preset-swatches">
								<?php foreach ($palette['colors'] as $c) : ?>
								<span style="background:<?php echo esc_attr($c); ?>;"></span>
								<?php endforeach; ?>
							</span>
							<span class="cotheme-preset-name"><?php echo esc_html($palette['name']); ?></span>
						</button>
						<button type="button" class="cotheme-delete-palette" data-key="<?php echo esc_attr($key); ?>" title="Ta bort">&times;</button>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<div style="margin-top:12px;">
					<button type="button" class="button" id="cotheme-save-palette-btn">Spara aktuell palett</button>
				</div>
			</div>

			<style>
				.cotheme-presets { margin-bottom: 12px; }
				.cotheme-presets-grid {
					display: grid;
					grid-template-columns: repeat(2, 1fr);
					gap: 8px;
					margin-top: 8px;
				}
				.cotheme-preset-btn {
					background: #fff;
					border: 2px solid #ddd;
					border-radius: 6px;
					padding: 8px;
					cursor: pointer;
					text-align: center;
					transition: border-color 0.15s;
				}
				.cotheme-preset-btn:hover,
				.cotheme-preset-btn.active {
					border-color: #0073aa;
				}
				.cotheme-preset-swatches {
					display: flex;
					gap: 0;
					height: 28px;
					border-radius: 4px;
					overflow: hidden;
					margin-bottom: 4px;
				}
				.cotheme-preset-swatches span {
					flex: 1;
				}
				.cotheme-preset-name {
					font-size: 11px;
					color: #555;
					font-family: -apple-system, BlinkMacSystemFont, sans-serif;
				}
				.cotheme-saved-palette { position: relative; }
				.cotheme-delete-palette {
					position: absolute;
					top: 2px;
					right: 2px;
					background: rgba(0,0,0,0.5);
					color: #fff;
					border: none;
					border-radius: 50%;
					width: 18px;
					height: 18px;
					font-size: 12px;
					line-height: 1;
					cursor: pointer;
					opacity: 0;
					transition: opacity 0.15s;
					padding: 0;
				}
				.cotheme-saved-palette:hover .cotheme-delete-palette { opacity: 1; }
			</style>
			<?php
		}
	}

	/**
	 * Visuell färgtilldelning — visar färgcirklar istället för dropdown
	 */
	class CoTheme_Color_Assignment_Control extends WP_Customize_Control {
		public $type = 'cotheme_color_assignment';

		public function render_content(): void {
			$current = $this->value();
			$defaults = cotheme_brand_color_defaults();
			?>
			<div class="cotheme-assign-control" data-setting="<?php echo esc_attr($this->id); ?>">
				<span class="cotheme-assign-label"><?php echo esc_html($this->label); ?></span>
				<div class="cotheme-assign-circles">
					<?php for ($i = 1; $i <= COTHEME_MAX_BRAND_COLORS; $i++) :
						$color = get_theme_mod("cotheme_brand_color_{$i}", $defaults[$i] ?? '#ccc');
						$active = ((string) $i === (string) $current) ? ' active' : '';
					?>
					<button type="button"
						class="cotheme-assign-circle<?php echo esc_attr($active); ?>"
						data-slot="<?php echo esc_attr($i); ?>"
						data-color-id="cotheme_brand_color_<?php echo esc_attr($i); ?>"
						style="background: <?php echo esc_attr($color); ?>;"
						title="Färg <?php echo esc_attr($i); ?>"
					></button>
					<?php endfor; ?>
				</div>
				<input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr($current); ?>">
			</div>

			<style>
				.cotheme-assign-control {
					margin-bottom: 14px;
					padding-bottom: 14px;
					border-bottom: 1px solid #f0f0f0;
				}
				.cotheme-assign-label {
					display: block;
					font-size: 12px;
					font-weight: 600;
					color: #1e1e1e;
					margin-bottom: 8px;
				}
				.cotheme-assign-circles {
					display: flex;
					gap: 6px;
					flex-wrap: wrap;
				}
				.cotheme-assign-circle {
					width: 28px;
					height: 28px;
					border-radius: 50%;
					border: 2px solid transparent;
					cursor: pointer;
					transition: all 0.15s ease;
					padding: 0;
					outline: none;
					box-shadow: inset 0 0 0 1px rgba(0,0,0,0.1);
				}
				.cotheme-assign-circle:hover {
					transform: scale(1.15);
				}
				.cotheme-assign-circle.active {
					border-color: #0073aa;
					box-shadow: 0 0 0 2px #fff, 0 0 0 4px #0073aa, inset 0 0 0 1px rgba(0,0,0,0.1);
				}
			</style>
			<?php
		}
	}

	class CoTheme_Custom_Fonts_Info_Control extends WP_Customize_Control {
		public $type = 'cotheme_info';

		public function render_content(): void {
			$custom_fonts = cotheme_get_custom_fonts();
			$admin_url    = admin_url('themes.php?page=cotheme-custom-fonts');

			if (!empty($custom_fonts)) {
				echo '<p><strong>Uppladdade typsnitt:</strong></p><ul style="margin:0.5em 0;">';
				foreach ($custom_fonts as $name => $variants) {
					$count = count($variants);
					printf(
						'<li>%s (%d %s)</li>',
						esc_html($name),
						$count,
						$count === 1 ? 'variant' : 'varianter'
					);
				}
				echo '</ul>';
			} else {
				echo '<p>Inga egna typsnitt uppladdade ännu.</p>';
			}

			printf(
				'<p><a href="%s" class="button" target="_blank">Hantera egna typsnitt</a></p>',
				esc_url($admin_url)
			);
		}
	}

	/**
	 * Kodsnutt-kontroll — textarea med monospace-font
	 */
	class CoTheme_Code_Snippet_Control extends WP_Customize_Control {
		public $type = 'cotheme_code_snippet';

		public function render_content(): void {
			?>
			<label>
				<span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
				<?php if ($this->description) : ?>
					<span class="description customize-control-description"><?php echo wp_kses_post($this->description); ?></span>
				<?php endif; ?>
				<textarea
					rows="6"
					style="width:100%; font-family:ui-monospace,SFMono-Regular,'SF Mono',Menlo,monospace; font-size:12px; tab-size:2; resize:vertical; background:#1e1e1e; color:#d4d4d4; border-radius:4px; padding:10px; border:1px solid #444;"
					<?php $this->link(); ?>
				><?php echo esc_textarea($this->value()); ?></textarea>
			</label>
			<?php if (!current_user_can('unfiltered_html')) : ?>
				<p class="description" style="color:#d63638; margin-top:6px;">
					Du behöver behörigheten <code>unfiltered_html</code> för att spara kodsnuttar.
				</p>
			<?php endif; ?>
			<?php
		}
	}
}


// ==================================================================
// Sektion: Inläggstyper — globala visningsinställningar per CPT
// ==================================================================

add_action('customize_register', function (WP_Customize_Manager $wp_customize) {
	$wp_customize->add_section('cotheme_post_type_display', [
		'title'       => 'Inläggstyper',
		'panel'       => 'cotheme_panel',
		'priority'    => 95,
		'description' => 'Dölj titel och navigering globalt för alla poster av en viss inläggstyp.',
	]);

	// Hämta alla publika post types
	$post_types = get_post_types(['public' => true], 'objects');

	foreach ($post_types as $post_type) {
		$slug  = $post_type->name;
		$label = $post_type->labels->name;

		// Dölj titel
		$wp_customize->add_setting("cotheme_hide_title_{$slug}", [
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
		]);
		$wp_customize->add_control("cotheme_hide_title_{$slug}", [
			'label'   => sprintf('Dölj titel — %s', $label),
			'section' => 'cotheme_post_type_display',
			'type'    => 'checkbox',
		]);

		// Dölj navigering
		$wp_customize->add_setting("cotheme_hide_nav_{$slug}", [
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
		]);
		$wp_customize->add_control("cotheme_hide_nav_{$slug}", [
			'label'   => sprintf('Dölj navigering — %s', $label),
			'section' => 'cotheme_post_type_display',
			'type'    => 'checkbox',
		]);
	}
});


// ==================================================================
// Customizer Preview JS
// ==================================================================

add_action('customize_preview_init', function () {
	wp_enqueue_script(
		'cotheme-customizer-preview',
		COTHEME_URI . '/assets/js/customizer-preview.js',
		['customize-preview'],
		COTHEME_VERSION,
		true
	);

	// Skicka färgdata till preview
	$data = [];
	for ($i = 1; $i <= COTHEME_MAX_BRAND_COLORS; $i++) {
		$data["brand_color_{$i}"] = get_theme_mod("cotheme_brand_color_{$i}", '#cccccc');
	}
	$assignments = ['primary', 'primary_hover', 'secondary', 'quote', 'heading', 'text', 'text_light', 'bg', 'border', 'focus'];
	foreach ($assignments as $key) {
		$data["assign_{$key}"] = get_theme_mod("cotheme_assign_{$key}", '');
	}
	wp_localize_script('cotheme-customizer-preview', 'cothemePreviewData', $data);
});

// ==================================================================
// Customizer Control JS
// ==================================================================

add_action('customize_controls_enqueue_scripts', function () {
	wp_enqueue_script(
		'cotheme-customizer-controls',
		COTHEME_URI . '/assets/js/customizer-controls.js',
		['customize-controls'],
		COTHEME_VERSION,
		true
	);

	wp_localize_script('cotheme-customizer-controls', 'cothemeCustomizer', [
		'ajaxUrl' => admin_url('admin-ajax.php'),
		'nonce'   => wp_create_nonce('cotheme_palette_nonce'),
	]);
});


// ==================================================================
// AJAX: Spara palett
// ==================================================================

// Max antal sparade paletter (förhindrar option-pollution)
define('COTHEME_MAX_SAVED_PALETTES', 50);

/**
 * Konvertera sparade paletter (med UUID-nycklar) till JS-vänligt format med id-fält
 */
function cotheme_palettes_for_js(array $palettes): array {
	$result = [];
	foreach ($palettes as $id => $palette) {
		$palette['id'] = $id;
		$result[] = $palette;
	}
	return $result;
}

add_action('wp_ajax_cotheme_save_palette', function () {
	check_ajax_referer('cotheme_palette_nonce', 'nonce');

	if (!current_user_can('edit_theme_options')) {
		wp_send_json_error('Behörighet saknas.');
	}

	$name   = mb_substr(sanitize_text_field(wp_unslash($_POST['name'] ?? '')), 0, 100);
	$colors = isset($_POST['colors']) ? array_map('sanitize_hex_color', wp_unslash($_POST['colors'])) : [];
	$count  = absint(wp_unslash($_POST['count'] ?? 0));

	if (empty($name) || empty($colors) || $count < 1) {
		wp_send_json_error('Ogiltiga värden.');
	}

	$palettes = get_option('cotheme_saved_palettes', []);

	if (count($palettes) >= COTHEME_MAX_SAVED_PALETTES) {
		wp_send_json_error('Maximalt antal paletter (' . COTHEME_MAX_SAVED_PALETTES . ') uppnått.');
	}

	// Unikt ID för säker borttagning (undviker TOCTOU med array-index)
	$palette_id = wp_generate_uuid4();
	$palettes[$palette_id] = [
		'name'   => $name,
		'colors' => $colors,
		'count'  => $count,
	];

	update_option('cotheme_saved_palettes', $palettes);
	wp_send_json_success(['palettes' => cotheme_palettes_for_js($palettes)]);
});

// ==================================================================
// AJAX: Ta bort palett
// ==================================================================

add_action('wp_ajax_cotheme_delete_palette', function () {
	check_ajax_referer('cotheme_palette_nonce', 'nonce');

	if (!current_user_can('edit_theme_options')) {
		wp_send_json_error('Behörighet saknas.');
	}

	$palette_id = sanitize_text_field(wp_unslash($_POST['key'] ?? ''));
	$palettes   = get_option('cotheme_saved_palettes', []);

	if (!isset($palettes[$palette_id])) {
		wp_send_json_error('Paletten hittades inte.');
	}

	unset($palettes[$palette_id]);
	update_option('cotheme_saved_palettes', $palettes);
	wp_send_json_success(['palettes' => cotheme_palettes_for_js($palettes)]);
});
