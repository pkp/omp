/**
 * @defgroup js_controllers_tab_catalogEntry
 */
// Define the namespace.
jQuery.pkp.controllers.tab.catalogEntry = jQuery.pkp.controllers.tab.catalogEntry || {};


/**
 * @file js/controllers/tab/catalogEntry/CatalogEntryTabHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryTabHandler
 * @ingroup js_controllers_tab_catalogEntry
 *
 * @brief A subclass of TabHandler for handling the catalog entry tabs. It adds
 * a listener for grid refreshes, so the tab interface can be reloaded.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.TabHandler
	 *
	 * @param {jQuery} $tabs A wrapped HTML element that
	 *  represents the tabbed interface.
	 * @param {Object} options Handler options.
	 */
	$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler = function($tabs, options) {
		this.parent($tabs, options);

		// Attach the tabs grid refresh handler.
		this.bind('gridRefreshRequested', this.gridRefreshRequested);

		if (options.tabsUrl) {
			this.tabsUrl_ = options.tabsUrl;
		}

		if (options.tabContentUrl) {
			this.tabContentUrl_ = options.tabContentUrl;
		}
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler, $.pkp.controllers.TabHandler);


	//
	// Private properties
	//
	/**
	 * The URL for retrieving a tab's content.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler.prototype.tabContentUrl_ = null;


	//
	// Public methods
	//
	/**
	 * This listens for grid refreshes from the publication formats grid. It
	 * requests a list of the current publication formats from the CatalogEntryHandler
	 * and calls a callback which updates the tab state accordingly as they are changed.
	 *
	 * @param {HTMLElement} sourceElement The parent DIV element
	 *  which contains the tabs.
	 * @param {Event} event The triggered event (gridRefreshRequested).
	 */
	$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler.prototype.gridRefreshRequested =
			function(sourceElement, event) {

		var $updateSourceElement = $(event.target);
		if ($updateSourceElement.attr('id') == 'formatsGridContainer') {

			if (this.tabsUrl_ && this.tabContentUrl_) {
				var $element = this.getHtmlElement();
				$.get(this.tabsUrl_, null, this.callbackWrapper(this.updateTabsHandler_), 'json');
			}
		}
	};


	//
	// Private methods
	//
	/**
	 * A callback to update the tabs on the interface.
	 *
	 * @private
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} data A parsed JSON response object.
	 */
	$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler.prototype.updateTabsHandler_ =
			function(ajaxContext, data) {

		var jsonData = this.handleJson(data);
		var $element = this.getHtmlElement();
		var currentTabs = $element.find('li a');
		var currentIndexes = {};

		// only interested in publication format tabs, so filter out the others
		var regexp = /publication(\d+)/;

		for (var j=0; j<currentTabs.length; j++) {
			var title = currentTabs[j].getAttribute('title');
			var match = regexp.exec(title);
			if (match !== null) {
				// match[1] is the id of a current format.
				// j also happens to be the zero-based index of the tab position
				// which will be useful if we have to remove it.
				currentIndexes[match[1]] = j;
			}
		}

		for (var i in jsonData.formats) {
			// i is the formatId, formats[i] is the localized name.
			if (!(i in currentIndexes)) { // this is a tab that has been added
				var url = this.tabContentUrl_ + '&assignedPublicationFormatId=' + encodeURIComponent(i);
				// replace dollar signs in $$$call$$$ so the .add() call interpolates correctly.
				// is this a bug in jqueryUI?
				url = url.replace(/[$]/g, '$$$$');
				$element.tabs('add', url, jsonData.formats[i]);
				$element.find('li a').filter(':last').attr('title', 'publication' + i);
			}
		}

		// now check our existing tabs to see if any should be removed
		for (i in currentIndexes) {
			if (!(i in jsonData.formats)) { // this is a tab that has been removed
				$element.tabs('remove', currentIndexes[i]);
			} else { // tab still exists, update localized name if necessary
				$element.find('li a').filter('[title="publication' + i + '"]').html(jsonData.formats[i]);
			}
		}
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
