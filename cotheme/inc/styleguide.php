<?php
/**
 * Styleguide — renderas i Customizer-förhandsgranskningen
 *
 * Visas när ?cotheme-styleguide=1 läggs till URL:en.
 * Elementen är klickbara och öppnar rätt Customizer-sektion.
 *
 * @package CoTheme
 */

defined('ABSPATH') || exit;

add_action('template_redirect', function () {
	if (empty($_GET['cotheme-styleguide']) || '1' !== sanitize_text_field(wp_unslash($_GET['cotheme-styleguide'])) || !is_customize_preview()) {
		return;
	}

	cotheme_render_styleguide_page();
	exit;
});

function cotheme_render_styleguide_page(): void {
	$site_name = get_bloginfo('name');
	$count     = absint(get_theme_mod('cotheme_brand_color_count', 3));
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
	<style>
		*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

		body {
			background: #f0f0f0;
			font-family: var(--cotheme-font-body, system-ui, sans-serif);
			font-weight: var(--cotheme-font-body-weight, 400);
			color: var(--cotheme-text, #333);
			-webkit-font-smoothing: antialiased;
		}

		/* ===== Layout ===== */
		.sg { max-width: 960px; margin: 0 auto; padding: 32px 24px 64px; }

		.sg-card {
			background: #fff;
			border-radius: 12px;
			padding: 32px;
			margin-bottom: 24px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.06);
			position: relative;
		}

		.sg-card[data-section] {
			cursor: pointer;
			transition: box-shadow 0.2s ease;
		}

		.sg-card[data-section]:hover {
			box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 0 0 2px var(--cotheme-primary, #0066cc);
		}

		.sg-card[data-section]::after {
			content: attr(data-tooltip);
			position: absolute;
			top: 12px;
			right: 12px;
			background: var(--cotheme-primary, #0066cc);
			color: #fff;
			font-size: 11px;
			padding: 4px 10px;
			border-radius: 4px;
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			font-weight: 500;
			opacity: 0;
			transition: opacity 0.15s ease;
			pointer-events: none;
		}

		.sg-card[data-section]:hover::after { opacity: 1; }

		.sg-label {
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.1em;
			color: var(--cotheme-text-light, #666);
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			font-weight: 600;
			margin-bottom: 20px;
		}

		/* ===== Hero / Varumärke ===== */
		.sg-hero {
			background: var(--cotheme-primary, #0066cc);
			border-radius: 12px;
			padding: 48px 40px;
			margin-bottom: 24px;
			text-align: center;
			position: relative;
			overflow: hidden;
		}

		.sg-hero-name {
			font-family: var(--cotheme-font-heading, system-ui, sans-serif);
			font-weight: var(--cotheme-font-heading-weight, 700);
			font-size: clamp(2rem, 5vw, 3.5rem);
			color: #fff;
			margin-bottom: 8px;
			line-height: 1.1;
		}

		.sg-hero-tagline {
			font-family: var(--cotheme-font-body, system-ui, sans-serif);
			color: rgba(255,255,255,0.75);
			font-size: 1rem;
		}

		.sg-hero-divider {
			width: 48px;
			height: 2px;
			background: rgba(255,255,255,0.4);
			margin: 16px auto;
		}

		/* ===== Färger: grid ===== */
		.sg-colors-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
			gap: 12px;
		}

		.sg-swatch {
			border-radius: 8px;
			overflow: hidden;
			border: 1px solid rgba(0,0,0,0.06);
			position: relative;
		}

		.sg-swatch-color {
			height: 64px;
			position: relative;
			cursor: pointer;
			transition: opacity 0.15s;
		}

		.sg-swatch-color:hover { opacity: 0.85; }

		.sg-swatch-color::after {
			content: '\270E';
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			font-size: 20px;
			color: #fff;
			text-shadow: 0 1px 3px rgba(0,0,0,0.4);
			opacity: 0;
			transition: opacity 0.15s;
			pointer-events: none;
		}

		.sg-swatch-color:hover::after { opacity: 1; }

		.sg-swatch-picker {
			position: absolute;
			top: 68px;
			left: 0;
			z-index: 100;
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 4px 20px rgba(0,0,0,0.15);
			padding: 12px;
			display: none;
		}

		.sg-swatch-picker.open { display: block; }

		.sg-swatch-picker input[type="color"] {
			width: 80px;
			height: 40px;
			border: none;
			cursor: pointer;
			padding: 0;
			border-radius: 4px;
		}

		.sg-swatch-info {
			padding: 8px 10px;
			background: #fff;
		}

		.sg-swatch-name {
			font-size: 11px;
			font-weight: 600;
			color: var(--cotheme-text, #333);
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			line-height: 1.3;
		}

		.sg-swatch-hex {
			font-size: 10px;
			color: var(--cotheme-text-light, #666);
			font-family: ui-monospace, SFMono-Regular, monospace;
		}

		/* Tilldelningsfärger — mindre grid */
		.sg-assigned-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
			gap: 8px;
		}

		.sg-assigned-chip {
			display: flex;
			align-items: center;
			gap: 6px;
			padding: 6px 10px;
			background: #f7f7f7;
			border-radius: 6px;
		}

		.sg-assigned-dot {
			width: 20px;
			height: 20px;
			border-radius: 50%;
			flex-shrink: 0;
			border: 1px solid rgba(0,0,0,0.08);
		}

		.sg-assigned-name {
			font-size: 11px;
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			color: var(--cotheme-text, #333);
			font-weight: 500;
		}

		/* ===== Typografi ===== */
		.sg-type-cols {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 32px;
		}

		@media (max-width: 700px) {
			.sg-type-cols { grid-template-columns: 1fr; }
		}

		.sg-specimen-title {
			font-family: var(--cotheme-font-heading, system-ui, sans-serif);
			font-weight: var(--cotheme-font-heading-weight, 700);
			font-size: 2rem;
			color: var(--cotheme-heading, #222);
			margin-bottom: 12px;
		}

		.sg-specimen-alphabet {
			font-family: var(--cotheme-font-heading, system-ui, sans-serif);
			font-weight: var(--cotheme-font-heading-weight, 700);
			font-size: 1.1rem;
			color: var(--cotheme-heading, #222);
			line-height: 1.8;
			margin-bottom: 16px;
			word-break: break-all;
		}

		.sg-specimen-meta {
			font-size: 11px;
			font-family: ui-monospace, SFMono-Regular, monospace;
			color: var(--cotheme-primary, #0066cc);
			margin-bottom: 6px;
		}

		.sg-heading-sample {
			font-family: var(--cotheme-font-heading, system-ui, sans-serif);
			font-weight: var(--cotheme-font-heading-weight, 700);
			color: var(--cotheme-heading, #222);
			line-height: 1.2;
			margin-bottom: 4px;
		}

		.sg-heading-sample.h1 { font-size: clamp(2rem, 4vw, 2.75rem); }
		.sg-heading-sample.h2 { font-size: clamp(1.625rem, 3.2vw, 2.25rem); }
		.sg-heading-sample.h3 { font-size: clamp(1.375rem, 2.6vw, 1.75rem); }
		.sg-heading-sample.h4 { font-size: clamp(1.125rem, 2vw, 1.375rem); }
		.sg-heading-sample.h5 { font-size: 1.125rem; }
		.sg-heading-sample.h6 { font-size: 1rem; }

		.sg-body-text {
			font-family: var(--cotheme-font-body, system-ui, sans-serif);
			font-weight: var(--cotheme-font-body-weight, 400);
			color: var(--cotheme-text, #333);
			font-size: 1rem;
			line-height: 1.6;
		}

		.sg-body-text p { margin-bottom: 1em; }
		.sg-body-text p:last-child { margin-bottom: 0; }

		.sg-body-light {
			color: var(--cotheme-text-light, #666);
			font-size: 0.875rem;
		}

		.sg-body-link {
			color: var(--cotheme-primary, #0066cc);
			text-decoration: underline;
			text-decoration-thickness: 1px;
			text-underline-offset: 2px;
		}

		/* ===== Heading-lista ===== */
		.sg-headings-list { margin-top: 24px; }

		.sg-heading-row {
			display: flex;
			align-items: baseline;
			gap: 12px;
			padding: 12px 0;
			border-bottom: 1px solid #f0f0f0;
		}

		.sg-heading-row:last-child { border-bottom: none; }

		.sg-heading-tag {
			flex-shrink: 0;
			width: 28px;
			font-size: 10px;
			font-weight: 600;
			color: var(--cotheme-text-light, #666);
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
		}

		/* ===== Komponenter ===== */
		.sg-components-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 24px;
		}

		@media (max-width: 700px) {
			.sg-components-grid { grid-template-columns: 1fr; }
		}

		.sg-component-group h4 {
			font-size: 11px;
			text-transform: uppercase;
			letter-spacing: 0.08em;
			color: var(--cotheme-text-light, #666);
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			font-weight: 600;
			margin-bottom: 12px;
		}

		/* Knappar */
		.sg-buttons { display: flex; flex-wrap: wrap; gap: 10px; }

		.sg-btn {
			display: inline-block;
			padding: 10px 24px;
			border-radius: 4px;
			font-size: 0.9rem;
			font-weight: 600;
			font-family: var(--cotheme-font-body, system-ui, sans-serif);
			line-height: 1;
			cursor: default;
			border: 2px solid transparent;
		}

		.sg-btn-primary { background: var(--cotheme-primary, #0066cc); color: #fff; }
		.sg-btn-secondary { background: var(--cotheme-secondary, #ff6600); color: #fff; }
		.sg-btn-outline {
			background: transparent;
			border-color: var(--cotheme-primary, #0066cc);
			color: var(--cotheme-primary, #0066cc);
		}
		.sg-btn-ghost {
			background: transparent;
			color: var(--cotheme-primary, #0066cc);
			padding-left: 0;
			padding-right: 0;
			text-decoration: underline;
			text-underline-offset: 2px;
		}

		/* Formulär */
		.sg-form-stack { display: flex; flex-direction: column; gap: 12px; }

		.sg-form-label {
			font-size: 0.85rem;
			font-weight: 600;
			color: var(--cotheme-text, #333);
			margin-bottom: 2px;
		}

		.sg-form-input {
			padding: 10px 12px;
			border: 1px solid var(--cotheme-border, #ddd);
			border-radius: 4px;
			font-size: 0.9rem;
			font-family: var(--cotheme-font-body, system-ui, sans-serif);
			background: #fff;
			color: var(--cotheme-text, #333);
			width: 100%;
		}

		.sg-form-input:focus {
			outline: none;
			border-color: var(--cotheme-primary, #0066cc);
			box-shadow: 0 0 0 3px rgba(0,102,204,0.12);
		}

		/* Blockquote */
		.sg-blockquote {
			border-left: 3px solid var(--cotheme-primary, #0066cc);
			padding: 16px 20px;
			font-style: italic;
			color: var(--cotheme-text-light, #666);
			font-family: var(--cotheme-font-body, system-ui, sans-serif);
			font-size: 1.05rem;
			line-height: 1.6;
			background: rgba(0,0,0,0.015);
			border-radius: 0 6px 6px 0;
		}

		/* ===== WCAG Kontrastverktyg ===== */
		.sg-wcag-grid {
			display: flex;
			flex-direction: column;
			gap: 0;
		}

		.sg-wcag-row {
			display: grid;
			grid-template-columns: 1fr auto auto auto;
			gap: 12px;
			align-items: center;
			padding: 12px 0;
			border-bottom: 1px solid #f0f0f0;
		}

		.sg-wcag-row:last-child { border-bottom: none; }

		.sg-wcag-pair {
			display: flex;
			align-items: center;
			gap: 10px;
		}

		.sg-wcag-preview {
			width: 40px;
			height: 40px;
			border-radius: 6px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-weight: 700;
			font-size: 14px;
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			border: 1px solid rgba(0,0,0,0.06);
			flex-shrink: 0;
		}

		.sg-wcag-info {
			display: flex;
			flex-direction: column;
			gap: 1px;
		}

		.sg-wcag-pair-name {
			font-size: 12px;
			font-weight: 600;
			color: var(--cotheme-text, #333);
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
		}

		.sg-wcag-pair-colors {
			font-size: 10px;
			color: var(--cotheme-text-light, #666);
			font-family: ui-monospace, SFMono-Regular, monospace;
		}

		.sg-wcag-ratio {
			font-size: 13px;
			font-weight: 700;
			font-family: ui-monospace, SFMono-Regular, monospace;
			color: var(--cotheme-text, #333);
			min-width: 52px;
			text-align: right;
		}

		.sg-wcag-badges {
			display: flex;
			gap: 4px;
			min-width: 110px;
			justify-content: flex-end;
		}

		.sg-wcag-badge {
			font-size: 10px;
			font-weight: 700;
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			padding: 3px 7px;
			border-radius: 4px;
			letter-spacing: 0.02em;
			line-height: 1;
		}

		.sg-wcag-badge-pass {
			background: #d4edda;
			color: #155724;
		}

		.sg-wcag-badge-fail {
			background: #f8d7da;
			color: #721c24;
		}

		.sg-wcag-badge-large {
			background: #fff3cd;
			color: #856404;
		}

		.sg-wcag-summary {
			display: flex;
			gap: 16px;
			margin-top: 20px;
			padding-top: 16px;
			border-top: 2px solid #f0f0f0;
		}

		.sg-wcag-score {
			flex: 1;
			text-align: center;
			padding: 12px;
			border-radius: 8px;
			background: #f7f7f7;
		}

		.sg-wcag-score-value {
			font-size: 24px;
			font-weight: 800;
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			line-height: 1;
			margin-bottom: 4px;
		}

		.sg-wcag-score-label {
			font-size: 10px;
			text-transform: uppercase;
			letter-spacing: 0.08em;
			color: var(--cotheme-text-light, #666);
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			font-weight: 600;
		}

		.sg-wcag-score-good .sg-wcag-score-value { color: #155724; }
		.sg-wcag-score-warn .sg-wcag-score-value { color: #856404; }
		.sg-wcag-score-bad .sg-wcag-score-value { color: #721c24; }

		/* WCAG Förslag */
		.sg-wcag-suggestion {
			grid-column: 1 / -1;
			display: flex;
			align-items: center;
			gap: 10px;
			padding: 10px 14px;
			background: #fffbeb;
			border: 1px solid #fde68a;
			border-radius: 8px;
			margin-top: -4px;
			margin-bottom: 4px;
		}

		.sg-wcag-suggestion-icon {
			font-size: 14px;
			flex-shrink: 0;
		}

		.sg-wcag-suggestion-text {
			font-size: 11px;
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			color: #92400e;
			flex: 1;
			line-height: 1.4;
		}

		.sg-wcag-suggestion-preview {
			display: flex;
			align-items: center;
			gap: 6px;
			flex-shrink: 0;
		}

		.sg-wcag-suggestion-swatch {
			width: 24px;
			height: 24px;
			border-radius: 4px;
			border: 1px solid rgba(0,0,0,0.1);
		}

		.sg-wcag-suggestion-hex {
			font-size: 11px;
			font-family: ui-monospace, SFMono-Regular, monospace;
			color: #92400e;
			font-weight: 600;
		}

		.sg-wcag-fix-btn {
			background: #f59e0b;
			color: #fff;
			border: none;
			border-radius: 5px;
			padding: 5px 12px;
			font-size: 11px;
			font-weight: 600;
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			cursor: pointer;
			white-space: nowrap;
			transition: background 0.15s;
			flex-shrink: 0;
		}

		.sg-wcag-fix-btn:hover { background: #d97706; }

		.sg-wcag-all-pass {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 12px 16px;
			background: #d1fae5;
			border-radius: 8px;
			margin-top: 12px;
		}

		.sg-wcag-all-pass-text {
			font-size: 12px;
			font-family: -apple-system, BlinkMacSystemFont, sans-serif;
			color: #065f46;
			font-weight: 600;
		}
	</style>
</head>
<body>
<div class="sg">

	<!-- Hero -->
	<div class="sg-hero" data-section="cotheme_brand_colors" data-tooltip="Redigera färger">
		<div class="sg-hero-name"><?php echo esc_html($site_name ?: 'Sajtnamn'); ?></div>
		<div class="sg-hero-divider"></div>
		<div class="sg-hero-tagline"><?php echo esc_html(get_bloginfo('description') ?: 'Din tagline här'); ?></div>
	</div>

	<!-- Varumärkesfärger -->
	<div class="sg-card" data-section="cotheme_brand_colors" data-tooltip="Redigera färger">
		<div class="sg-label">Varumärkesfärger</div>
		<div class="sg-colors-grid">
			<?php
			$defaults = cotheme_brand_color_defaults();
			for ($i = 1; $i <= $count; $i++) :
				$color = get_theme_mod("cotheme_brand_color_{$i}", $defaults[$i] ?? '#ccc');
				$name  = get_theme_mod("cotheme_brand_color_{$i}_name", '');
				$label = $name ?: "Färg {$i}";
			?>
			<div class="sg-swatch" data-brand-index="<?php echo (int) $i; ?>">
				<div class="sg-swatch-color" style="background: var(--cotheme-brand-<?php echo (int) $i; ?>, <?php echo esc_attr($color); ?>);" data-edit-color="<?php echo (int) $i; ?>"></div>
				<div class="sg-swatch-picker" id="sg-picker-<?php echo (int) $i; ?>">
					<input type="color" value="<?php echo esc_attr($color); ?>" data-color-input="<?php echo (int) $i; ?>">
				</div>
				<div class="sg-swatch-info">
					<div class="sg-swatch-name"><?php echo esc_html($label); ?></div>
					<div class="sg-swatch-hex" data-hex-label="<?php echo (int) $i; ?>"><?php echo esc_html($color); ?></div>
				</div>
			</div>
			<?php endfor; ?>
		</div>
	</div>

	<!-- Tilldelade färger -->
	<div class="sg-card" data-section="cotheme_color_assignments" data-tooltip="Redigera tilldelning">
		<div class="sg-label">Tilldelade färger</div>
		<div class="sg-assigned-grid">
			<?php
			$roles = [
				'primary'       => 'Primär',
				'secondary'     => 'Sekundär',
				'heading'       => 'Rubrik',
				'text'          => 'Text',
				'text-light'    => 'Ljus text',
				'bg'            => 'Bakgrund',
				'border'        => 'Ram',
				'primary-hover' => 'Hover',
			];
			foreach ($roles as $var => $label) :
			?>
			<div class="sg-assigned-chip">
				<div class="sg-assigned-dot" style="background: var(--cotheme-<?php echo esc_attr($var); ?>);"></div>
				<span class="sg-assigned-name"><?php echo esc_html($label); ?></span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- Typografi -->
	<div class="sg-card" data-section="cotheme_typography_heading" data-tooltip="Redigera typsnitt">
		<div class="sg-type-cols">
			<!-- Vänster: rubrik-specimen -->
			<div>
				<div class="sg-label">Rubriktypsnitt</div>
				<div class="sg-specimen-title"><?php echo esc_html($site_name ?: 'Rubriker'); ?></div>
				<div class="sg-specimen-alphabet">
					A a B b C c D d E e F f G g H h I i J j
					K k L l M m N n O o P p Q q R r S s T
					t U u V v W w X x Y y Z z Å å Ä ä Ö ö
				</div>
				<div class="sg-specimen-meta" id="sg-heading-meta"></div>

				<div class="sg-headings-list">
					<?php
					$headings = [
						'h1' => 'Overskrift 1',
						'h2' => 'Overskrift 2',
						'h3' => 'Overskrift 3',
						'h4' => 'Overskrift 4',
						'h5' => 'Overskrift 5',
						'h6' => 'Overskrift 6',
					];
					foreach ($headings as $tag => $text) :
					?>
					<div class="sg-heading-row">
						<span class="sg-heading-tag"><?php echo esc_html(strtoupper($tag)); ?></span>
						<div class="sg-heading-sample <?php echo esc_attr($tag); ?>"><?php echo esc_html($text); ?></div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Höger: brödtext-specimen -->
			<div data-section="cotheme_typography_body" data-tooltip="Redigera brödtext" style="cursor:pointer;">
				<div class="sg-label">Brödtexttypsnitt</div>
				<div class="sg-body-text">
					<p>Här ser du hur brödtext kommer att se ut på din webbplats. Du kan anpassa typografin för att matcha ditt varumärkes personlighet.</p>
					<p>Oavsett om du siktar på ett modernt och stilrent uttryck eller en mer traditionell och elegant känsla, sätter rätt typografi tonen för ditt innehåll.</p>
					<p class="sg-body-light">Det här är ljusare text — perfekt för bildtexter, metadata och sekundärt innehåll som inte ska dominera sidan.</p>
					<p>Text med en <a href="#" class="sg-body-link" onclick="return false;">länk som ser ut så här</a> och kan anpassas via primärfärgen.</p>
				</div>
				<div class="sg-specimen-meta" id="sg-body-meta"></div>
			</div>
		</div>
	</div>

	<!-- Komponenter -->
	<div class="sg-card" data-section="cotheme_color_assignments" data-tooltip="Redigera färgtilldelning">
		<div class="sg-label">Komponenter</div>
		<div class="sg-components-grid">
			<!-- Knappar -->
			<div class="sg-component-group">
				<h4>Knappar</h4>
				<div class="sg-buttons">
					<span class="sg-btn sg-btn-primary">Primär</span>
					<span class="sg-btn sg-btn-secondary">Sekundär</span>
					<span class="sg-btn sg-btn-outline">Outline</span>
					<span class="sg-btn sg-btn-ghost">Textlänk</span>
				</div>
			</div>

			<!-- Formulär -->
			<div class="sg-component-group">
				<h4>Formulär</h4>
				<div class="sg-form-stack">
					<div>
						<div class="sg-form-label">Namn</div>
						<input type="text" class="sg-form-input" placeholder="Anna Svensson" readonly>
					</div>
					<div>
						<div class="sg-form-label">E-post</div>
						<input type="email" class="sg-form-input" placeholder="anna@exempel.se" readonly>
					</div>
				</div>
			</div>

			<!-- Citat -->
			<div class="sg-component-group" style="grid-column: 1 / -1;">
				<h4>Citat</h4>
				<blockquote class="sg-blockquote">
					"Design handlar inte bara om hur det ser ut och känns. Design handlar om hur det fungerar."
				</blockquote>
			</div>
		</div>
	</div>

	<!-- WCAG Kontrastanalys -->
	<div class="sg-card" data-section="cotheme_color_assignments" data-tooltip="Redigera färgtilldelning">
		<div class="sg-label">WCAG 2.1 Kontrastanalys</div>
		<div class="sg-wcag-grid" id="sg-wcag-grid">
			<!-- Fylls via JavaScript -->
		</div>
		<div class="sg-wcag-summary" id="sg-wcag-summary"></div>
	</div>

</div>

<script>
(function () {
	var parentCustomize = (window.parent && window.parent.wp && window.parent.wp.customize)
		? window.parent.wp.customize
		: null;

	// Klick → öppna Customizer-sektion
	document.querySelectorAll('[data-section]').forEach(function (el) {
		el.addEventListener('click', function (e) {
			// Ignorera klick på färgredigerare
			if (e.target.closest('.sg-swatch-color') || e.target.closest('.sg-swatch-picker')) return;

			e.preventDefault();
			e.stopPropagation();
			var section = this.getAttribute('data-section');
			if (section && parentCustomize) {
				parentCustomize.section(section).focus();
			}
		});
	});

	// ===== Inline färgredigering =====

	// Öppna/stäng färgväljare
	document.querySelectorAll('[data-edit-color]').forEach(function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			e.stopPropagation();

			var index = this.getAttribute('data-edit-color');
			var picker = document.getElementById('sg-picker-' + index);
			if (!picker) return;

			// Stäng alla andra
			document.querySelectorAll('.sg-swatch-picker.open').forEach(function (p) {
				if (p !== picker) p.classList.remove('open');
			});

			picker.classList.toggle('open');

			// Fokusera färginput
			if (picker.classList.contains('open')) {
				var input = picker.querySelector('input[type="color"]');
				if (input) input.click();
			}
		});
	});

	// Stäng picker vid klick utanför
	document.addEventListener('click', function (e) {
		if (!e.target.closest('.sg-swatch')) {
			document.querySelectorAll('.sg-swatch-picker.open').forEach(function (p) {
				p.classList.remove('open');
			});
		}
	});

	// Färgändring → uppdatera Customizer-setting
	document.querySelectorAll('[data-color-input]').forEach(function (input) {
		input.addEventListener('input', function () {
			var index = this.getAttribute('data-color-input');
			var newColor = this.value;

			// Uppdatera hex-label
			var hexLabel = document.querySelector('[data-hex-label="' + index + '"]');
			if (hexLabel) hexLabel.textContent = newColor;

			// Uppdatera Customizer-setting i parent
			if (parentCustomize) {
				var settingId = 'cotheme_brand_color_' + index;
				if (parentCustomize(settingId)) {
					parentCustomize(settingId).set(newColor);
				}
			}
		});
	});

	// ===== WCAG 2.1 Kontrastverktyg =====

	/**
	 * Beräkna relativ luminans enligt WCAG 2.1
	 * https://www.w3.org/TR/WCAG21/#dfn-relative-luminance
	 */
	function sRGBtoLinear(c) {
		c = c / 255;
		return c <= 0.04045 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
	}

	function relativeLuminance(r, g, b) {
		return 0.2126 * sRGBtoLinear(r) + 0.7152 * sRGBtoLinear(g) + 0.0722 * sRGBtoLinear(b);
	}

	function contrastRatio(l1, l2) {
		var lighter = Math.max(l1, l2);
		var darker  = Math.min(l1, l2);
		return (lighter + 0.05) / (darker + 0.05);
	}

	/**
	 * Konvertera RGB → HSL
	 */
	function rgbToHsl(r, g, b) {
		r /= 255; g /= 255; b /= 255;
		var max = Math.max(r, g, b), min = Math.min(r, g, b);
		var h, s, l = (max + min) / 2;

		if (max === min) {
			h = s = 0;
		} else {
			var d = max - min;
			s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
			switch (max) {
				case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
				case g: h = ((b - r) / d + 2) / 6; break;
				case b: h = ((r - g) / d + 4) / 6; break;
			}
		}
		return { h: h, s: s, l: l };
	}

	/**
	 * Konvertera HSL → RGB
	 */
	function hslToRgb(h, s, l) {
		var r, g, b;
		if (s === 0) {
			r = g = b = l;
		} else {
			function hue2rgb(p, q, t) {
				if (t < 0) t += 1;
				if (t > 1) t -= 1;
				if (t < 1/6) return p + (q - p) * 6 * t;
				if (t < 1/2) return q;
				if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
				return p;
			}
			var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
			var p = 2 * l - q;
			r = hue2rgb(p, q, h + 1/3);
			g = hue2rgb(p, q, h);
			b = hue2rgb(p, q, h - 1/3);
		}
		return {
			r: Math.round(r * 255),
			g: Math.round(g * 255),
			b: Math.round(b * 255)
		};
	}

	/**
	 * RGB → hex
	 */
	function rgbToHex(r, g, b) {
		return '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
	}

	/**
	 * Beräkna en justerad förgrundsfärg som uppnår önskad kontrast mot bakgrunden.
	 * Behåller nyans och mättnad, justerar ljushet via binärsökning.
	 */
	function suggestFgColor(fgColor, bgColor, targetRatio) {
		var bgLum = relativeLuminance(bgColor.r, bgColor.g, bgColor.b);
		var hsl = rgbToHsl(fgColor.r, fgColor.g, fgColor.b);

		// Bestäm riktning: om bakgrunden är ljus → gör fg mörkare, annars ljusare
		var needDarker = bgLum > 0.5;
		var low, high;

		if (needDarker) {
			low = 0; high = hsl.l;
		} else {
			low = hsl.l; high = 1;
		}

		// Binärsökning på lightness
		var bestL = needDarker ? 0 : 1;
		for (var i = 0; i < 30; i++) {
			var mid = (low + high) / 2;
			var rgb = hslToRgb(hsl.h, hsl.s, mid);
			var lum = relativeLuminance(rgb.r, rgb.g, rgb.b);
			var ratio = contrastRatio(lum, bgLum);

			if (ratio >= targetRatio) {
				bestL = mid;
				// Försök hitta en ljushet närmare originalet
				if (needDarker) {
					low = mid;
				} else {
					high = mid;
				}
			} else {
				if (needDarker) {
					high = mid;
				} else {
					low = mid;
				}
			}
		}

		var result = hslToRgb(hsl.h, hsl.s, bestL);
		var resultLum = relativeLuminance(result.r, result.g, result.b);
		var finalRatio = contrastRatio(resultLum, bgLum);

		if (finalRatio < targetRatio) return null;

		return { r: result.r, g: result.g, b: result.b, hex: rgbToHex(result.r, result.g, result.b) };
	}

	/**
	 * Beräkna en justerad bakgrundsfärg (för fall där fg är fast, t.ex. vit text på knapp).
	 */
	function suggestBgColor(fgColor, bgColor, targetRatio) {
		var fgLum = relativeLuminance(fgColor.r, fgColor.g, fgColor.b);
		var hsl = rgbToHsl(bgColor.r, bgColor.g, bgColor.b);

		// Om fg är ljus → gör bg mörkare, annars ljusare
		var needDarker = fgLum > 0.5;
		var low, high;

		if (needDarker) {
			low = 0; high = hsl.l;
		} else {
			low = hsl.l; high = 1;
		}

		var bestL = needDarker ? 0 : 1;
		for (var i = 0; i < 30; i++) {
			var mid = (low + high) / 2;
			var rgb = hslToRgb(hsl.h, hsl.s, mid);
			var lum = relativeLuminance(rgb.r, rgb.g, rgb.b);
			var ratio = contrastRatio(fgLum, lum);

			if (ratio >= targetRatio) {
				bestL = mid;
				if (needDarker) {
					low = mid;
				} else {
					high = mid;
				}
			} else {
				if (needDarker) {
					high = mid;
				} else {
					low = mid;
				}
			}
		}

		var result = hslToRgb(hsl.h, hsl.s, bestL);
		var resultLum = relativeLuminance(result.r, result.g, result.b);
		var finalRatio = contrastRatio(fgLum, resultLum);

		if (finalRatio < targetRatio) return null;

		return { r: result.r, g: result.g, b: result.b, hex: rgbToHex(result.r, result.g, result.b) };
	}

	/**
	 * Hämta beräknad RGB från en CSS-variabel via computed style
	 */
	function getCSSColor(varName) {
		var val = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
		if (!val) return null;

		// Hantera hex
		if (val.charAt(0) === '#') {
			var hex = val.replace('#', '');
			if (hex.length === 3) hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
			return {
				r: parseInt(hex.substr(0, 2), 16),
				g: parseInt(hex.substr(2, 2), 16),
				b: parseInt(hex.substr(4, 2), 16),
				hex: '#' + hex
			};
		}

		// Hantera rgb/rgba
		var m = val.match(/rgba?\(\s*(\d+),\s*(\d+),\s*(\d+)/);
		if (m) {
			var r = parseInt(m[1]), g = parseInt(m[2]), b = parseInt(m[3]);
			return {
				r: r, g: g, b: b,
				hex: '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)
			};
		}

		return null;
	}

	/**
	 * Mappning: CSS-variabel → Customizer-setting (tilldelning)
	 * Används för att veta vilken varumärkesfärg som ska ändras
	 */
	var cssVarToAssignment = {
		'--cotheme-text':       'cotheme_assign_text',
		'--cotheme-text-light': 'cotheme_assign_text_light',
		'--cotheme-heading':    'cotheme_assign_heading',
		'--cotheme-primary':    'cotheme_assign_primary',
		'--cotheme-secondary':  'cotheme_assign_secondary',
		'--cotheme-bg':         'cotheme_assign_bg'
	};

	/**
	 * Hitta vilken varumärkesfärg-setting en CSS-variabel pekar på
	 */
	function resolveSettingId(cssVar) {
		if (!cssVar || !parentCustomize) return null;
		var assignId = cssVarToAssignment[cssVar];
		if (!assignId) return null;
		var slot = parentCustomize(assignId) ? parentCustomize(assignId).get() : null;
		if (!slot) return null;
		return 'cotheme_brand_color_' + slot;
	}

	/**
	 * Färgpar att kontrollera
	 * adjustTarget: 'fg' eller 'bg' — vilken färg som ska justeras vid förslag
	 */
	var wcagPairs = [
		{ name: 'Brödtext / Bakgrund',     fg: '--cotheme-text',       bg: '--cotheme-bg',        type: 'normal', adjustTarget: 'fg' },
		{ name: 'Ljus text / Bakgrund',     fg: '--cotheme-text-light', bg: '--cotheme-bg',        type: 'normal', adjustTarget: 'fg' },
		{ name: 'Rubrik / Bakgrund',        fg: '--cotheme-heading',    bg: '--cotheme-bg',        type: 'large',  adjustTarget: 'fg' },
		{ name: 'Primär (länk) / Bakgrund', fg: '--cotheme-primary',    bg: '--cotheme-bg',        type: 'normal', adjustTarget: 'fg' },
		{ name: 'Vit / Primärknapp',        fg: null, fgHex: '#ffffff', bg: '--cotheme-primary',   type: 'normal', adjustTarget: 'bg' },
		{ name: 'Vit / Sekundärknapp',      fg: null, fgHex: '#ffffff', bg: '--cotheme-secondary', type: 'normal', adjustTarget: 'bg' },
		{ name: 'Primär / Vit (outline)',   fg: '--cotheme-primary',    bg: null, bgHex: '#ffffff', type: 'normal', adjustTarget: 'fg' },
	];

	function renderWCAG() {
		var grid    = document.getElementById('sg-wcag-grid');
		var summary = document.getElementById('sg-wcag-summary');
		if (!grid) return;

		var html = '';
		var passAA = 0, passAAA = 0, total = wcagPairs.length;
		var failCount = 0;

		wcagPairs.forEach(function (pair, pairIndex) {
			var fgColor, bgColor;

			if (pair.fg) {
				fgColor = getCSSColor(pair.fg);
			} else {
				var h = pair.fgHex.replace('#', '');
				fgColor = { r: parseInt(h.substr(0,2),16), g: parseInt(h.substr(2,2),16), b: parseInt(h.substr(4,2),16), hex: pair.fgHex };
			}

			if (pair.bg) {
				bgColor = getCSSColor(pair.bg);
			} else {
				var h2 = pair.bgHex.replace('#', '');
				bgColor = { r: parseInt(h2.substr(0,2),16), g: parseInt(h2.substr(2,2),16), b: parseInt(h2.substr(4,2),16), hex: pair.bgHex };
			}

			if (!fgColor || !bgColor) return;

			var fgLum = relativeLuminance(fgColor.r, fgColor.g, fgColor.b);
			var bgLum = relativeLuminance(bgColor.r, bgColor.g, bgColor.b);
			var ratio = contrastRatio(fgLum, bgLum);
			var ratioStr = ratio.toFixed(2) + ':1';

			// WCAG 2.1 krav
			var aaThreshold  = pair.type === 'large' ? 3 : 4.5;
			var aaaThreshold = pair.type === 'large' ? 4.5 : 7;
			var meetsAA  = ratio >= aaThreshold;
			var meetsAAA = ratio >= aaaThreshold;

			if (meetsAA) passAA++;
			if (meetsAAA) passAAA++;

			// Badges
			var badges = '';
			if (meetsAAA) {
				badges += '<span class="sg-wcag-badge sg-wcag-badge-pass">AAA</span>';
				badges += '<span class="sg-wcag-badge sg-wcag-badge-pass">AA</span>';
			} else if (meetsAA) {
				badges += '<span class="sg-wcag-badge sg-wcag-badge-fail">AAA</span>';
				badges += '<span class="sg-wcag-badge sg-wcag-badge-pass">AA</span>';
			} else {
				badges += '<span class="sg-wcag-badge sg-wcag-badge-fail">AAA</span>';
				badges += '<span class="sg-wcag-badge sg-wcag-badge-fail">AA</span>';
			}

			// Stor text-markering
			if (pair.type === 'large' && !meetsAA) {
				if (ratio >= 3) {
					badges += '<span class="sg-wcag-badge sg-wcag-badge-large">Stor</span>';
				}
			}

			html += '<div class="sg-wcag-row">';
			html += '<div class="sg-wcag-pair">';
			html += '<div class="sg-wcag-preview" style="background:' + bgColor.hex + ';color:' + fgColor.hex + ';">Aa</div>';
			html += '<div class="sg-wcag-info">';
			html += '<span class="sg-wcag-pair-name">' + pair.name + '</span>';
			html += '<span class="sg-wcag-pair-colors">' + fgColor.hex + ' / ' + bgColor.hex + '</span>';
			html += '</div></div>';
			html += '<div class="sg-wcag-ratio">' + ratioStr + '</div>';
			html += '<div class="sg-wcag-badges">' + badges + '</div>';
			html += '</div>';

			// Förslag vid underkänd AA
			if (!meetsAA) {
				failCount++;
				var suggested = null;
				var adjustCssVar = null;
				var adjustLabel = '';

				if (pair.adjustTarget === 'fg') {
					suggested = suggestFgColor(fgColor, bgColor, aaThreshold);
					adjustCssVar = pair.fg;
					adjustLabel = 'förgrund';
				} else {
					suggested = suggestBgColor(fgColor, bgColor, aaThreshold);
					adjustCssVar = pair.bg;
					adjustLabel = 'bakgrund';
				}

				if (suggested) {
					var settingId = resolveSettingId(adjustCssVar);
					var newRatio = contrastRatio(
						pair.adjustTarget === 'fg'
							? relativeLuminance(suggested.r, suggested.g, suggested.b)
							: fgLum,
						pair.adjustTarget === 'bg'
							? relativeLuminance(suggested.r, suggested.g, suggested.b)
							: bgLum
					);

					html += '<div class="sg-wcag-suggestion">';
					html += '<span class="sg-wcag-suggestion-icon">&#9881;</span>';
					html += '<span class="sg-wcag-suggestion-text">';
					html += 'Justera ' + adjustLabel + ' till <strong>' + suggested.hex + '</strong>';
					html += ' (' + newRatio.toFixed(2) + ':1)';
					html += '</span>';
					html += '<div class="sg-wcag-suggestion-preview">';
					html += '<div class="sg-wcag-suggestion-swatch" style="background:' + suggested.hex + ';"></div>';
					html += '<span class="sg-wcag-suggestion-hex">' + suggested.hex + '</span>';
					html += '</div>';

					if (settingId) {
						html += '<button type="button" class="sg-wcag-fix-btn" data-fix-setting="' + settingId + '" data-fix-color="' + suggested.hex + '">';
						html += 'Applicera';
						html += '</button>';
					}

					html += '</div>';
				}
			}
		});

		// Meddelande om alla godkända
		if (failCount === 0) {
			html += '<div class="sg-wcag-all-pass">';
			html += '<span class="sg-wcag-suggestion-icon">&#10003;</span>';
			html += '<span class="sg-wcag-all-pass-text">Alla färgkombinationer uppfyller WCAG 2.1 AA-krav!</span>';
			html += '</div>';
		}

		grid.innerHTML = html;

		// Sammanfattning
		var pctAA  = Math.round((passAA / total) * 100);
		var pctAAA = Math.round((passAAA / total) * 100);
		var aaClass  = pctAA === 100 ? 'sg-wcag-score-good' : (pctAA >= 70 ? 'sg-wcag-score-warn' : 'sg-wcag-score-bad');
		var aaaClass = pctAAA === 100 ? 'sg-wcag-score-good' : (pctAAA >= 70 ? 'sg-wcag-score-warn' : 'sg-wcag-score-bad');

		summary.innerHTML =
			'<div class="sg-wcag-score ' + aaClass + '">' +
				'<div class="sg-wcag-score-value">' + passAA + '/' + total + '</div>' +
				'<div class="sg-wcag-score-label">Godk\u00e4nd AA</div>' +
			'</div>' +
			'<div class="sg-wcag-score ' + aaaClass + '">' +
				'<div class="sg-wcag-score-value">' + passAAA + '/' + total + '</div>' +
				'<div class="sg-wcag-score-label">Godk\u00e4nd AAA</div>' +
			'</div>';
	}

	// Applicera WCAG-förslag via klick
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.sg-wcag-fix-btn');
		if (!btn || !parentCustomize) return;

		var settingId = btn.getAttribute('data-fix-setting');
		var color     = btn.getAttribute('data-fix-color');

		if (settingId && color && parentCustomize(settingId)) {
			parentCustomize(settingId).set(color);
		}
	});

	// Debounce-hjälpare — förhindrar överdriven omrendering
	var wcagTimer = null;
	function debouncedRenderWCAG() {
		if (wcagTimer) clearTimeout(wcagTimer);
		wcagTimer = setTimeout(renderWCAG, 100);
	}

	// Kör vid laddning
	renderWCAG();

	// Uppdatera vid färgändringar (lyssna på CSS-variabel-ändringar via MutationObserver på style)
	var wcagObserver = new MutationObserver(debouncedRenderWCAG);
	wcagObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['style'] });

	// Uppdatera även vid inline color-input-ändringar (med debounce)
	document.querySelectorAll('[data-color-input]').forEach(function (input) {
		input.addEventListener('input', debouncedRenderWCAG);
	});
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
	<?php
}
