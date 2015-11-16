/**
 * @file plugins/themes/default/js/main.js
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Handle JavaScript functionality unique to this theme.
 */
(function($) {

	// Register click handlers for the search panel
	var headerSearchPanelIsClosing = false,
	    headerSearchForm = $('#headerNavigationContainer .pkp_search'),
	    headerSearchPrompt = $('.headerSearchPrompt', headerSearchForm),
		headerSearchCancel = $('.headerSearchCancel', headerSearchForm),
		headerSearchInput = $('input[name="query"]', headerSearchForm);

	// Register events
	headerSearchPrompt.on('click', triggerSearchPanel);
	headerSearchCancel.on('click', closeSearchPanel);
	headerSearchInput.on('blur', function() {
		if(!headerSearchInput.val() && headerSearchForm.hasClass('is_open')) {
			closeSearchPanel();
		}
	});
	headerSearchForm.on('submit', function() {
		if(headerSearchForm.hasClass('is_searching')) {
			return;
		}
		headerSearchForm.addClass('is_searching');
	});
	headerSearchForm.on('keyup', function(e) {
		if(headerSearchForm.hasClass('is_open') && e.keyCode == 27) {
			closeSearchPanel();
		}
	});

	/**
	 * Open or submit search form
	 *
	 * @param Event e Optional event handler
	 */
	function triggerSearchPanel(e) {

		if (headerSearchPanelIsClosing) {
			return;
		}

		if (typeof e !== 'undefined') {
			e.preventDefault();
			e.stopPropagation();
		}

		if (headerSearchForm.hasClass('is_open')) {
			headerSearchForm.submit();
			return;
		}

		headerSearchForm.addClass('is_open');
		setTimeout(function() {
			headerSearchForm.find('input[type="text"]').focus();
		},200);
	}

	/**
	 * Close the search panel
	 *
	 * @param Event e Optional event handler
	 */
	function closeSearchPanel(e) {

		if (headerSearchPanelIsClosing) {
			return;
		}

		if (typeof e !== 'undefined') {
			e.preventDefault();
			e.stopPropagation();
		}

		headerSearchPanelIsClosing = true;
		headerSearchForm.removeClass('is_open');

		setTimeout(function() {
			headerSearchPanelIsClosing = false;
			headerSearchInput.val('');
		},300)
	}

	// Spotlights
	var spotlightComponent = $('.cmp_spotlights');
	if (spotlightComponent.length) {

		// Store references to the tabs in the list and the spotlights themselves
		var tabs = spotlightComponent.find('> .list a');
		var spotlights = spotlightComponent.find('.spotlights > li');

		// Handle click events on the tabs
		tabs.click(function(e) {

			// Intercept the link's normal action and prevent the event from
			// bubbling up the DOM
			e.preventDefault();
			e.stopPropagation();

			// Get the link that was clicked on and exit if it's already the
			// current link
			var target = $(e.target);
			if (target.hasClass('current')) {
				return;
			}

			// Reset the element classes so the spotlight clicked on is now
			// assigned the `current` class
			tabs.parent().removeClass('current');
			spotlights.removeClass('current');
			target.parent().addClass('current');
			spotlights.filter('.spotlight_' + target.data('spotlight')).addClass('current');
		});
	}

})(jQuery);
