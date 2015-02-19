/**
 * @defgroup js_pages_catalog
 */
/**
 * @file js/pages/catalog/CarouselHandler.js
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
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
		this.parent($containerElement, options);

		// Hide all items detail boxes.
		$('.details_box', $containerElement).hide();

		// Bind handler to images load event.
		var $images = $containerElement.find('img');
		$images.bind('load', this.callbackWrapper(this.loadCallback_));

		// Apply the opacity value to images.
		$images.css('opacity', this.MIN_OPACITY_);

		// Bind click handlers to carousel items.
		$('li.mover', $containerElement).click(
				this.callbackWrapper(this.clickHandler_));

		// Bind blur and focus events handlers.
		this.bind('blur', this.callbackWrapper(this.blurCallback_));
		this.bind('focus', this.callbackWrapper(this.focusCallback_));

		// Bind carousel controls click handlers.
		$('#nextCarouselItem', $containerElement).click(
				this.callbackWrapper(this.nextItemClickHandler_));
		$('#previousCarouselItem', $containerElement).click(
				this.callbackWrapper(this.previousItemClickHandler_));

		this.applyCarouselPlugin_();
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.CarouselHandler,
			$.pkp.classes.Handler);


	//
	// Private constants.
	//
	/**
	 * {number} The duration of the transition between two items.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.TRANSITION_DURATION_ = 550;


	/**
	 * {number} Items minimum opacity, when not focused.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.MIN_OPACITY_ = 0.5;


	/**
	 * {number} The maximum number of placeholders that this carousel can use
	 * to make sure the presentation of the items will be same for smaller number
	 * of items.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.MAX_PLACEHOLDER_NUMBER_ = 8;


	//
	// Private methods.
	//
	/**
	 * Called everytime a carousel feature is blurred.
	 * @param {jQueryObject} $context The context in which ocurred the event.
	 * @param {HTMLElement} element The element that triggered the event.
	 * @param {Event} event The blur event.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.blurCallback_ =
			function($context, element, event) {
		this.toggleFeature_($(event.target), false);
	};


	/**
	 * Called everytime a carousel feature is focused.
	 * @param {jQueryObject} $context The context in which ocurred the event.
	 * @param {HTMLElement} element The element that triggered the event.
	 * @param {Event} event The focus event.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.focusCallback_ =
			function($context, element, event) {
		this.toggleFeature_($(event.target), true);
	};


	/**
	 * Called everytime an image loading is finished.
	 * @param {jQueryObject} $context The context in which ocurred the event.
	 * @param {Event} event The load event.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.loadCallback_ =
			function($context, event) {
		// Remove download progress indicators.
		var $targetElement = $(event.target).parents('.mover');

		if ($targetElement.hasClass('mover')) {
			$targetElement.find('.pkp_helpers_progressIndicator').hide();
			$targetElement.find('img').parent('div').
					addClass('pkp_helpers_black_bg');

			if ($targetElement.hasClass('roundabout-in-focus')) {
				this.toggleFeature_($('.roundabout-in-focus'), true);
			}
		}
	};


	/**
	 * Called everytime user clicks on carousel items.
	 * @param {HTMLElement} element The element that triggered the event.
	 * @param {Event} event The click event.
	 * @return {boolean} Returns event handling status.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.clickHandler_ =
			function(element, event) {
		var $item = $(element);

		// Avoid any action if user clicked on a placeholder.
		if ($item.hasClass('placeholder')) {
			return false;
		} else {
			this.getHtmlElement().find('ul.pkp_catalog_carousel').
					roundabout('animateToChild', $item.index());
			return true;
		}
	};


	/**
	 * Next item control click handler.
	 * @param {HTMLElement} element The element that triggered the event.
	 * @param {Event} event The click event.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.nextItemClickHandler_ =
			function(element, event) {
		this.moveItems_(1);
	};


	/**
	 * Previous item control click handler.
	 * @param {HTMLElement} element The element that triggered the event.
	 * @param {Event} event The click event.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.previousItemClickHandler_ =
			function(element, event) {
		this.moveItems_(-1);
	};


	/**
	 * Move the carousel the passed number times.
	 * @param {number} itemsToMove The number of items to move. If the
	 * passed value is negative, the carousel will go back.
	 * @return {boolean} Success status.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.moveItems_ =
			function(itemsToMove) {
		var $currentItem = $('.roundabout-in-focus', this.getHtmlElement()),
				currentItemIndex = $currentItem.index(),
				carouselItemsNumber = $('.mover', this.getHtmlElement()).length,
				$targetItem;

		// Allow foward items looping (begins with the first item if user reachs
		// the last one and click to move foward).
		if (currentItemIndex == carouselItemsNumber - 1 && itemsToMove > 0) {
			currentItemIndex = -1;
		}

		$targetItem = $($('li', this.getHtmlElement()).
				get(currentItemIndex + itemsToMove));

		if ($targetItem.hasClass('mover') && !$targetItem.hasClass('placeholder')) {
			this.getHtmlElement().find('ul.pkp_catalog_carousel').
					roundabout('animateToChild', $targetItem.index());
			return true;
		} else {
			return false;
		}
	};


	/**
	 * Applies the carousel plugin (roundabout).
	 * See http://fredhq.com/projects/roundabout
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.applyCarouselPlugin_ =
			function() {

		var $containerElement = this.getHtmlElement(),
				minScale = this.getMinScale_(),
				firstItemIndex,
				itemsNumber, relativeIndex, startingChild;

		// Add placeholder carousel items, if needed.
		this.addPlaceholders_();

		// Get the start item index.
		firstItemIndex = $('li.mover', $containerElement)
				.not('.placeholder').first().index();
		itemsNumber = $('li.mover', $containerElement).not('.placeholder').length;
		relativeIndex = Math.ceil(itemsNumber / 2);
		startingChild = firstItemIndex + relativeIndex - 1;

		// The html must be visible, otherwise the plugin will not
		// be correctly applied.
		$containerElement.parent().show();

		// Configure carousel plugin.
		$('ul.pkp_catalog_carousel', $containerElement).roundabout({
			minZ: 0,
			maxZ: 360,
			minOpacity: 1,
			minScale: minScale,
			duration: this.TRANSITION_DURATION_,
			triggerFocusEvents: true,
			triggerBlurEvents: true,
			clickToFocus: false,
			startingChild: startingChild
		});
	};


	/**
	 * Execute code to toggle the feature (focused or not).
	 * @param {jQueryObject} $feature The feature that will be toggled.
	 * @param {boolean} show True if focused and false if not.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.toggleFeature_ =
			function($feature, show) {
		var $img = $('img', $feature),
				$detailsElement = $('.details_box', $feature),
				toggleDetailsDuration = 250,
				opacityValue = 1,
				imgAnimationDuration = this.TRANSITION_DURATION_;

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


	/**
	 * Add placeholders items to the carousel to make sure we have
	 * at least 5 items at the same time in carousel view.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.addPlaceholders_ =
			function() {

		var $carousel = this.getHtmlElement().find('ul.pkp_catalog_carousel'),
				itemsNumber = $carousel.find('li.mover').length,
				placeholdersNumber = this.MAX_PLACEHOLDER_NUMBER_ - itemsNumber,
				i;

		if (placeholdersNumber > 0) {
			for (i = placeholdersNumber; i > 0; i--) {
				$carousel.prepend('<li class="mover placeholder"></li>');
			}
		}
	};


	/**
	 * Get minimum items scale, based on the current number of carousel items.
	 * This will make sure that the layout will be the best one for all cases.
	 * @return {number} The minimum item scale.
	 * @private
	 */
	$.pkp.pages.catalog.CarouselHandler.prototype.getMinScale_ =
			function() {
		// Find the items number, including placeholders.
		var itemsNumber = this.getHtmlElement().find('li').length,
				minScale = 0.3 - 0.003 * (itemsNumber * 4);

		if (minScale < 0.05) {
			return 0.05;
		} else {
			return minScale;
		}
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
