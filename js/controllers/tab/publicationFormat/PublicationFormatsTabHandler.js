/**
 * @defgroup js_controllers_tab_publicationFormat
 */
// Define the namespace.
jQuery.pkp.controllers.tab.publicationFormat =
			jQuery.pkp.controllers.tab.publicationFormat || {};


/**
 * @file js/controllers/tab/publicationFormat/PublicationFormatsTabHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatsTabHandler
 * @ingroup js_controllers_tab_publicationFormat
 *
 * @brief A subclass of TabHandler for handling the publication formats tabs. It adds
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
	$.pkp.controllers.tab.publicationFormat.PublicationFormatsTabHandler =
			function($tabs, options) {
		this.parent($tabs, options);

		this.tabsUrl_ = options.tabsUrl;
		this.bind('refreshTabs', this.refreshTabsHandler_);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.tab.publicationFormat.PublicationFormatsTabHandler,
			$.pkp.controllers.TabHandler);


	//
	// Private properties
	//
	/**
	 * The URL for retrieving tabs.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.tab.publicationFormat.PublicationFormatsTabHandler.prototype.
			tabsUrl_ = null;


	//
	// Private methods
	//
	/**
	 * Tab refresh handler.
	 *
	 * @private
	 *
	 * @param {HTMLElement} sourceElement The parent DIV element
	 *  which contains the tabs.
	 * @param {Event} event The triggered event (refreshTabs).
	 */
	$.pkp.controllers.tab.publicationFormat.PublicationFormatsTabHandler.prototype.
			refreshTabsHandler_ = function(sourceElement, event) {

		if (this.tabsUrl_) {
			var $element = this.getHtmlElement();
			$.get(this.tabsUrl_, null, this.callbackWrapper(
					this.updateTabsHandler_), 'json');
		}
	};


	/**
	 * A callback to update the tabs on the interface.
	 *
	 * @private
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} data A parsed JSON response object.
	 */
	$.pkp.controllers.tab.publicationFormat.PublicationFormatsTabHandler.prototype.
			updateTabsHandler_ = function(ajaxContext, data) {

		var jsonData = this.handleJson(data);
		if (jsonData !== false) {
			// Get the tabs that we're updating
			var $tabs = this.getHtmlElement();

			// Replace the grid content
			$tabs.replaceWith(jsonData.content);
		}
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
