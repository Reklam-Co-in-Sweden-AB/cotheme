/**
 * CoTheme — Footer reveal
 *
 * Mäter footerns faktiska höjd och skriver den till CSS-variabeln
 * --cotheme-footer-h på <body>. Variabeln används både för footerns
 * höjd och som margin-bottom på <main>, så reveal-effekten håller
 * sig responsiv när footer-innehållet ändrar höjd vid breakpoints.
 */
(function () {
	'use strict';

	var body = document.body;
	if (!body || !body.classList.contains('has-footer-reveal')) {
		return;
	}

	var footer = document.querySelector('.cotheme-footer--themer, .cotheme-footer');
	if (!footer) {
		return;
	}

	function setHeight() {
		// Math.floor istället för ceil: marginalen får aldrig vara större än
		// footerns faktiska höjd, annars uppstår en 1px-glipa där body-bg
		// syns igenom mellan sista sektionen och footern.
		var h = Math.floor(footer.getBoundingClientRect().height);
		if (h > 0) {
			body.style.setProperty('--cotheme-footer-h', h + 'px');
		}
	}

	// Initial mätning så fort fonter och bilder laddats
	if (document.readyState === 'complete') {
		setHeight();
	} else {
		window.addEventListener('load', setHeight);
	}

	// Mät om vid resize (debouncad så vi inte hamrar layouten)
	var resizeTimer = null;
	window.addEventListener('resize', function () {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(setHeight, 120);
	});

	// Mät om om footerns innehåll ändrar storlek (t.ex. dynamiska element)
	if (typeof ResizeObserver !== 'undefined') {
		var ro = new ResizeObserver(setHeight);
		ro.observe(footer);
	}
})();
