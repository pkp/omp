/**
 * @file js/pages/workflow/ProductionHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionHandler
 * @ingroup js_pages_workflow
 *
 * @brief Handler for the production stage.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $production The HTML element encapsulating
	 *  the production page.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.workflow.ProductionHandler =
			function($production, options) {

		this.parent($production, options);

		// Transform the stage sections into jQueryUI accordions.
		$('#metadataAccordion', $production).accordion({
			autoHeight: false,
			collapsible: true,
			active: false
		});

		// Store the Publication Formats fetch URL.
		this.accordionUrl_ = options.accordionUrl;

		// Bind for changes to the Publication Formats grid.
		this.bind('gridRefreshRequested', this.fetchAccordionHandler_);

		// Load the current accordion.
		this.trigger('gridRefreshRequested');
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.workflow.ProductionHandler,
			$.pkp.classes.Handler);


	//
	// Private Properties
	//
	/**
	 * The URL template used to fetch the publication formats accordion.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.workflow.ProductionHandler.
			prototype.accordionUrl_ = null;


	//
	// Public Methods
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
	$.pkp.pages.workflow.ProductionHandler.prototype.fetchAccordionHandler_ =
			function(sourceElement, event) {

		$.get(this.accordionUrl_, null, this.callbackWrapper(this.updateAccordionHandler_), 'json');
	};


	/**
	 * Display the fetched publication formats accordion.
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 * @private
	 */
	$.pkp.pages.workflow.ProductionHandler.prototype.updateAccordionHandler_ =
			function(ajaxContext, jsonData) {

		jsonData = this.handleJson(jsonData);

		// Find the container and add fetched content.
		var $publicationFormatContainer = $('#publicationFormatContainer');
		$publicationFormatContainer.empty();
		$publicationFormatContainer.append(jsonData.content);

		$publicationFormatContainer.accordion({
			autoHeight: false,
			collapsible: true,
			active: false
		});
	};

/** @param {jQuery} $ jQuery closure. */
})(jQuery);
