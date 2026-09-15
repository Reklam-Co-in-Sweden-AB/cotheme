/**
 * CoTheme Customizer — Kontroller
 *
 * Navigerar preview till styleguide, visar/döljer typsnittsfält,
 * hanterar dynamiskt antal varumärkesfärger, palett-presets,
 * färgtilldelningscirklar och paletthantering (spara/ta bort).
 */
(function (customize) {
	'use strict';

	var MAX_COLORS = 10;

	// =====================================================================
	// Typsnitt: visa/dölj baserat på källa
	// =====================================================================

	function bindFontSourceVisibility(context) {
		var sourceId = 'cotheme_font_' + context + '_source';

		customize.control(sourceId, function (control) {
			var updateVisibility = function (source) {
				var controls = {
					system: 'cotheme_font_' + context + '_system',
					google: 'cotheme_font_' + context + '_google',
					adobe:  'cotheme_font_' + context + '_adobe',
					custom: 'cotheme_font_' + context + '_custom'
				};

				Object.keys(controls).forEach(function (key) {
					customize.control(controls[key], function (ctrl) {
						ctrl.container.toggle(source === key);
					});
				});
			};

			control.setting.bind(updateVisibility);
			updateVisibility(control.setting.get());
		});
	}

	// =====================================================================
	// Varumärkesfärger: visa/dölj baserat på antal
	// =====================================================================

	function bindBrandColorVisibility() {
		function updateVisibility() {
			var countSetting = customize('cotheme_brand_color_count');
			var count = countSetting ? parseInt(countSetting.get(), 10) || 3 : 3;

			for (var i = 1; i <= MAX_COLORS; i++) {
				var show = i <= count;
				var colorEl = document.querySelector('[id*="customize-control-cotheme_brand_color_' + i + '"]:not([id*="_name"])');
				var nameEl  = document.querySelector('[id*="customize-control-cotheme_brand_color_' + i + '_name"]');

				if (colorEl) colorEl.style.display = show ? '' : 'none';
				if (nameEl) nameEl.style.display = show ? '' : 'none';
			}

			// Uppdatera synlighet för färgtilldelningscirklar
			updateAssignmentCircleVisibility(count);
		}

		customize('cotheme_brand_color_count', function (setting) {
			setting.bind(updateVisibility);
		});

		// Kör vid start med fördröjning så DOM:en hinner laddas
		setTimeout(updateVisibility, 300);
	}

	// =====================================================================
	// Färgtilldelningscirklar: synlighet baserat på antal
	// =====================================================================

	function updateAssignmentCircleVisibility(count) {
		document.querySelectorAll('.cotheme-assign-circle').forEach(function (circle) {
			var slot = parseInt(circle.getAttribute('data-slot'), 10);
			circle.style.display = slot <= count ? '' : 'none';
		});
	}

	// =====================================================================
	// Färgtilldelningscirklar: klick → välj färg
	// =====================================================================

	function bindColorAssignmentCircles() {
		document.addEventListener('click', function (e) {
			var circle = e.target.closest('.cotheme-assign-circle');
			if (!circle) return;

			var control = circle.closest('.cotheme-assign-control');
			if (!control) return;

			var settingId = control.getAttribute('data-setting');
			var slot = circle.getAttribute('data-slot');

			// Uppdatera Customizer-setting
			if (customize(settingId)) {
				customize(settingId).set(slot);
			}

			// Uppdatera visuell markering
			control.querySelectorAll('.cotheme-assign-circle').forEach(function (c) {
				c.classList.remove('active');
			});
			circle.classList.add('active');

			// Uppdatera dolt fält
			var hiddenInput = control.querySelector('input[type="hidden"]');
			if (hiddenInput) {
				hiddenInput.value = slot;
			}
		});
	}

	// =====================================================================
	// Färgtilldelningscirklar: synka bakgrundsfärg i realtid
	// =====================================================================

	function bindAssignmentCircleColorSync() {
		for (var i = 1; i <= MAX_COLORS; i++) {
			(function (index) {
				customize('cotheme_brand_color_' + index, function (setting) {
					setting.bind(function (newColor) {
						// Uppdatera alla cirklar som refererar till denna färgplats
						document.querySelectorAll('.cotheme-assign-circle[data-slot="' + index + '"]').forEach(function (circle) {
							circle.style.background = newColor;
						});
					});
				});
			})(i);
		}
	}

	// =====================================================================
	// Palett-presets: klick → applicera färger
	// =====================================================================

	function bindPalettePresets() {
		document.addEventListener('click', function (e) {
			// Hantera både vanliga preset-knappar och sparade paletter
			var btn = e.target.closest('.cotheme-preset-btn:not(.cotheme-saved-palette)') ||
			          e.target.closest('.cotheme-preset-btn-inner');
			if (!btn) return;

			// Ignorera om klick på delete-knapp
			if (e.target.closest('.cotheme-delete-palette')) return;

			var colorsAttr = btn.getAttribute('data-colors');
			var countAttr  = btn.getAttribute('data-count');
			if (!colorsAttr || !countAttr) return;

			var colors;
			try { colors = JSON.parse(colorsAttr); } catch (err) { return; }
			var count  = parseInt(countAttr, 10);

			// Uppdatera antal
			customize('cotheme_brand_color_count').set(count);

			// Uppdatera varje färg
			colors.forEach(function (color, index) {
				var settingId = 'cotheme_brand_color_' + (index + 1);
				if (customize(settingId)) {
					customize(settingId).set(color);
				}
			});

			// Markera aktiv preset
			document.querySelectorAll('.cotheme-preset-btn, .cotheme-preset-btn-inner').forEach(function (b) {
				b.classList.remove('active');
			});
			btn.classList.add('active');
		});
	}

	// =====================================================================
	// Palett: spara aktuell
	// =====================================================================

	function bindSavePalette() {
		document.addEventListener('click', function (e) {
			if (!e.target.matches('#cotheme-save-palette-btn')) return;

			var name = prompt('Namnge din palett:');
			if (!name || !name.trim()) return;

			var countSetting = customize('cotheme_brand_color_count');
			var count = countSetting ? parseInt(countSetting.get(), 10) || 3 : 3;
			var colors = [];

			for (var i = 1; i <= count; i++) {
				var setting = customize('cotheme_brand_color_' + i);
				colors.push(setting ? setting.get() : '#cccccc');
			}

			var data = new FormData();
			data.append('action', 'cotheme_save_palette');
			data.append('nonce', cothemeCustomizer.nonce);
			data.append('name', name.trim());
			data.append('count', count);
			colors.forEach(function (c) {
				data.append('colors[]', c);
			});

			fetch(cothemeCustomizer.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: data
			})
			.then(function (r) {
				if (!r.ok) throw new Error(r.status);
				return r.json();
			})
			.then(function (resp) {
				if (resp.success) {
					renderSavedPalettes(resp.data.palettes);
				}
			})
			.catch(function () {});
		});
	}

	// =====================================================================
	// Palett: ta bort sparad
	// =====================================================================

	function bindDeletePalette() {
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.cotheme-delete-palette');
			if (!btn) return;
			e.stopPropagation();

			var key = btn.getAttribute('data-key');
			if (!confirm('Ta bort denna palett?')) return;

			var data = new FormData();
			data.append('action', 'cotheme_delete_palette');
			data.append('nonce', cothemeCustomizer.nonce);
			data.append('key', key);

			fetch(cothemeCustomizer.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: data
			})
			.then(function (r) {
				if (!r.ok) throw new Error(r.status);
				return r.json();
			})
			.then(function (resp) {
				if (resp.success) {
					renderSavedPalettes(resp.data.palettes);
				}
			})
			.catch(function () {});
		});
	}

	// =====================================================================
	// Rendera sparade paletter (uppdaterar DOM efter spara/ta bort)
	// =====================================================================

	function renderSavedPalettes(palettes) {
		var container = document.getElementById('cotheme-saved-palettes');
		var titleEl = container ? container.previousElementSibling : null;

		// Ta bort befintlig container och titel
		if (container) container.remove();
		if (titleEl && titleEl.classList.contains('customize-control-title')) titleEl.remove();

		if (!palettes || palettes.length === 0) return;

		// Hitta rätt plats (efter presets-grid)
		var presetsGrid = document.querySelector('.cotheme-presets-grid');
		if (!presetsGrid) return;

		// Skapa titel
		var title = document.createElement('span');
		title.className = 'customize-control-title';
		title.style.marginTop = '16px';
		title.textContent = 'Sparade paletter';

		// Skapa grid
		var grid = document.createElement('div');
		grid.className = 'cotheme-presets-grid';
		grid.id = 'cotheme-saved-palettes';

		palettes.forEach(function (palette, index) {
			var wrapper = document.createElement('div');
			wrapper.className = 'cotheme-preset-btn cotheme-saved-palette';
			wrapper.style.position = 'relative';

			var innerBtn = document.createElement('button');
			innerBtn.type = 'button';
			innerBtn.className = 'cotheme-preset-btn-inner';
			innerBtn.setAttribute('data-colors', JSON.stringify(palette.colors));
			innerBtn.setAttribute('data-count', palette.count);
			innerBtn.style.cssText = 'background:none;border:none;padding:0;cursor:pointer;width:100%;';

			var swatches = document.createElement('span');
			swatches.className = 'cotheme-preset-swatches';
			palette.colors.forEach(function (c) {
				var s = document.createElement('span');
				s.style.background = c;
				swatches.appendChild(s);
			});

			var nameSpan = document.createElement('span');
			nameSpan.className = 'cotheme-preset-name';
			nameSpan.textContent = palette.name;

			innerBtn.appendChild(swatches);
			innerBtn.appendChild(nameSpan);

			var deleteBtn = document.createElement('button');
			deleteBtn.type = 'button';
			deleteBtn.className = 'cotheme-delete-palette';
			deleteBtn.setAttribute('data-key', palette.id || index);
			deleteBtn.title = 'Ta bort';
			deleteBtn.innerHTML = '&times;';

			wrapper.appendChild(innerBtn);
			wrapper.appendChild(deleteBtn);
			grid.appendChild(wrapper);
		});

		presetsGrid.after(title);
		title.after(grid);
	}

	// =====================================================================
	// Panel-navigering: visa styleguide i preview
	// =====================================================================

	function bindPanelNavigation() {
		var previousUrl = null;

		customize.panel('cotheme_panel', function (panel) {
			panel.expanded.bind(function (isExpanded) {
				if (isExpanded) {
					previousUrl = customize.previewer.previewUrl.get();
					var homeUrl = customize.settings.url.home || window.location.origin;
					customize.previewer.previewUrl.set(homeUrl + '?cotheme-styleguide=1');
				} else if (previousUrl) {
					customize.previewer.previewUrl.set(previousUrl);
					previousUrl = null;
				}
			});
		});
	}

	// =====================================================================
	// Init
	// =====================================================================

	customize.bind('ready', function () {
		bindFontSourceVisibility('heading');
		bindFontSourceVisibility('body');
		bindBrandColorVisibility();
		bindPalettePresets();
		bindPanelNavigation();
		bindColorAssignmentCircles();
		bindAssignmentCircleColorSync();
		bindSavePalette();
		bindDeletePalette();
	});

})(wp.customize);
