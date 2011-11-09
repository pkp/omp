/**
 * @defgroup js_pages_production
 */
// Create the pages_authorDashboard namespace.
jQuery.pkp.pages.admin = jQuery.pkp.pages.admin || { };

/**
 * @file js/pages/admin/PressesHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressesHandler
 * @ingroup js_pages_admin
 *
 * @brief Handler for the hosted presses page.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $presses The HTML element encapsulating
	 *  the presses page.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.admin.PressesHandler =
			function($presses, options) {

		var $linkActionElement = $('#openWizard a');

		if ($linkActionElement) {
			// Hide the link to users.
			$linkActionElement.attr('style', 'display:none');
		}

		this.parent($presses, options);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.admin.PressesHandler,
			$.pkp.classes.Handler);


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
