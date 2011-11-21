/**
 * @defgroup js_pages_catalog
 */
// Create the pages_catalog namespace.
$.pkp.pages.catalog = $.pkp.pages.catalog || {};

/**
 * @file js/pages/catalog/CatalogHeaderHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogHeaderHandler
 * @ingroup js_pages_catalog
 *
 * @brief Handler for catalog management.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $catalogHeader The HTML element encapsulating
	 *  the header div.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler =
			function($catalogHeader, options) {

		this.parent($catalogHeader, options);
		this.searchTabIndex_ = options.searchTabIndex;

		var $catalogTabs = $('#catalogTabs');
		$catalogTabs.tabs().tabs('disable', this.searchTabIndex_); // Search results

		// React to "search" events from the search form.
		this.bind('searchCatalog', this.searchCatalogHandler_);

		// React to "select series" events from the series tab.
		this.bind('selectSeries', this.selectSeriesHandler_);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.CatalogHeaderHandler,
			$.pkp.classes.Handler);


	//
	// Private properties
	//
	/**
	 * The URL template used to fetch the metadata edit form.
	 * @private
	 * @type {string}
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.
			prototype.searchTabIndex_ = 0;


	//
	// Private methods
	//
	/**
	 * Handle the "search catalog" event triggered by the
	 * search form to load the results in the tab.
	 * @private
	 *
	 * @param {$.pkp.controllers.form.AjaxFormHandler} callingForm The form
	 *  that triggered the event.
	 * @param {Event} event The upload event.
	 * @param {String} searchUrl The URL that will return search results.
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.
			prototype.searchCatalogHandler_ =
			function(callingForm, event, searchUrl) {

		var tabIndex = this.searchTabIndex_;
		var $catalogTabs = $('#catalogTabs');
		var selectedTabIndex = $catalogTabs.tabs('option', 'selected');

		// Load and jump to the tab, or reload if already there
		if (selectedTabIndex === tabIndex) {
			// It's already selected
			$catalogTabs.tabs('url', tabIndex, searchUrl)
				.tabs('load', tabIndex);
		} else {
			// It's not selected yet
			$catalogTabs.tabs('url', tabIndex, searchUrl)
				.tabs('enable', tabIndex)
				.tabs('select', tabIndex);
		}
	};


	/**
	 * Handle the "select series" event triggered by the
	 * pulldown atop the series form.
	 * @private
	 *
	 * @param {$.pkp.controllers.form.FormHandler} callingForm The form
	 *  that triggered the event.
	 * @param {Event} event The upload event.
	 * @param {String} seriesId The selected series ID.
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.
			prototype.selectSeriesHandler_ =
			function(callingForm, event, seriesId) {


	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
