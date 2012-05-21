/**
 * @file js/pages/catalog/MonographListHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographListHandler
 * @ingroup js_pages_catalog
 *
 * @brief Handler for monograph list.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $monographsContainer The HTML element encapsulating
	 *  the monograph list div.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.catalog.MonographListHandler =
			function($monographsContainer, options) {

		this.parent($monographsContainer, options);
		// iterate over our monographs in groups of four, since our CSS spacing
		// displays four monographs per row.  Normalize the element detail heights.
		var $monographs = $monographsContainer.find('.pkp_catalog_monograph');
		for (var $i = 0; $i < $monographs.size(); $i += 4) {
			$monographs.slice($i, $i + 4).equalizeElementHeights();
		}
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.MonographListHandler,
			$.pkp.classes.Handler);

/** @param {jQuery} $ jQuery closure. */
})(jQuery);
