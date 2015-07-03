/**
 * @file js/pages/catalog/MonographPublicListHandler.js
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographPublicListHandler
 * @ingroup js_pages_catalog
 *
 * @brief Handler for monograph list.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.monographList.MonographListHandler
	 *
	 * @param {jQueryObject} $monographsContainer The HTML element encapsulating
	 *  the monograph list div.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.catalog.MonographPublicListHandler =
			function($monographsContainer, options) {

		this.parent($monographsContainer, options);

		this.formatList();
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.MonographPublicListHandler,
			$.pkp.controllers.monographList.MonographListHandler);


	//
	// Extendeded protected methods from MonographListHandler
	//
	/**
	 * @inheritDoc
	 */
	$.pkp.pages.catalog.MonographPublicListHandler.
			prototype.getMonographs = function() {
		return this.getHtmlElement().find('.pkp_catalog_monograph');
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
