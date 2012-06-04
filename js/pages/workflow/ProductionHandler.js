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

		// Bind for changes to grids (publication formats and proofs).
		this.bind('gridRefreshRequested', this.loadWidgetsHandler_);
		this.bind('containerReloadRequested', this.loadWidgetsHandler_);

		// Load the current accordion.
		this.loadWidgets_();
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


	/**
	 * Flag to avoid infinite loop while refreshing grids on this page.
	 * @private
	 * @type {boolean}
	 */
	$.pkp.pages.workflow.ProductionHandler.
			prototype.stopRefreshProcess_ = false;


	//
	// Public Methods
	//
	/**
	 * This listens for grid refreshes from all grids inside the
	 * production page and call a method that will refresh all the
	 * others grids and accordion widget.
	 *
	 * @private
	 * @param {HTMLElement} sourceElement The parent DIV element
	 *  which contains the tabs.
	 * @param {Event} event The triggered event (gridRefreshRequested).
	 */
	$.pkp.pages.workflow.ProductionHandler.prototype.loadWidgetsHandler_ =
			function(sourceElement, event) {

		if (!this.stopRefreshProcess_) {
			// Avoid infinite loop.
			this.stopRefreshProcess_ = true;

			this.loadWidgets_();
			return;
		}

		this.stopRefreshProcess_ = false;
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

		// Update any in place notification above this widget in DOM hierarchy.
		$publicationFormatContainer.
				trigger('notifyUser', $publicationFormatContainer);

		$publicationFormatContainer.accordion('destroy').accordion({
			autoHeight: false,
			collapsible: true,
			active: false
		});
	};


	/**
	 * Execute the steps necessary to load/reload the accordions and the
	 * publication formats grid.
	 * It requests a list of the current publication formats from the
	 * CatalogEntryHandler and calls a callback which updates the tab
	 * state accordingly as they are changed.
	 * It also updates the publication formats grid.
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 * @private
	 */
	$.pkp.pages.workflow.ProductionHandler.prototype.loadWidgets_ =
			function(ajaxContext, jsonData) {
		var callback = this.callbackWrapper(this.updateAccordionHandler_);
		$.get(this.accordionUrl_, null, callback, 'json');

		$formatsGrid = $('[id^="formatsGridContainer"]',
				this.getHtmlElement()).children();
		$formatsGrid.trigger('dataChanged');
	};

/** @param {jQuery} $ jQuery closure. */
})(jQuery);
