<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class('cotheme-site'); ?>>
<?php wp_body_open(); ?>
<?php
// Kodsnutt: body-start (t.ex. GTM noscript)
// Saneras vid sparning (cotheme_sanitize_code_snippet) — renderas för alla.
$cotheme_body_snippet = get_theme_mod('cotheme_snippet_body_open', '');
if (!empty($cotheme_body_snippet)) {
	echo $cotheme_body_snippet;
}
?>
<a class="cotheme-skip-link screen-reader-text" href="#main-content"><?php esc_html_e('Hoppa till innehåll', 'cotheme'); ?></a>

<?php if (!cotheme_should_hide_header()) : ?>
	<?php cotheme_render_header(); ?>
<?php endif; ?>
