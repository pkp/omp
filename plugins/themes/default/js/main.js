/**
 * @file plugins/themes/default/js/main.js
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2000-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Handle JavaScript functionality unique to this theme.
 */
(function($) {

	// Initialize dropdown navigation menus on large screens
	// See bootstrap dropdowns: https://getbootstrap.com/docs/4.0/components/dropdowns/
	if (typeof $.fn.dropdown !== 'undefined') {
		var $nav = $('#navigationPrimary, #navigationUser'),
		$submenus = $('ul', $nav);
		function toggleDropdowns() {
			if (window.innerWidth > 992) {
				$submenus.each(function(i) {
					var id = 'pkpDropdown' + i;
					$(this)
						.addClass('dropdown-menu')
						.attr('aria-labelledby', id);
					$(this).siblings('a')
						.attr('data-toggle', 'dropdown')
						.attr('aria-haspopup', true)
						.attr('aria-expanded', false)
						.attr('id', id)
						.attr('href', '#');
				});
				$('[data-toggle="dropdown"]').dropdown();

			} else {
				$('[data-toggle="dropdown"]').dropdown('dispose');
				$submenus.each(function(i) {
					$(this)
						.removeClass('dropdown-menu')
						.removeAttr('aria-labelledby');
					$(this).siblings('a')
						.removeAttr('data-toggle')
						.removeAttr('aria-haspopup')
						.removeAttr('aria-expanded',)
						.removeAttr('id')
						.attr('href', '#');
				});
			}
		}
		window.onresize = toggleDropdowns;
		$().ready(function() {
			toggleDropdowns();
		});
	}

	// Toggle nav menu on small screens
	$('.pkp_site_nav_toggle').click(function(e) {
		$('.pkp_site_nav_menu').toggleClass('pkp_site_nav_menu--isOpen');
		$('.pkp_site_nav_toggle').toggleClass('pkp_site_nav_toggle--transform');
	});

	// Modify the Chart.js display options used by UsageStats plugin
	document.addEventListener('usageStatsChartOptions.pkp', function(e) {
		e.chartOptions.elements.line.backgroundColor = 'rgba(0, 122, 178, 0.6)';
		e.chartOptions.elements.bar.backgroundColor = 'rgba(0, 122, 178, 0.6)';
	});

	// Show or hide the reviewer interests field on the registration form
	// when a user has opted to register as a reviewer.
	function reviewerInterestsToggle() {
		var is_checked = false;
		$('#reviewerOptinGroup').find('input').each(function() {
			if ($(this).is(':checked')) {
				is_checked = true;
				return false;
			}
		});
		if (is_checked) {
			$('#reviewerInterests').addClass('is_visible');
		} else {
			$('#reviewerInterests').removeClass('is_visible');
		}
	}

	reviewerInterestsToggle();
	$('#reviewerOptinGroup input').on('click', reviewerInterestsToggle);

	var swiper = new Swiper('.swiper', {
		ally: {
			prevSlideMessage: pkpDefaultThemeI18N.prevSlide,
			nextSlideMessage: pkpDefaultThemeI18N.nextSlide,
		},
		autoHeight: true,
		navigation: {
			nextEl: '.swiper-button-next',
			prevEl: '.swiper-button-prev',
		},
		pagination: {
			el: '.swiper-pagination',
			type: 'bullets',
		}
	});

})(jQuery);

/**
 * Create language buttons to show multilingual metadata
 * [data-pkp-switcher-data]: Publication data for the switchers to control
 * [data-pkp-switcher]: Switchers' containers
 */
(() => {
	function createSwitcher(switcherContainer, data, localeOrder, localeNames) {
		// Get all locales for the switcher from the data
		const locales = Object.keys(Object.assign({}, ...Object.values(data)));
		// The initially selected locale
		let selectedLocale = null;
		// Create and sort to alphabetical order
		const buttons = localeOrder
			.map((locale) => {
				if (locales.indexOf(locale) === -1) {
					return null;
				}
				if (!selectedLocale) {
					selectedLocale = locale;
				}

				const isSelectedLocale = locale === selectedLocale;
				const button = document.createElement('button');

				button.type = 'button';
				button.classList.add('pkpBadge', 'pkpBadge--button');
				button.value = locale;
				button.tabIndex = '-1';
				button.role = 'option';
				button.ariaHidden = `${!isSelectedLocale}`;
				button.textContent = localeNames[locale];
				if (isSelectedLocale) {
					button.ariaPressed = 'false';
					button.ariaCurrent = 'true';
					button.tabIndex = '0';
				}
				return button;
			})
			.filter((btn) => btn)
			.sort((a, b) => a.value.localeCompare(b.value));

		// If only one button, set it disabled
		if (buttons.length === 1) {
			buttons[0].disabled = true;
		}

		buttons.forEach((btn, i) => {
			switcherContainer.appendChild(btn);
		});

		return buttons;
	}

	/**
	 * Sync data in elements to match the selected locale 
	 */
	function syncDataElContents(locale, propsData, langAttrs) {
		for (prop in propsData.data) {
			propsData.dataEls[prop].lang = langAttrs[locale];
			propsData.dataEls[prop].innerHTML = propsData.data[prop][locale] ?? '';
		}
	}

	/**
	 * Toggle visibility of the buttons
	 * setValue == true => aria-hidden == true, aria-expanded == false
	 */
	function setVisibility(switcherContainer, buttons, currentSelected, setValue) {
		// Toggle switcher container's listbox/none-role
		// Listbox when buttons visible and none when hidden
		switcherContainer.role = setValue ? 'none' : 'listbox';
		currentSelected.btn.ariaPressed = `${!setValue}`;
		buttons.forEach((btn) => {
			if (btn !== currentSelected.btn) {
				btn.ariaHidden = `${setValue}`;
			}
		});
		switcherContainer.ariaExpanded = `${!setValue}`;
	}

	function setSwitcher(propsData, switcherContainer, localeOrder, localeNames, langAttrs) {
		// Create buttons and append them to the switcher container
		const buttons = createSwitcher(switcherContainer, propsData.data, localeOrder, localeNames);
		const currentSelected = {btn: switcherContainer.querySelector('[tabindex="0"]')};
		const focused = {btn: currentSelected.btn};

		// Sync contents in data elements to match the selected locale (currentSelected.btn.value)
		syncDataElContents(currentSelected.btn.value, propsData, langAttrs);

		// Do not add listeners if just one button, it is disabled
		if (buttons.length < 2) {
			return;
		}

		// New button switches language and syncs data contents. Same button hides buttons.
		switcherContainer.addEventListener('click', (evt) => {
			const newSelectedBtn = evt.target;
			if (newSelectedBtn.type === 'button') {
				if (newSelectedBtn !== currentSelected.btn) {
					syncDataElContents(newSelectedBtn.value, propsData, langAttrs);
					// Aria
					currentSelected.btn.ariaCurrent = null;
					newSelectedBtn.ariaCurrent = 'true';
					currentSelected.btn.ariaPressed = null;
					newSelectedBtn.ariaPressed = 'true';
					// Tab index
					currentSelected.btn.tabIndex = '-1';
					newSelectedBtn.tabIndex = '0';
					// Update current and focused button
					currentSelected.btn = focused.btn = newSelectedBtn;
					focused.btn.focus();
				} else {
					setVisibility(switcherContainer, buttons, currentSelected, switcherContainer.ariaExpanded === 'true');
				}
			}
		});

		// Hide buttons when focus out
		switcherContainer.addEventListener('focusout', (evt) => {
			// For safari losing button focus
			if (evt.target.parentElement === switcherContainer && switcherContainer.ariaExpanded === 'true') {
				focused.btn.focus();
			}
			if (!evt.relatedTarget || evt.relatedTarget && evt.relatedTarget.parentElement !== switcherContainer) {
				setVisibility(switcherContainer, buttons, currentSelected, 'true');
			}
		});

		// Arrow keys left and right cycles button focus when buttons visible. Set focused button.
		switcherContainer.addEventListener("keydown", (evt) => {
			if (switcherContainer.ariaExpanded === 'true' && evt.target.type === 'button' && (evt.key === "ArrowRight" || evt.key === "ArrowLeft")) {
				focused.btn = (evt.key === "ArrowRight")
					? (focused.btn.nextElementSibling ?? buttons[0])
					: (focused.btn.previousElementSibling ?? buttons[buttons.length - 1]);
				focused.btn.focus();
			}
		});
	}

	/**
	 * Set all multilingual data and elements for the switchers
	 */
	function setSwitchersData(dataEls, pubLocaleData) {
		const propsData = {};
		dataEls.forEach((dataEl) => {
			const propName = dataEl.getAttribute('data-pkp-switcher-data');
			const switcherName = pubLocaleData[propName].switcher;
			if (!propsData[switcherName]) {
				propsData[switcherName] = {data: [], dataEls: []};
			}
			propsData[switcherName].data[propName] = pubLocaleData[propName].data;
			propsData[switcherName].dataEls[propName] = dataEl;
		});
		return propsData;
	}

	(() => {
		const switcherContainers = document.querySelectorAll('[data-pkp-switcher]');

		if (!switcherContainers.length) return;

		const pubLocaleData = JSON.parse(pubLocaleDataJson);
		const switchersDataEls = document.querySelectorAll('[data-pkp-switcher-data]');
		const switchersData = setSwitchersData(switchersDataEls, pubLocaleData);
		// Create and set switchers, and sync data on the page
		switcherContainers.forEach((switcherContainer) => {
			const switcherName = switcherContainer.getAttribute('data-pkp-switcher');
			if (switchersData[switcherName]) {
				setSwitcher(switchersData[switcherName], switcherContainer, pubLocaleData.localeOrder, pubLocaleData.localeNames, pubLocaleData.langAttrs);
			}
		});
	})();
})();