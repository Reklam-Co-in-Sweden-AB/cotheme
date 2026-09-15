<?php
/**
 * Per-sida-toggles: Dolj header och/eller footer
 *
 * Lagger till en metabox med tva kryssrutor i redigeraren for sidor, inlagg och
 * BB Themer-layouts. Nar nagon ar ikryssad hoppar header.php/footer.php over
 * cotheme_render_header() / cotheme_render_footer().
 *
 * Anvandning: t.ex. for Power Pack Maintenance Mode-sidor, landing pages,
 * popup-sidor eller andra sidor som ska visas utan tema-chrome.
 *
 * @package CoTheme
 */

defined('ABSPATH') || exit;

const COTHEME_META_HIDE_HEADER = '_cotheme_hide_header';
const COTHEME_META_HIDE_FOOTER = '_cotheme_hide_footer';
const COTHEME_LAYOUT_NONCE     = 'cotheme_layout_nonce';

/**
 * Post types som ska fa metaboxen.
 * Filter: cotheme_layout_post_types
 */
function cotheme_layout_post_types(): array {
	return apply_filters('cotheme_layout_post_types', ['page', 'post']);
}

/**
 * Registrera metaboxen
 */
add_action('add_meta_boxes', function () {
	foreach (cotheme_layout_post_types() as $post_type) {
		add_meta_box(
			'cotheme_layout',
			__('Sidlayout', 'cotheme'),
			'cotheme_render_layout_metabox',
			$post_type,
			'side',
			'default'
		);
	}
});

/**
 * Rendera metaboxens innehall
 */
function cotheme_render_layout_metabox(WP_Post $post): void {
	wp_nonce_field(COTHEME_LAYOUT_NONCE, COTHEME_LAYOUT_NONCE);

	$hide_header = (bool) get_post_meta($post->ID, COTHEME_META_HIDE_HEADER, true);
	$hide_footer = (bool) get_post_meta($post->ID, COTHEME_META_HIDE_FOOTER, true);
	?>
	<p style="margin:0 0 8px;">
		<label>
			<input type="checkbox" name="<?php echo esc_attr(COTHEME_META_HIDE_HEADER); ?>" value="1" <?php checked($hide_header); ?>>
			<?php esc_html_e('Dölj header på denna sida', 'cotheme'); ?>
		</label>
	</p>
	<p style="margin:0 0 8px;">
		<label>
			<input type="checkbox" name="<?php echo esc_attr(COTHEME_META_HIDE_FOOTER); ?>" value="1" <?php checked($hide_footer); ?>>
			<?php esc_html_e('Dölj footer på denna sida', 'cotheme'); ?>
		</label>
	</p>
	<p class="description" style="margin:8px 0 0;">
		<?php esc_html_e('Användbart för t.ex. maintenance mode, landing pages eller popup-sidor.', 'cotheme'); ?>
	</p>
	<?php
}

/**
 * Spara meta vid save_post
 */
add_action('save_post', function (int $post_id, WP_Post $post): void {
	if (!isset($_POST[COTHEME_LAYOUT_NONCE]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[COTHEME_LAYOUT_NONCE])), COTHEME_LAYOUT_NONCE)) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (!in_array($post->post_type, cotheme_layout_post_types(), true)) {
		return;
	}
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}

	foreach ([COTHEME_META_HIDE_HEADER, COTHEME_META_HIDE_FOOTER] as $key) {
		if (!empty($_POST[$key])) {
			update_post_meta($post_id, $key, 1);
		} else {
			delete_post_meta($post_id, $key);
		}
	}
}, 10, 2);

/**
 * Helper: ska header doljas for aktuell vy?
 */
function cotheme_should_hide_header(): bool {
	if (!is_singular()) {
		return false;
	}
	return (bool) get_post_meta(get_queried_object_id(), COTHEME_META_HIDE_HEADER, true);
}

/**
 * Helper: ska footer doljas for aktuell vy?
 */
function cotheme_should_hide_footer(): bool {
	if (!is_singular()) {
		return false;
	}
	return (bool) get_post_meta(get_queried_object_id(), COTHEME_META_HIDE_FOOTER, true);
}

/**
 * Lagg till body-klasser nar header/footer ar gomda
 */
add_filter('body_class', function (array $classes): array {
	if (cotheme_should_hide_header()) {
		$classes[] = 'cotheme-no-header';
	}
	if (cotheme_should_hide_footer()) {
		$classes[] = 'cotheme-no-footer';
	}
	return $classes;
});
