/**
 * @defgroup js_pages_catalog
 */
// Create the pages_catalog namespace.
$.pkp.pages.catalog = $.pkp.pages.catalog || {};

/**
 * @file js/pages/catalog/CarouselHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
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

		$('.details_box', $containerElement).hide();

		$containerElement.roundabout({
			minZ: 0,
			maxZ: 360,
			minOpacity: 1,
			minScale: 0.05,
			duration:this.TRANSITION_DURATION_,
			triggerFocusEvents: true,
			triggerBlurEvents: true
		}, this.callbackWrapper(function() { // Called when the carousel is ready.
			// Toggle the element in focus.
			this.toggleFeature_($('.roundabout-in-focus'), true);

			// Remove all download progress indicators.
			var $features = $('li.mover', this.getHtmlElement());
			$features.find('.pkp_helpers_progressIndicator').hide();
			$features.addClass('pkp_helpers_black_bg');
		}));

		this.bind('blur', this.callbackWrapper(this.blurCallback_));
		this.bind('focus', this.callbackWrapper(this.focusCallback_));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.CarouselHandler,
			$.pkp.classes.Handler);


	//
	// Private constansts.
	//
	/**
	 * {integer} The duration of the transition between two features.
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.TRANSITION_DURATION_ = 550;


	/**
	 * {integer} Features minimum opacity, when not focused.
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.MIN_OPACITY_ = 0.4;


	//
	// Private methods.
	//
	/**
	 * Called everytime a carousel feature is blurred.
	 * @param {jQuery} $context The context in which ocurred the event.
	 * @param {HtmlElement} element The element that triggered the event.
	 * @param {Event} event The blur event.
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.blurCallback_ =
			function($context, element, event) {
		this.toggleFeature_($(event.target), false);
	};


	/**
	 * Called everytime a carousel feature is focused.
	 * @param {jQuery} $context The context in which ocurred the event.
	 * @param {HtmlElement} element The element that triggered the event.
	 * @param {Event} event The focus event.
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.focusCallback_ =
			function($context, element, event) {
		this.toggleFeature_($(event.target), true);
	};


	/**
	 * Execute code to toggle the feature (focused or not).
	 * @param {jQuery} $feature The feature that will be toggled.
	 * @param {boolean} show True if focused and false if not.
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.toggleFeature_ =
			function($feature, show) {
		var $img = $('img', $feature);
		var $detailsElement = $('.details_box', $feature);
		var toggleDetailsDuration = 250;
		var opacityValue = 1;
		var imgAnimationDuration = this.TRANSITION_DURATION_;

		if (!show) {
			opacityValue = this.MIN_OPACITY_;
			imgAnimationDuration = toggleDetailsDuration;
			$detailsElement.hide(toggleDetailsDuration);
		}

		if ($img.css('opacity') != opacityValue) {
			$('img', $feature).animate(
					{opacity: opacityValue}, imgAnimationDuration);
			if (show) {
				$detailsElement.show(toggleDetailsDuration);
			} else {
				$detailsElement.hide(toggleDetailsDuration);
			}
		}
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
