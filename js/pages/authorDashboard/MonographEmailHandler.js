/**
 * @defgroup js_pages_authorDashboard
 */


/**
 * @file js/pages/authorDashboard/MonographEmailHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographEmailHandler
 * @ingroup js_pages_authorDashboard
 *
 * @brief Handler for reading monograph emails within the author dashboard.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.linkAction.LinkActionHandler
	 *
	 * @param {jQuery} $monographEmailContainer The container for
	 *  the monograph email link.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.authorDashboard.MonographEmailHandler =
		function($monographEmailContainer, options) {

		this.parent($monographEmailContainer, options);

		$monographEmailContainer.find('a[id^="monographEmail"]').click(
				this.callbackWrapper(this.activateAction));
	}
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.authorDashboard.MonographEmailHandler,
			$.pkp.controllers.linkAction.LinkActionHandler);

/** @param {jQuery} $ jQuery closure. */
})(jQuery);