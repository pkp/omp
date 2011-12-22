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
	 * @param {jQuery} $containerElement The HTML element encapsulating
	 *  the carousel container.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.catalog.CarouselHandler =
			function($containerElement, options) {

		this.parent($containerElement, options);

		var $carouselElement = $containerElement.find('#featuresCarousel');
		//$carouselElement.find('img').hide(0);
		$carouselElement.orbit({
			timer: false,
			afterSlideChange: this.callbackWrapper(this.afterSlideChangeHandler_)
		});
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.CarouselHandler,
			$.pkp.classes.Handler);


	//
	// Private Functions
	//
	/**
	 * Callback that will be activated when a new monograph is displayed
	 * in the carousel.
	 *
	 * @private
	 *
	 * @param {Object} selectedElement The currently selected DOM element.
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.afterSlideChangeHandler_ =
			function(selectedElement) {

		// FIXME: Fetch and display the status information for
		// selectedElement.

		return false;
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
