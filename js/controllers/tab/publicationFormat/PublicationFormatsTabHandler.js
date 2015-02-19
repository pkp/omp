/**
 * @defgroup js_controllers_tab_publicationFormat
 */
/**
 * @file js/controllers/tab/publicationFormat/PublicationFormatsTabHandler.js
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatsTabHandler
 * @ingroup js_controllers_tab_publicationFormat
 *
 * @brief A subclass of TabHandler for handling the publication formats tabs.
 * It adds a listener for grid refreshes, so the tab interface can be reloaded.
 */
(function($) {

	/** @type {Object} */
	$.pkp.controllers.tab.publicationFormat =
			$.pkp.controllers.tab.publicationFormat || {};



	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.TabHandler
	 *
	 * @param {jQueryObject} $tabs A wrapped HTML element that
	 *  represents the tabbed interface.
	 * @param {Object} options Handler options.
	 */
	$.pkp.controllers.tab.publicationFormat.PublicationFormatsTabHandler =
			function($tabs, options) {
		if (options.currentFormatTabId !== undefined) {
			var $linkId = 'publication' + options.currentFormatTabId,
					$tab = $('#' + $linkId, $tabs).parent('li');

			if ($tab.length) {
				options.selected = $tabs.children().children().index($tab);
			}
		}

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
	 * @type {string?}
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
			var publicationId = null,
					$element = this.getHtmlElement(),
					$selectedTabLink = $('li.ui-tabs-selected',
							this.getHtmlElement()).find('a'),
					publicationElementId;

			if ($selectedTabLink.length) {
				publicationElementId = $selectedTabLink.attr('id');
				publicationId = $.trim(publicationElementId.
						replace('publication', ' '));
			}

			$.get(this.tabsUrl_, {currentFormatTabId: publicationId},
					this.callbackWrapper(this.updateTabsHandler_), 'json');
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

		this.trigger('gridRefreshRequested');

		var jsonData = this.handleJson(data),
				$tabs = this.getHtmlElement();

		if (jsonData !== false) {
			// Replace the grid content
			$tabs.replaceWith(jsonData.content);
		}
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
