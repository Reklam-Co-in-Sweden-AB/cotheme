<?php
/**
 * Temauppdateringar från GitHub
 *
 * Temat kollar efter nya releases i Reklam-Co-in-Sweden-AB/cotheme var
 * 12:e timme och visar "Uppdatering finns" under Utseende → Teman, precis
 * som för teman från wordpress.org. Bygger på biblioteket Plugin Update
 * Checker (inc/lib/plugin-update-checker, MIT-licens).
 *
 * Endast release-filen cotheme.zip får användas som uppdatering. GitHubs
 * automatiska källkods-zip har repots rotstruktur (cotheme/ och
 * cotheme-child/ i en mapp som heter cotheme-<tag>) och skulle förstöra
 * temat om WordPress installerade den.
 *
 * @package CoTheme
 */

defined('ABSPATH') || exit;

require_once COTHEME_DIR . '/inc/lib/plugin-update-checker/plugin-update-checker.php';

$cotheme_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/Reklam-Co-in-Sweden-AB/cotheme/',
	COTHEME_DIR . '/style.css',
	'cotheme'
);

// Kräv release-filen cotheme.zip — fall aldrig tillbaka på källkods-zippen
$cotheme_update_checker->getVcsApi()->enableReleaseAssets(
	'/^cotheme\.zip$/i',
	\YahnisElsts\PluginUpdateChecker\v5p7\Vcs\Api::REQUIRE_RELEASE_ASSETS
);

// Använd bara publicerade releases. Annars faller biblioteket tillbaka på
// senaste taggen eller main-branchen, som båda ger källkods-zippen.
add_filter('puc_vcs_update_detection_strategies_theme-cotheme', function ($strategies) {
	$latest_release = \YahnisElsts\PluginUpdateChecker\v5p7\Vcs\Api::STRATEGY_LATEST_RELEASE;
	return array_intersect_key($strategies, [$latest_release => true]);
});
