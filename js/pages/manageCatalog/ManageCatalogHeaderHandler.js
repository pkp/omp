/**
 * @defgroup js_pages_manageCatalog
 */
// Create the pages_manageCatalog namespace.
$.pkp.pages.manageCatalog = $.pkp.pages.manageCatalog || {};

/**
 * @file js/pages/manageCatalog/ManageCatalogHeaderHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageCatalogHeaderHandler
 * @ingroup js_pages_manageCatalog
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
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler =
			function($catalogHeader, options) {

		this.parent($catalogHeader, options);

		// Save options for later
		this.searchTabIndex_ = options.searchTabIndex;
		this.spotlightTabName_ = options.spotlightTabName;
		this.seriesFetchUrlTemplate_ = options.seriesFetchUrlTemplate;
		this.categoryFetchUrlTemplate_ = options.categoryFetchUrlTemplate;
		this.spotlightsUrl_ = options.spotlightsUrl;

		// Set up the tabs
		var $catalogTabs = $('#catalogTabs').tabs();
		$catalogTabs.tabs('disable', this.searchTabIndex_); // Search results

		// React to "search" events from the search form.
		this.bind('searchCatalog', this.searchCatalogHandler_);

		// React to "select category" events from the category tab.
		this.bind('selectCategory', this.selectCategoryHandler_);

		// React to "select series" events from the series tab.
		this.bind('selectSeries', this.selectSeriesHandler_);

		// React to showing a tab.  Used to load the remote spotlights grid.
		var eventData = {
				spotlightTabName: this.spotlightTabName_,
				spotlightsUrl: this.spotlightsUrl_
		};
		$catalogTabs.bind('tabsselect', eventData,
				this.callbackWrapper(this.showTabHandler_));

		// React to data changed from inner widgets (including modals
		// that have an event bridge and directs their data changed events
		// to the element that triggered the modal).
		this.bind('dataChanged', this.dataChangedHandler_);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler,
			$.pkp.classes.Handler);


	//
	// Private properties
	//
	/**
	 * The numeric index of the search results tab among other tabs
	 * @private
	 * @type {int}
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
			prototype.searchTabIndex_ = 0;


	/**
	 * The panel id (name) of the spotlight tab among other tabs
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
			prototype.spotlightTabName_ = null;


	/**
	 * The URL template used to fetch the series submission list.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
			prototype.seriesFetchUrlTemplate_ = null;


	/**
	 * The URL template used to fetch the category submission list.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
			prototype.categoryFetchUrlTemplate_ = null;


	/**
	 * The URL used to fetch the spotlights list.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
			prototype.spotlightsUrl_ = null;


	//
	// Private methods
	//
	/**
	 * Get the URL to fetch a series' monograph listing from
	 * @private
	 * @param {String} seriesPath The series path to return the fetch URL for.
	 * @return {String} The URL to use to fetch series contents.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.prototype.
			getSeriesFetchUrl_ = function(seriesPath) {

		return (this.seriesFetchUrlTemplate_.
				replace('SERIES_PATH', seriesPath));
	};


	/**
	 * Get the URL to fetch a category's monograph listing from
	 * @private
	 * @param {String} categoryPath The category path to return
	 * the fetch URL for.
	 * @return {String} The URL to use to fetch series contents.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.prototype.
			getCategoryFetchUrl_ = function(categoryPath) {

		return (this.categoryFetchUrlTemplate_.
				replace('CATEGORY_PATH', categoryPath));
	};


	/**
	 * Handle the "search catalog" event triggered by the
	 * search form to load the results in the tab.
	 * @private
	 *
	 * @param {$.pkp.controllers.form.AjaxFormHandler} callingForm The form
	 *  that triggered the event.
	 * @param {Event} event The event.
	 * @param {String} searchUrl The URL that will return search results.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
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
	 * @param {Event} event The event.
	 * @param {String?} seriesPath The selected series path.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
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
	 * @param {Event} event The event.
	 * @param {String?} categoryPath The selected category path.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
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
	 * Handle the "show tabs" event.  In most cases, we just return true since
	 * there are other methods for dealing with the tabs.  The exception is the
	 * spotlights tab, which is loaded like a regular AJAX tab.
	 * @private
	 *
	 * @param {Object} element The parent element of the tab
	 * that triggered the event.
	 * @param {Event} event The event.
	 * @param {Object} tabElement the HTML element which generated the event.
	 * @return {boolean} Let the other tabs function normally.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
			prototype.showTabHandler_ = function(element, event, tabElement) {

		if (tabElement.panel.id == event.data.spotlightTabName) {
			this.loadSpotligthsContent_(event.data.spotlightsUrl);
		} else {
			return true;
		}
	};


	/**
	 * Loads the spotlights tab content.
	 * @private
	 * @param {string} url Url to fetch the content.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
			prototype.loadSpotligthsContent_ = function(url) {
		$.get(url, function(data) {
			var jsonData = $.parseJSON(data);
			$('#spotlightsTab').html(jsonData.content);
		});
	};


	/**
	 * Data changed event handler. For each tab we need to react in a
	 * different way.
	 * @private
	 * @param {Event} event The data changed event.
	 * @param {Object} element The HTML element which generated the event.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
			prototype.dataChangedHandler_ = function(event, element) {

		var $catalogTabs = $('#catalogTabs').tabs();
		var currentTabIndex = $catalogTabs.tabs('option', 'selected');

		switch(currentTabIndex) {
			case 0:
				// Homepage.
				$catalogTabs.tabs('load', 0);
				break;
			case 1:
				// Category.
				$('#selectCategoryForm', this.getHtmlElement()).
						trigger('containerReloadRequested');
				break;
			case 2:
				// Series.
				$('#selectSeriesForm', this.getHtmlElement()).
						trigger('containerReloadRequested');
				break;
			case 3:
			case 4:
				if (this.searchTabIndex_ == currentTabIndex) {
					// Search tab.
					$('#catalogSearchForm', this.getHtmlElement()).
							trigger('submit');
				} else {
					// Spotlights tab.
					this.loadSpotligthsContent_(this.spotlightsUrl_);
				}
				break;
		}
	};


	/**
	 * Show the contents of a fetched series.
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 * @private
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.prototype.
			showFetchedSeries_ = function(ajaxContext, jsonData) {

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
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.prototype.
			showFetchedCategory_ = function(ajaxContext, jsonData) {

		jsonData = this.handleJson(jsonData);

		// Find the container and add fetched content.
		$('#categoryContainer').append(jsonData.content);
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
