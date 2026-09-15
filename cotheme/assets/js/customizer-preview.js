/**
 * CoTheme Customizer — Live-förhandsgranskning
 *
 * Uppdaterar CSS custom properties i realtid.
 * Stöder det nya systemet med varumärkesfärger + tilldelning.
 */
(function (customize) {
	'use strict';

	var MAX_COLORS = 10;

	// Mappning: tilldelning → CSS custom property
	var assignmentMap = {
		cotheme_assign_primary:       '--cotheme-primary',
		cotheme_assign_primary_hover: '--cotheme-primary-hover',
		cotheme_assign_secondary:     '--cotheme-secondary',
		cotheme_assign_quote:         '--cotheme-quote',
		cotheme_assign_heading:       '--cotheme-heading',
		cotheme_assign_text:          '--cotheme-text',
		cotheme_assign_text_light:    '--cotheme-text-light',
		cotheme_assign_bg:            '--cotheme-bg',
		cotheme_assign_border:        '--cotheme-border',
		cotheme_assign_focus:         '--cotheme-focus'
	};

	/**
	 * Hämta aktuell hex-färg för en varumärkesfärgplats
	 */
	function getBrandColor(slot) {
		var setting = customize('cotheme_brand_color_' + slot);
		return setting ? setting.get() : '#cccccc';
	}

	/**
	 * Uppdatera en tilldelad CSS-variabel
	 */
	function updateAssignment(assignmentId) {
		var cssProp = assignmentMap[assignmentId];
		if (!cssProp) return;

		var slot = customize(assignmentId) ? customize(assignmentId).get() : '';
		if (slot) {
			var color = getBrandColor(slot);
			document.documentElement.style.setProperty(cssProp, color);
		}
	}

	/**
	 * Uppdatera alla tilldelningar (körs när en varumärkesfärg ändras)
	 */
	function updateAllAssignments() {
		Object.keys(assignmentMap).forEach(function (id) {
			updateAssignment(id);
		});
	}

	// Bind varumärkesfärger — när en ändras, uppdatera alla tilldelningar + brand-variabel
	for (var i = 1; i <= MAX_COLORS; i++) {
		(function (index) {
			customize('cotheme_brand_color_' + index, function (value) {
				value.bind(function (newVal) {
					document.documentElement.style.setProperty('--cotheme-brand-' + index, newVal);
					updateAllAssignments();
				});
			});
		})(i);
	}

	// Bind tilldelningar — när en ändras, uppdatera den CSS-variabeln
	Object.keys(assignmentMap).forEach(function (id) {
		customize(id, function (value) {
			value.bind(function () {
				updateAssignment(id);
			});
		});
	});

	// Container-bredd
	customize('cotheme_container_width', function (value) {
		value.bind(function (newVal) {
			document.documentElement.style.setProperty('--cotheme-container-width', newVal + 'px');
		});
	});

	// Brödtext — storlek och radavstånd
	var bodyProps = {
		cotheme_body_size_desktop: '--cotheme-body-size',
		cotheme_body_line_height:  '--cotheme-body-line-height'
	};

	Object.keys(bodyProps).forEach(function (id) {
		customize(id, function (value) {
			value.bind(function (newVal) {
				var val = id === 'cotheme_body_line_height' ? newVal : newVal + 'px';
				document.documentElement.style.setProperty(bodyProps[id], val);
			});
		});
	});

	// Rubriker — storlek H1–H6
	['h1', 'h2', 'h3', 'h4', 'h5', 'h6'].forEach(function (tag) {
		customize('cotheme_' + tag + '_size_desktop', function (value) {
			value.bind(function (newVal) {
				document.documentElement.style.setProperty('--cotheme-' + tag + '-size', newVal + 'px');
			});
		});
	});

})(wp.customize);
