<?php
/**
 * Admin-sida för uppladdning av egna typsnitt
 *
 * @package CoTheme
 */

defined('ABSPATH') || exit;

// ==================================================================
// Admin-meny
// ==================================================================

add_action('admin_menu', function () {
	add_theme_page(
		'Egna typsnitt',
		'Egna typsnitt',
		'manage_options',
		'cotheme-custom-fonts',
		'cotheme_render_custom_fonts_page'
	);
});

// ==================================================================
// Hantera formulärinlämningar
// ==================================================================

add_action('admin_init', function () {
	if (!isset($_POST['cotheme_custom_font_action'])) {
		return;
	}

	if (!current_user_can('manage_options')) {
		return;
	}

	$action = sanitize_text_field(wp_unslash($_POST['cotheme_custom_font_action']));

	// Lägg till typsnitt
	if ($action === 'add') {
		check_admin_referer('cotheme_add_custom_font');

		$font_name = sanitize_text_field(wp_unslash($_POST['cotheme_font_name'] ?? ''));
		if (empty($font_name)) {
			add_settings_error('cotheme_custom_fonts', 'no_name', 'Ange ett namn för typsnittet.', 'error');
			return;
		}

		$variants  = [];
		$weights   = array_map('sanitize_text_field', wp_unslash($_POST['cotheme_font_weight'] ?? []));
		$styles    = array_map('sanitize_text_field', wp_unslash($_POST['cotheme_font_style'] ?? []));
		$file_ids  = array_map('absint', wp_unslash($_POST['cotheme_font_file_id'] ?? []));
		$file_urls = array_map('esc_url_raw', wp_unslash($_POST['cotheme_font_file_url'] ?? []));

		foreach ($file_ids as $i => $file_id) {
			if (empty($file_id) && empty($file_urls[$i])) {
				continue;
			}

			$variants[] = [
				'attachment_id' => $file_id,
				'url'           => $file_urls[$i] ?? '',
				'weight'        => $weights[$i] ?? '400',
				'style'         => $styles[$i] ?? 'normal',
			];
		}

		if (empty($variants)) {
			add_settings_error('cotheme_custom_fonts', 'no_files', 'Ladda upp minst en typsnittsfil.', 'error');
			return;
		}

		cotheme_save_custom_font($font_name, $variants);
		add_settings_error('cotheme_custom_fonts', 'font_added', sprintf('Typsnittet "%s" har lagts till.', esc_html($font_name)), 'success');
	}

	// Ta bort typsnitt
	if ($action === 'delete') {
		check_admin_referer('cotheme_delete_custom_font');

		$font_name = sanitize_text_field(wp_unslash($_POST['cotheme_delete_font_name'] ?? ''));
		if ($font_name) {
			cotheme_delete_custom_font($font_name);
			add_settings_error('cotheme_custom_fonts', 'font_deleted', sprintf('Typsnittet "%s" har tagits bort.', esc_html($font_name)), 'success');
		}
	}
});

// ==================================================================
// Tillåt uppladdning av typsnittsfiler
// ==================================================================

add_filter('upload_mimes', function ($mimes) {
	// Begränsa typsnittsuppladdning till administratörer
	if (!current_user_can('manage_options')) {
		return $mimes;
	}

	$mimes['woff']  = 'font/woff';
	$mimes['woff2'] = 'font/woff2';
	$mimes['ttf']   = 'font/ttf';
	$mimes['otf']   = 'font/otf';
	return $mimes;
});

add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename) {
	// Begränsa till administratörer
	if (!current_user_can('manage_options')) {
		return $data;
	}

	$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	$font_types = [
		'woff'  => 'font/woff',
		'woff2' => 'font/woff2',
		'ttf'   => 'font/ttf',
		'otf'   => 'font/otf',
	];

	if (!isset($font_types[$ext])) {
		return $data;
	}

	// Verifiera filinnehållet via magic bytes
	$valid = false;
	if (is_readable($file)) {
		$header = file_get_contents($file, false, null, 0, 4);
		if ($header !== false) {
			switch ($ext) {
				case 'woff':
					// WOFF magic: "wOFF"
					$valid = (substr($header, 0, 4) === 'wOFF');
					break;
				case 'woff2':
					// WOFF2 magic: "wOF2"
					$valid = (substr($header, 0, 4) === 'wOF2');
					break;
				case 'ttf':
					// TrueType magic: 0x00010000 eller "true"
					$valid = (
						$header === "\x00\x01\x00\x00" ||
						substr($header, 0, 4) === 'true'
					);
					break;
				case 'otf':
					// OpenType magic: "OTTO"
					$valid = (substr($header, 0, 4) === 'OTTO');
					break;
			}
		}
	}

	if ($valid) {
		$data['ext']  = $ext;
		$data['type'] = $font_types[$ext];
	}

	return $data;
}, 10, 3);

// ==================================================================
// Rendera admin-sidan
// ==================================================================

function cotheme_render_custom_fonts_page(): void {
	$custom_fonts = cotheme_get_custom_fonts();

	// Ladda WordPress Media Uploader
	wp_enqueue_media();
	?>
	<div class="wrap">
		<h1>Egna typsnitt</h1>
		<p>Ladda upp egna typsnittsfiler (WOFF2, WOFF, TTF, OTF) för användning i temat.</p>

		<?php settings_errors('cotheme_custom_fonts'); ?>

		<style>
			.cotheme-fonts-grid {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
				gap: 20px;
				margin: 20px 0;
			}
			.cotheme-font-card {
				background: #fff;
				border: 1px solid #ddd;
				border-radius: 8px;
				padding: 20px;
			}
			.cotheme-font-card h3 {
				margin-top: 0;
				font-size: 1.2em;
			}
			.cotheme-font-preview {
				font-size: 1.5em;
				padding: 15px 0;
				border-bottom: 1px solid #eee;
				margin-bottom: 10px;
			}
			.cotheme-font-variant {
				font-size: 13px;
				color: #666;
				padding: 2px 0;
			}
			.cotheme-add-font-form {
				background: #fff;
				border: 1px solid #ddd;
				border-radius: 8px;
				padding: 24px;
				max-width: 600px;
				margin: 20px 0;
			}
			.cotheme-variant-row {
				display: flex;
				gap: 10px;
				align-items: center;
				margin-bottom: 10px;
				padding: 10px;
				background: #f9f9f9;
				border-radius: 4px;
			}
			.cotheme-variant-row select {
				width: 120px;
			}
			.cotheme-variant-row .button {
				white-space: nowrap;
			}
			#cotheme-variants-container .cotheme-variant-row .cotheme-remove-variant {
				color: #a00;
				cursor: pointer;
				font-size: 18px;
				padding: 0 5px;
			}
		</style>

		<?php if (!empty($custom_fonts)) : ?>
			<h2>Uppladdade typsnitt</h2>
			<div class="cotheme-fonts-grid">
				<?php foreach ($custom_fonts as $name => $variants) : ?>
					<div class="cotheme-font-card">
						<h3><?php echo esc_html($name); ?></h3>
						<div class="cotheme-font-preview" style="font-family: '<?php echo esc_attr($name); ?>', sans-serif;">
							Aa Bb Cc Dd Ee Ff Gg
						</div>
						<?php foreach ($variants as $variant) : ?>
							<div class="cotheme-font-variant">
								Vikt: <?php echo esc_html($variant['weight']); ?>,
								Stil: <?php echo esc_html($variant['style']); ?>
							</div>
						<?php endforeach; ?>
						<form method="post" style="margin-top: 10px;">
							<?php wp_nonce_field('cotheme_delete_custom_font'); ?>
							<input type="hidden" name="cotheme_custom_font_action" value="delete">
							<input type="hidden" name="cotheme_delete_font_name" value="<?php echo esc_attr($name); ?>">
							<button type="submit" class="button" onclick="return confirm('Vill du verkligen ta bort typsnittet \'<?php echo esc_js($name); ?>\'?');">
								Ta bort
							</button>
						</form>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<h2>Lägg till nytt typsnitt</h2>
		<div class="cotheme-add-font-form">
			<form method="post">
				<?php wp_nonce_field('cotheme_add_custom_font'); ?>
				<input type="hidden" name="cotheme_custom_font_action" value="add">

				<p>
					<label for="cotheme_font_name"><strong>Typsnittsnamn</strong></label><br>
					<input type="text" id="cotheme_font_name" name="cotheme_font_name" class="regular-text" placeholder="T.ex. Min Font" required>
				</p>

				<p><strong>Varianter</strong></p>
				<p class="description">Ladda upp en fil per vikt/stil-kombination.</p>

				<div id="cotheme-variants-container">
					<div class="cotheme-variant-row">
						<select name="cotheme_font_weight[]">
							<option value="300">Light (300)</option>
							<option value="400" selected>Regular (400)</option>
							<option value="500">Medium (500)</option>
							<option value="600">Semi Bold (600)</option>
							<option value="700">Bold (700)</option>
							<option value="800">Extra Bold (800)</option>
							<option value="900">Black (900)</option>
						</select>
						<select name="cotheme_font_style[]">
							<option value="normal">Normal</option>
							<option value="italic">Italic</option>
						</select>
						<input type="hidden" name="cotheme_font_file_id[]" class="cotheme-font-file-id" value="">
						<input type="hidden" name="cotheme_font_file_url[]" class="cotheme-font-file-url" value="">
						<span class="cotheme-font-filename">Ingen fil vald</span>
						<button type="button" class="button cotheme-upload-font-btn">Välj fil</button>
					</div>
				</div>

				<p style="margin-top: 10px;">
					<button type="button" class="button" id="cotheme-add-variant-btn">+ Lägg till variant</button>
				</p>

				<p style="margin-top: 20px;">
					<?php submit_button('Spara typsnitt', 'primary', 'submit', false); ?>
				</p>
			</form>
		</div>
	</div>

	<script>
	(function () {
		'use strict';

		// Lägg till ny variant-rad
		document.getElementById('cotheme-add-variant-btn').addEventListener('click', function () {
			var container = document.getElementById('cotheme-variants-container');
			var firstRow = container.querySelector('.cotheme-variant-row');
			var newRow = firstRow.cloneNode(true);

			// Återställ värden
			newRow.querySelector('.cotheme-font-file-id').value = '';
			newRow.querySelector('.cotheme-font-file-url').value = '';
			newRow.querySelector('.cotheme-font-filename').textContent = 'Ingen fil vald';

			// Lägg till ta bort-knapp
			var removeBtn = document.createElement('span');
			removeBtn.className = 'cotheme-remove-variant';
			removeBtn.textContent = '×';
			removeBtn.title = 'Ta bort variant';
			removeBtn.addEventListener('click', function () {
				newRow.remove();
			});
			newRow.appendChild(removeBtn);

			container.appendChild(newRow);
		});

		// Media Uploader för typsnittsfiler
		document.addEventListener('click', function (e) {
			if (!e.target.classList.contains('cotheme-upload-font-btn')) {
				return;
			}

			e.preventDefault();
			var row = e.target.closest('.cotheme-variant-row');

			var frame = wp.media({
				title: 'Välj typsnittsfil',
				button: { text: 'Välj fil' },
				multiple: false,
				library: {
					type: ['font/woff', 'font/woff2', 'font/ttf', 'font/otf', 'application/x-font-woff', 'application/x-font-woff2', 'application/x-font-ttf', 'application/x-font-otf']
				}
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				row.querySelector('.cotheme-font-file-id').value = attachment.id;
				row.querySelector('.cotheme-font-file-url').value = attachment.url;
				row.querySelector('.cotheme-font-filename').textContent = attachment.filename;
			});

			frame.open();
		});
	})();
	</script>
	<?php
}
