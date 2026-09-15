<?php
/**
 * Skapa kundtema (child-tema) från adminpanelen
 *
 * Utseende → Skapa kundtema skapar ett child-tema till CoTheme med kundens
 * namn, mappnamn och logga (som screenshot.png i temalistan).
 *
 * Customizer-inställningarna (färger, typsnitt, logga, menyplaceringar)
 * sparas per aktivt tema och är kopplade till temats mappnamn. Därför kan
 * de kopieras från det aktiva temat till det nya, så att en sajt som kör
 * "CoTheme Child" kan byta till ett eget kundtema utan att tappa något.
 *
 * @package CoTheme
 */

defined('ABSPATH') || exit;

define('COTHEME_CHILD_TEMPLATE_DIR', COTHEME_DIR . '/inc/child-template');

// ==================================================================
// Admin-meny
// ==================================================================

add_action('admin_menu', function () {
	add_theme_page(
		'Skapa kundtema',
		'Skapa kundtema',
		'switch_themes',
		'cotheme-child-theme',
		'cotheme_render_child_theme_page'
	);
});

// ==================================================================
// Hantera formuläret
// ==================================================================

add_action('admin_post_cotheme_create_child_theme', function () {
	if (!current_user_can('switch_themes')) {
		wp_die('Du har inte behörighet att skapa teman.', '', ['response' => 403]);
	}

	check_admin_referer('cotheme_create_child_theme');

	$activate = !empty($_POST['cotheme_child_activate']);
	$result   = cotheme_create_child_theme([
		'name'          => sanitize_text_field(wp_unslash($_POST['cotheme_child_name'] ?? '')),
		'slug'          => sanitize_text_field(wp_unslash($_POST['cotheme_child_slug'] ?? '')),
		'logo_id'       => absint($_POST['cotheme_child_logo_id'] ?? 0),
		'logo_bg'       => sanitize_hex_color(wp_unslash($_POST['cotheme_child_logo_bg'] ?? '')) ?: '#ffffff',
		'copy_settings' => !empty($_POST['cotheme_child_copy_settings']),
		'activate'      => $activate,
	]);

	if (is_wp_error($result)) {
		cotheme_set_child_theme_notice('error', $result->get_error_message());
	} else {
		$theme   = wp_get_theme($result);
		$message = sprintf(
			'Kundtemat "%s" har skapats i mappen %s%s.',
			$theme->get('Name'),
			$result,
			$activate ? ' och är aktiverat' : ''
		);
		cotheme_set_child_theme_notice('success', $message);
	}

	wp_safe_redirect(admin_url('themes.php?page=cotheme-child-theme'));
	exit;
});

/**
 * Sparar ett meddelande som visas efter omdirigeringen.
 */
function cotheme_set_child_theme_notice(string $type, string $message): void {
	set_transient('cotheme_child_theme_notice_' . get_current_user_id(), [
		'type'    => $type,
		'message' => $message,
	], MINUTE_IN_SECONDS);
}

/**
 * Kontrollerar om kundteman kan skapas på den här sajten.
 *
 * @return string Felmeddelande, eller tom sträng om allt är OK.
 */
function cotheme_child_theme_blocker(): string {
	// install_themes saknas bl.a. när DISALLOW_FILE_MODS är satt
	if (!current_user_can('install_themes') || !wp_is_file_mod_allowed('cotheme_create_child_theme')) {
		return 'Sajten tillåter inte att teman installeras från adminpanelen (till exempel via DISALLOW_FILE_MODS). Skapa kundtemat för hand i stället.';
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	if (get_filesystem_method([], get_theme_root()) !== 'direct') {
		return 'Servern låter inte WordPress skriva direkt till temamappen. Skapa kundtemat för hand i stället.';
	}

	return '';
}

// ==================================================================
// Skapa temat
// ==================================================================

/**
 * Skapar ett child-tema till CoTheme.
 *
 * @param array $args name, slug, logo_id, logo_bg, copy_settings, activate
 * @return string|WP_Error Temats mappnamn, eller fel.
 */
function cotheme_create_child_theme(array $args) {
	$blocker = cotheme_child_theme_blocker();
	if ($blocker !== '') {
		return new WP_Error('cotheme_blocked', $blocker);
	}

	// Namnet hamnar i en CSS-kommentar — ta bort allt som kan avsluta den
	$name = trim(str_replace(['/*', '*/'], '', $args['name']));
	$name = mb_substr($name, 0, 60);
	if ($name === '') {
		return new WP_Error('cotheme_no_name', 'Ange kundens namn.');
	}

	// Ett ifyllt mappnamn används som det är. Är fältet tomt skapas det från
	// namnet — sanitize_title gör om å/ä/ö till a/o och mellanslag till bindestreck.
	$slug = $args['slug'] !== '' ? $args['slug'] : sanitize_title($name);
	if (!preg_match('/^[a-z0-9][a-z0-9-]{0,39}$/', $slug)) {
		return new WP_Error('cotheme_bad_slug', 'Mappnamnet får bara innehålla a–z, 0–9 och bindestreck (högst 40 tecken).');
	}

	wp_clean_themes_cache();
	$theme_dir = get_theme_root() . '/' . $slug;
	if ($slug === get_template() || wp_get_theme($slug)->exists() || file_exists($theme_dir)) {
		return new WP_Error('cotheme_exists', sprintf('Det finns redan ett tema med mappnamnet "%s". Välj ett annat.', $slug));
	}

	$logo_path = '';
	if ($args['logo_id']) {
		$allowed_mimes = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];
		if (!in_array(get_post_mime_type($args['logo_id']), $allowed_mimes, true)) {
			return new WP_Error('cotheme_bad_logo', 'Loggan måste vara en PNG-, JPG-, GIF- eller WebP-bild. SVG fungerar inte som temabild.');
		}
		$logo_path = (string) get_attached_file($args['logo_id']);
		if ($logo_path === '' || !is_readable($logo_path)) {
			return new WP_Error('cotheme_logo_missing', 'Loggans bildfil hittades inte på servern.');
		}
	}

	global $wp_filesystem;
	WP_Filesystem();

	if (!$wp_filesystem->mkdir($theme_dir, FS_CHMOD_DIR)) {
		return new WP_Error('cotheme_mkdir', 'Kunde inte skapa temamappen. Kontrollera skrivrättigheterna för wp-content/themes.');
	}

	$screenshot = $logo_path !== '' ? cotheme_build_child_screenshot($logo_path, $args['logo_bg']) : '';

	$written = $wp_filesystem->put_contents($theme_dir . '/style.css', cotheme_child_style_header($name, $slug), FS_CHMOD_FILE)
		&& $wp_filesystem->copy(COTHEME_CHILD_TEMPLATE_DIR . '/functions.php', $theme_dir . '/functions.php', false, FS_CHMOD_FILE)
		&& ($screenshot !== ''
			? $wp_filesystem->put_contents($theme_dir . '/screenshot.png', $screenshot, FS_CHMOD_FILE)
			: $wp_filesystem->copy(COTHEME_CHILD_TEMPLATE_DIR . '/screenshot.png', $theme_dir . '/screenshot.png', false, FS_CHMOD_FILE));

	if (!$written) {
		$wp_filesystem->rmdir($theme_dir, true);
		return new WP_Error('cotheme_write', 'Kunde inte skriva temafilerna. Inget tema skapades.');
	}

	wp_clean_themes_cache();

	if ($args['copy_settings']) {
		cotheme_copy_theme_settings(get_stylesheet(), $slug);
	}

	if ($args['activate']) {
		switch_theme($slug);
	}

	// CSS-variablerna cachas — bygg om dem utifrån det tema som nu är aktivt
	delete_transient('cotheme_head_css');

	return $slug;
}

/**
 * Bygger style.css-huvudet för kundtemat.
 */
function cotheme_child_style_header(string $name, string $slug): string {
	return "/*\n"
		. " Theme Name:   {$name}\n"
		. " Theme URI:    https://reklamco.se\n"
		. " Description:  Kundtema för {$name}, byggt på CoTheme.\n"
		. " Author:       Reklam & Co\n"
		. " Author URI:   https://reklamco.se\n"
		. " Template:     cotheme\n"
		. " Version:      1.0.0\n"
		. " Text Domain:  {$slug}\n"
		. "*/\n\n"
		. "/* Kundspecifik CSS skrivs nedan */\n";
}

/**
 * Kopierar Customizer-inställningar och Ytterligare CSS mellan två teman.
 *
 * Ytterligare CSS ligger som ett eget inlägg per tema. Det nya temat får en
 * egen kopia, annars skulle ändringar i det nya temat skriva över det gamlas.
 */
function cotheme_copy_theme_settings(string $from, string $to): void {
	$mods = get_option('theme_mods_' . $from, []);
	if (!is_array($mods)) {
		$mods = [];
	}
	unset($mods['custom_css_post_id']);

	$css_post = wp_get_custom_css_post($from);
	if ($css_post && $css_post->post_content !== '') {
		$new_css_post = wp_update_custom_css_post($css_post->post_content, [
			'stylesheet'   => $to,
			'preprocessed' => $css_post->post_content_filtered,
		]);
		if ($new_css_post instanceof WP_Post) {
			$mods['custom_css_post_id'] = $new_css_post->ID;
		}
	}

	update_option('theme_mods_' . $to, $mods);
}

/**
 * Lägger loggan centrerad på en 1200×900-yta (WordPress rekommenderade
 * storlek för temabilder).
 *
 * @return string PNG-data, eller tom sträng om bilden inte kunde skapas.
 */
function cotheme_build_child_screenshot(string $logo_path, string $background): string {
	if (!function_exists('imagecreatefromstring') || !function_exists('imagepng') || !wp_getimagesize($logo_path)) {
		return '';
	}

	$logo = imagecreatefromstring((string) file_get_contents($logo_path));
	if (!$logo) {
		return '';
	}

	// Palettbilder (t.ex. 8-bitars PNG) tappar annars transparensen
	if (!imageistruecolor($logo)) {
		imagepalettetotruecolor($logo);
	}

	$canvas_w = 1200;
	$canvas_h = 900;
	$canvas   = imagecreatetruecolor($canvas_w, $canvas_h);

	[$r, $g, $b] = sscanf(strlen($background) === 4
		? '#' . $background[1] . $background[1] . $background[2] . $background[2] . $background[3] . $background[3]
		: $background, '#%02x%02x%02x');
	imagefill($canvas, 0, 0, imagecolorallocate($canvas, $r, $g, $b));
	imagealphablending($canvas, true);

	// Loggan får ta upp högst 760×460 och skalas upp högst 4 gånger
	$logo_w = imagesx($logo);
	$logo_h = imagesy($logo);
	$scale  = min(760 / $logo_w, 460 / $logo_h, 4);
	$new_w  = max(1, (int) round($logo_w * $scale));
	$new_h  = max(1, (int) round($logo_h * $scale));

	imagecopyresampled(
		$canvas,
		$logo,
		(int) (($canvas_w - $new_w) / 2),
		(int) (($canvas_h - $new_h) / 2),
		0,
		0,
		$new_w,
		$new_h,
		$logo_w,
		$logo_h
	);

	ob_start();
	imagepng($canvas);
	return (string) ob_get_clean();
}

// ==================================================================
// Rendera admin-sidan
// ==================================================================

function cotheme_render_child_theme_page(): void {
	$blocker = cotheme_child_theme_blocker();
	$active  = wp_get_theme();

	$notice_key = 'cotheme_child_theme_notice_' . get_current_user_id();
	$notice     = get_transient($notice_key);
	delete_transient($notice_key);

	if ($blocker === '') {
		wp_enqueue_media();
	}
	?>
	<div class="wrap">
		<h1>Skapa kundtema</h1>
		<p>Skapar ett child-tema till CoTheme för en kund. Kundens egen CSS och egna funktioner läggs i kundtemat, medan CoTheme uppdateras centralt utan att skriva över dem.</p>

		<?php if (is_array($notice)) : ?>
			<div class="notice notice-<?php echo esc_attr($notice['type'] === 'success' ? 'success' : 'error'); ?> is-dismissible">
				<p>
					<?php echo esc_html($notice['message']); ?>
					<?php if ($notice['type'] === 'success') : ?>
						<a href="<?php echo esc_url(admin_url('themes.php')); ?>">Visa teman</a> ·
						<a href="<?php echo esc_url(admin_url('customize.php')); ?>">Öppna Customizern</a>
					<?php endif; ?>
				</p>
			</div>
		<?php endif; ?>

		<?php if ($blocker !== '') : ?>
			<div class="notice notice-warning inline"><p><?php echo esc_html($blocker); ?></p></div>
		<?php else : ?>

		<style>
			.cotheme-child-form {
				background: #fff;
				border: 1px solid #ddd;
				border-radius: 8px;
				padding: 24px;
				max-width: 640px;
				margin: 20px 0;
			}
			.cotheme-child-form .form-table th {
				width: 180px;
			}
			.cotheme-child-logo-preview {
				display: flex;
				align-items: center;
				justify-content: center;
				width: 240px;
				aspect-ratio: 4 / 3;
				max-width: 100%;
				margin-bottom: 8px;
				border: 1px solid #ddd;
				border-radius: 4px;
				background: #fff;
				color: #646970;
			}
			.cotheme-child-logo-preview img {
				max-width: 64%;
				max-height: 52%;
			}
		</style>

		<div class="cotheme-child-form">
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
				<?php wp_nonce_field('cotheme_create_child_theme'); ?>
				<input type="hidden" name="action" value="cotheme_create_child_theme">

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="cotheme_child_name">Kundens namn</label></th>
						<td>
							<input type="text" id="cotheme_child_name" name="cotheme_child_name" class="regular-text" maxlength="60" placeholder="T.ex. SMF" required>
							<p class="description">Visas som temats namn under Utseende → Teman.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="cotheme_child_slug">Mappnamn</label></th>
						<td>
							<input type="text" id="cotheme_child_slug" name="cotheme_child_slug" class="regular-text code" maxlength="40" pattern="[a-z0-9][a-z0-9\-]*" placeholder="smf" required aria-describedby="cotheme_child_slug_desc">
							<p class="description" id="cotheme_child_slug_desc">Små bokstäver a–z, siffror och bindestreck. Kan inte ändras efter att temat har aktiverats utan att inställningarna tappas.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Logga</th>
						<td>
							<div class="cotheme-child-logo-preview" id="cotheme_child_logo_preview">Ingen logga vald</div>
							<input type="hidden" id="cotheme_child_logo_id" name="cotheme_child_logo_id" value="">
							<button type="button" class="button" id="cotheme_child_logo_select">Välj logga</button>
							<button type="button" class="button-link" id="cotheme_child_logo_remove" hidden>Ta bort</button>
							<p class="description">Valfri. PNG, JPG, GIF eller WebP. Blir temats bild i temalistan.</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="cotheme_child_logo_bg">Bakgrund bakom loggan</label></th>
						<td>
							<input type="color" id="cotheme_child_logo_bg" name="cotheme_child_logo_bg" value="#ffffff">
							<p class="description">Välj en mörk färg om loggan är vit.</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Inställningar</th>
						<td>
							<fieldset>
								<label for="cotheme_child_copy_settings">
									<input type="checkbox" id="cotheme_child_copy_settings" name="cotheme_child_copy_settings" value="1" checked>
									Ta med inställningarna från det aktiva temat (<?php echo esc_html($active->get('Name')); ?>)
								</label>
								<p class="description">Färger, typsnitt, logga, menyplaceringar och Ytterligare CSS kopieras till kundtemat.</p>
								<br>
								<label for="cotheme_child_activate">
									<input type="checkbox" id="cotheme_child_activate" name="cotheme_child_activate" value="1" checked>
									Aktivera kundtemat direkt
								</label>
							</fieldset>
						</td>
					</tr>
				</table>

				<?php submit_button('Skapa kundtema'); ?>
			</form>
		</div>

		<script>
		(function () {
			'use strict';

			var nameInput = document.getElementById('cotheme_child_name');
			var slugInput = document.getElementById('cotheme_child_slug');
			var slugEdited = false;

			// Föreslå mappnamn utifrån kundens namn tills mappnamnet ändrats för hand
			function toSlug(value) {
				return value
					.toLowerCase()
					.replace(/[åä]/g, 'a')
					.replace(/ö/g, 'o')
					.normalize('NFD').replace(/[̀-ͯ]/g, '')
					.replace(/[^a-z0-9]+/g, '-')
					.replace(/^-+|-+$/g, '')
					.slice(0, 40);
			}

			nameInput.addEventListener('input', function () {
				if (!slugEdited) {
					slugInput.value = toSlug(nameInput.value);
				}
			});

			slugInput.addEventListener('input', function () {
				slugEdited = slugInput.value !== '';
			});

			// Media Uploader för loggan
			var preview   = document.getElementById('cotheme_child_logo_preview');
			var logoId    = document.getElementById('cotheme_child_logo_id');
			var removeBtn = document.getElementById('cotheme_child_logo_remove');
			var bgInput   = document.getElementById('cotheme_child_logo_bg');
			var frame;

			bgInput.addEventListener('input', function () {
				preview.style.background = bgInput.value;
			});

			document.getElementById('cotheme_child_logo_select').addEventListener('click', function () {
				if (!frame) {
					frame = wp.media({
						title: 'Välj logga',
						button: { text: 'Använd som logga' },
						multiple: false,
						library: { type: ['image/png', 'image/jpeg', 'image/gif', 'image/webp'] }
					});

					frame.on('select', function () {
						var attachment = frame.state().get('selection').first().toJSON();
						var img = document.createElement('img');
						img.src = attachment.url;
						img.alt = 'Förhandsvisning av logga';
						preview.replaceChildren(img);
						logoId.value = attachment.id;
						removeBtn.hidden = false;
					});
				}

				frame.open();
			});

			removeBtn.addEventListener('click', function () {
				logoId.value = '';
				preview.replaceChildren(document.createTextNode('Ingen logga vald'));
				removeBtn.hidden = true;
			});
		})();
		</script>

		<?php endif; ?>
	</div>
	<?php
}
