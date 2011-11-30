/**
 * @file js/pages/catalog/MonographHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographHandler
 * @ingroup js_pages_catalog
 *
 * @brief Handler for a monograph entry.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $monographsContainer The HTML element encapsulating
	 *  the monograph list div.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.catalog.MonographHandler =
			function($monographsContainer, options) {

		this.parent($monographsContainer, options);

		// Attach the view type handlers, if links exist
		$monographsContainer.find('.star').click(
				this.callbackWrapper(this.featureButtonHandler_));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.MonographHandler,
			$.pkp.classes.Handler);


	//
	// Private Methods
	//
	/**
	 * Callback that will be activated when "feature" is toggled
	 *
	 * @private
	 *
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.catalog.MonographHandler.prototype.featureButtonHandler_ =
			function() {

		// FIXME: Handle toggling of featured status here

		// Stop further event processing
		return false;
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
