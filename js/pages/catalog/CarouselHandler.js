/**
 * @defgroup js_pages_catalog
 */
/**
 * @file js/pages/catalog/CarouselHandler.js
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CarouselHandler
 * @ingroup js_pages_catalog
 *
 * @brief Catalog carousel handler.
 *
 */
(function($) {

	/** @type {Object} */
	$.pkp.pages.catalog = $.pkp.pages.catalog || {};



	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQueryObject} $containerElement The HTML element encapsulating
	 *  the carousel container.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.catalog.CarouselHandler =
			function($containerElement, options) {
		var $carousel = $containerElement.find('.pkp_catalog_carousel');
		this.parent($containerElement, options);

		$carousel.slick({
			dots: true,
			adaptiveHeight: true
		});

		// FIXME: Required to get display initialized properly.
		$carousel.slickGoTo(0);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.CarouselHandler,
			$.pkp.classes.Handler);
/** @param {jQuery} $ jQuery closure. */
}(jQuery));
