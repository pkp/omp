/**
 * @defgroup js_pages_manageCatalog
 */
/**
 * @file js/pages/manageCatalog/ManageCatalogHeaderHandler.js
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageCatalogHeaderHandler
 * @ingroup js_pages_manageCatalog
 *
 * @brief Handler for catalog management.
 *
 */
(function($) {

	/** @type {Object} */
	$.pkp.pages.manageCatalog = $.pkp.pages.manageCatalog || {};



	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.TabHandler
	 *
	 * @param {jQueryObject} $catalogTabs The tabs HTML element.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler =
			function($catalogTabs, options) {

		this.parent($catalogTabs, options);

		// React to data changed from inner widgets (including modals
		// that have an event bridge and directs their data changed events
		// to the element that triggered the modal).
		this.bind('gridRefreshRequested', this.dataChangedHandler_);
		this.bind('dataChanged', this.dataChangedHandler_);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler,
			$.pkp.controllers.TabHandler);


	//
	// Private methods
	//
	/**
	 * Data changed event handler. For each tab we need to react in a
	 * different way.
	 * @private
	 * @param {Object} element The HTML element which generated the event.
	 * @param {Event} event The data changed event.
	 * @return {boolean}
	 */
	$.pkp.pages.manageCatalog.ManageCatalogHeaderHandler.
			prototype.dataChangedHandler_ = function(element, event) {

		var $catalogTabs = $('#catalogTabs').tabs(),
				currentTabIndex = $catalogTabs.tabs('option', 'active');

		switch (currentTabIndex) {
			case 0:
				// Homepage.
				if ($(event.target).attr('id') == 'homepageMonographsGridContainer') {
					return false;
				}
				$("div[id^='component-grid-managecatalog-homepagemonographsgrid']",
						this.getHtmlElement()).trigger('dataChanged');
				break;

			case 1:
				// Category.
				// Avoid refresh requested events coming from category monographs grid.
				if ($(event.target).attr('id') == 'categoryMonographsGridContainer') {
					return false;
				}
				$("div[id^='component-grid-managecatalog-categorymonographsgrid']",
						this.getHtmlElement()).trigger('dataChanged');
				break;
			case 2:
				// Series.
				// Avoid refresh requested events coming from series monographs grid.
				if ($(event.target).attr('id') == 'seriesMonographsGridContainer') {
					return false;
				}
				$("div[id^='component-grid-managecatalog-seriesmonographsgrid']",
						this.getHtmlElement()).trigger('dataChanged');
				break;
		}
		return false;
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
