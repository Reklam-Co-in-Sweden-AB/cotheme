<?php
/**
 * Mall för Beaver Themers innehållslayouter (arkiv, singular, 404 m.fl.).
 * Themers egen mall lägger layouten direkt i <body> utan <main>, så att
 * "Hoppa till innehåll" saknar mål och sidan saknar huvudlandmärke.
 * Kopplas in via filtret fl_theme_builder_template_include i functions.php.
 *
 * @package CoTheme
 */

get_header(); ?>

<main id="main-content" class="cotheme-content cotheme-themer-content">
	<?php FLThemeBuilderLayoutRenderer::render_content_layout(); ?>
</main>

<?php get_footer(); ?>
