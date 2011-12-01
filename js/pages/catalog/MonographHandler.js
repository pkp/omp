/**
 * @file js/pages/catalog/MonographHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographHandler
 * @ingroup js_pages_catalog
 *
 * @brief Handler for a monograph entry.
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
	$.pkp.pages.catalog.MonographHandler =
			function($monographsContainer, options) {

		// Initialize and save parameters.
		this.parent($monographsContainer, options);
		this.monographId_ = options.monographId;
		this.setFeaturedUrlTemplate_ = options.setFeaturedUrlTemplate;
		this.isFeatured_ = options.isFeatured;

		// Attach the view type handlers, if links exist.
		$monographsContainer.find('.star, .star_highlighted').click(
				this.callbackWrapper(this.featureButtonHandler_));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.MonographHandler,
			$.pkp.classes.Handler);


	//
	// Private Properties
	//
	/**
	 * The ID of this monograph entry.
	 * @private
	 * @type {int?}
	 */
	$.pkp.pages.catalog.MonographHandler.prototype.monographId_ = null;


	/**
	 * The current state of the featured flag.
	 * @private
	 * @type {boolean?}
	 */
	$.pkp.pages.catalog.MonographHandler.prototype.isFeatured_ = null;


	/**
	 * The URL template used to set the featured status of a monograph.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.catalog.MonographHandler.
			prototype.setFeaturedUrlTemplate_ = null;


	//
	// Private Methods
	//
	/**
	 * Get the URL to set a monograph's published state.
	 * @private
	 * @return {String} The URL to use to set the monograph feature state.
	 */
	$.pkp.pages.catalog.MonographHandler.prototype.getSetFeaturedUrl_ =
			function() {

		return this.setFeaturedUrlTemplate_.replace(
				'FEATURED_DUMMY', this.isFeatured_ ? 0 : 1);
	};


	/**
	 * Callback that will be activated when "feature" is toggled
	 *
	 * @private
	 *
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.catalog.MonographHandler.prototype.featureButtonHandler_ =
			function() {

		$.get(this.getSetFeaturedUrl_(),
				this.callbackWrapper(this.handleSetFeaturedResponse_), 'json');

		// Stop further event processing
		return false;
	};


	/**
	 * Handle a callback after a "set featured" request returns with
	 * a response.
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 * @private
	 */
	$.pkp.pages.catalog.MonographHandler.prototype.handleSetFeaturedResponse_ =
			function(ajaxContext, jsonData) {

		jsonData = this.handleJson(jsonData);

		// Record the new state of the isFeatured flag
		this.isFeatured_ = jsonData.content ? 1 : 0;

		// Update the UI
		if (this.isFeatured_) {
			// Now featured; previously not.
			this.getHtmlElement().find('.star')
				.removeClass('star')
				.addClass('star_highlighted');
		} else {
			// No longer featured.
			this.getHtmlElement().find('.star_highlighted')
				.addClass('star')
				.removeClass('star_highlighted');
		}
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
