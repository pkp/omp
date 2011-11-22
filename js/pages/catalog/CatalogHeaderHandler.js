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

		// Save options for later
		this.searchTabIndex_ = options.searchTabIndex;
		this.seriesFetchUrlTemplate_ = options.seriesFetchUrlTemplate;
		this.categoryFetchUrlTemplate_ = options.categoryFetchUrlTemplate;

		// Set up the tabs
		var $catalogTabs = $('#catalogTabs');
		$catalogTabs.tabs().tabs('disable', this.searchTabIndex_); // Search results

		// React to "search" events from the search form.
		this.bind('searchCatalog', this.searchCatalogHandler_);

		// React to "select category" events from the category tab.
		this.bind('selectCategory', this.selectCategoryHandler_);

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
	 * The numeric index of the search results tab among other tabs
	 * @private
	 * @type {int}
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.
			prototype.searchTabIndex_ = 0;


	/**
	 * The URL template used to fetch the series submission list.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.
			prototype.seriesFetchUrlTemplate_ = null;


	/**
	 * The URL template used to fetch the category submission list.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.
			prototype.categoryFetchUrlTemplate_ = null;


	//
	// Private methods
	//
	/**
	 * Get the URL to fetch a series' monograph listing from
	 * @private
	 * @param {String} seriesPath The series path to return the fetch URL for.
	 * @return {String} The URL to use to fetch series contents.
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.prototype.getSeriesFetchUrl_ =
			function(seriesPath) {

		return (this.seriesFetchUrlTemplate_.replace('SERIES_PATH', seriesPath));
	};


	/**
	 * Get the URL to fetch a category's monograph listing from
	 * @private
	 * @param {String} categoryPath The category path to return the fetch URL for.
	 * @return {String} The URL to use to fetch series contents.
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.prototype.getCategoryFetchUrl_ =
			function(categoryPath) {

		return (this.seriesFetchUrlTemplate_.replace('SERIES_PATH', categoryPath));
	};


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
	 * pulldown atop the series tab.
	 * @private
	 *
	 * @param {$.pkp.controllers.form.FormHandler} callingForm The form
	 *  that triggered the event.
	 * @param {Event} event The upload event.
	 * @param {String?} seriesPath The selected series path.
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.
			prototype.selectSeriesHandler_ =
			function(callingForm, event, seriesPath) {

		// Remove any existing contents.
		$('#seriesContainer').children().remove();

		if (seriesPath !== '0') {
			// A series was selected. Load and display.
			$.get(this.getSeriesFetchUrl_(seriesPath),
					this.callbackWrapper(this.showFetchedSeries_), 'json');
		}
	};


	/**
	 * Handle the "select category" event triggered by the
	 * pulldown atop the category tab.
	 * @private
	 *
	 * @param {$.pkp.controllers.form.FormHandler} callingForm The form
	 *  that triggered the event.
	 * @param {Event} event The upload event.
	 * @param {String?} categoryPath The selected category path.
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.
			prototype.selectCategoryHandler_ =
			function(callingForm, event, categoryPath) {

		// Remove any existing contents.
		$('#categoryContainer').children().remove();

		if (categoryPath !== '0') {
			// A category was selected. Load and display.
			$.get(this.getCategoryFetchUrl_(categoryPath),
					this.callbackWrapper(this.showFetchedCategory_), 'json');
		}
	};


	/**
	 * Show the contents of a fetched series.
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 * @private
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.prototype.showFetchedSeries_ =
			function(ajaxContext, jsonData) {

		jsonData = this.handleJson(jsonData);

		// Find the container and add fetched content.
		$('#seriesContainer').append(jsonData.content);
	};


	/**
	 * Show the contents of a fetched category.
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 * @private
	 */
	$.pkp.pages.catalog.CatalogHeaderHandler.prototype.showFetchedCategory_ =
			function(ajaxContext, jsonData) {

		jsonData = this.handleJson(jsonData);

		// Find the container and add fetched content.
		$('#categoryContainer').append(jsonData.content);
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
