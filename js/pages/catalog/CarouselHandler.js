/**
 * @defgroup js_pages_catalog
 */
// Create the pages_catalog namespace.
$.pkp.pages.catalog = $.pkp.pages.catalog || {};

/**
 * @file js/pages/catalog/CarouselHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CarouselHandler
 * @ingroup js_pages_catalog
 *
 * @brief Catalog carousel handler.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $carouselElement The HTML element encapsulating
	 *  the carousel.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.catalog.CarouselHandler =
			function($carouselElement, options) {

		this.parent($carouselElement, options);

		$('#featuresCarousel').CloudCarousel({
			xPos: 256,
			yPos: 0,
			buttonLeft: $('#left-but'),
			buttonRight: $('#right-but'),
			altBox: $('#alt-text'),
			titleBox: $('#title-text')
		});
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.CarouselHandler,
			$.pkp.classes.Handler);


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
