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

		this.$formatTabsSelector_ = options.formatsTabContainerSelector;

		// Bind for changes to grids (publication formats and proofs).
		this.bind('gridRefreshRequested', this.refreshWidgetsHandler_);
		this.bind('containerReloadRequested', this.refreshWidgetsHandler_);

	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.workflow.ProductionHandler,
			$.pkp.classes.Handler);


	//
	// Private Properties
	//
	/**
	 * Format tabs container selector.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.workflow.ProductionHandler.
			prototype.$formatTabsSelector_ = null;

	/**
	 * Flag to avoid unnecessary widgets refresh.
	 * @private
	 * @type {boolean}
	 */
	$.pkp.pages.workflow.ProductionHandler.
			prototype.widgetsRefreshed_ = false;


	//
	// Public Methods
	//
	/**
	 * This listens for grid refreshes from all grids inside the
	 * production page and call a method that will refresh all the
	 * others grids and tab widget.
	 *
	 * @private
	 * @param {HTMLElement} sourceElement The parent DIV element
	 *  which contains the tabs.
	 * @param {Event} event The triggered event (gridRefreshRequested).
	 */
	$.pkp.pages.workflow.ProductionHandler.prototype.refreshWidgetsHandler_ =
			function(sourceElement, event) {
		var $triggerElement = $(event.target);
		if (!this.widgetsRefreshed_) {
			this.widgetsRefreshed_ = true;
			if (!$triggerElement.attr('id').match(/^formatsGridContainer/)) {
				var $formatsGrid = $('[id^="formatsGridContainer"]',
						this.getHtmlElement()).children();
				$formatsGrid.trigger('dataChanged');
			}

			var $formatTabs = $(this.$formatTabsSelector_, this.getHtmlElement()).children('div');
			if ($formatTabs.has($triggerElement).length == 0) {
				$formatTabs.trigger('refreshTabs');
			}
		} else {
			this.widgetsRefreshed_ = false;
		}
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
