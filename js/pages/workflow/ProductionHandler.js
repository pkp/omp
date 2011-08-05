/**
 * @defgroup js_pages_production
 */
// Create the pages_authorDashboard namespace.
jQuery.pkp.pages.workflow = { };

/**
 * @file js/pages/production/ProductionHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionHandler
 * @ingroup js_pages_production
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
		$('#productionAccordion', $production).accordion({
			autoHeight: false,
			collapsible: true
		});
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.workflow.ProductionHandler,
			$.pkp.classes.Handler);


	//
	// Private static properties
	//


	//
	// Private properties
	//


	//
	// Private methods
	//


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
