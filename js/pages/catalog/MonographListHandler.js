/**
 * @file js/pages/catalog/MonographListHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographListHandler
 * @ingroup js_pages_catalog
 *
 * @brief Handler for monograph list.
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
	$.pkp.pages.catalog.MonographListHandler =
			function($monographsContainer, options) {

		this.parent($monographsContainer, options);

		$monographsContainer.find('ul').sortable();
console.log($monographsContainer.find('.grid_view'));
		// Attach the view type handlers
		$monographsContainer.find('.grid_view').click(
				this.callbackWrapper(this.gridViewHandler_));
		$monographsContainer.find('.list_view').click(
				this.callbackWrapper(this.listViewHandler_));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.MonographListHandler,
			$.pkp.classes.Handler);


	//
	// Private Methods
	//
	/**
	 * Callback that will be activated when the "list view" icon is clicked
	 *
	 * @private
	 *
	 * @param {Object} callingContext The calling element or object.
	 * @param {Event=} event The triggering event (e.g. a click on
	 *  a button.
	 * @return {boolean} Should return false to stop event processing.
	 */
	$.pkp.pages.catalog.MonographListHandler.prototype.listViewHandler_ =
			function(callingContext, event) {

		var $htmlElement = $(this.getHtmlElement());
		$htmlElement.find('.pkp_catalog_monographList')
			.removeClass('grid_view')
			.addClass('list_view');

		// The buck stops here
		return false;
	};


	/**
	 * Callback that will be activated when the "grid view" icon is clicked
	 *
	 * @private
	 *
	 * @param {Object} callingContext The calling element or object.
	 * @param {Event=} event The triggering event (e.g. a click on
	 *  a button.
	 * @return {boolean} Should return false to stop event processing.
	 */
	$.pkp.pages.catalog.MonographListHandler.prototype.gridViewHandler_ =
			function(callingContext, event) {

		var $htmlElement = $(this.getHtmlElement());
		$htmlElement.find('.pkp_catalog_monographList')
			.removeClass('list_view')
			.addClass('grid_view');

		// The buck stops here
		return false;
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
