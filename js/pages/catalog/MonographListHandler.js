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

		// Attach the view type handlers
		$monographsContainer.find('.grid_view').click(
				this.callbackWrapper(this.useGridView));
		$monographsContainer.find('.list_view').click(
				this.callbackWrapper(this.useListView));

		this.useGridView();
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.MonographListHandler,
			$.pkp.classes.Handler);


	//
	// Public Methods
	//
	/**
	 * Callback that will be activated when the "list view" icon is clicked
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.catalog.MonographListHandler.prototype.useListView =
			function() {

		var $htmlElement = $(this.getHtmlElement());
		$htmlElement.find('.pkp_catalog_monographList')
			.removeClass('grid_view')
			.addClass('list_view');

		// Control enabled/disabled state of buttons
		$htmlElement.find('.list_view').attr('disabled', 'disabled');
		$htmlElement.find('.grid_view').attr('disabled', '');

		// In case called as event handler, stop further processing
		return false;
	};


	/**
	 * Callback that will be activated when the "grid view" icon is clicked
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.catalog.MonographListHandler.prototype.useGridView =
			function() {

		var $htmlElement = $(this.getHtmlElement());
		$htmlElement.find('.pkp_catalog_monographList')
			.removeClass('list_view')
			.addClass('grid_view');

		// Control enabled/disabled state of buttons
		$htmlElement.find('.grid_view').attr('disabled', 'disabled');
		$htmlElement.find('.list_view').attr('disabled', '');

		// In case called as event handler, stop further processing
		return false;
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
