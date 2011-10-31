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
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.CatalogHeaderHandler,
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
