/**
 * @file js/pages/catalog/MonographHandler.js
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
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
	 * @extends $.pkp.controllers.linkAction.LinkActionHandler
	 *
	 * @param {jQueryObject} $monographsContainer The HTML element encapsulating
	 *  the monograph list div.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.manageCatalog.MonographHandler =
			function($monographsContainer, options) {

		// Initialize and save parameters.
		this.parent($monographsContainer, options);
		this.submissionId_ = options.submissionId;
		this.seq_ = options.seq;
		this.setFeaturedUrlTemplate_ = options.setFeaturedUrlTemplate;
		this.setNewReleaseUrlTemplate_ = options.setNewReleaseUrlTemplate;
		this.isFeatured_ = options.isFeatured;
		this.isNewRelease_ = options.isNewRelease;
		this.datePublished_ = options.datePublished;
		this.workflowUrl_ = options.workflowUrl;
		this.catalogUrl_ = options.catalogUrl;

		// Attach the view type handlers, if links exist.
		$monographsContainer.find('a[id^="featureMonograph"]').click(
				this.callbackWrapper(this.featureButtonHandler_));

		$monographsContainer.find('a[id^="releaseMonograph"]').click(
				this.callbackWrapper(this.releaseButtonHandler_));

		// prevent the whole li element from capturing the linkAction click event.
		$monographsContainer.unbind('click');

		$monographsContainer.find('a[id^="catalogEntry"]').click(
				this.callbackWrapper(this.activateAction));

		$monographsContainer.find('a[id^="itemWorkflow"]').click(
				this.callbackWrapper(this.workflowButtonHandler_));

		$monographsContainer.find('a[id^="publicCatalog"]').click(
				this.callbackWrapper(this.publicCatalogButtonHandler_));

		// Expose list events to the container
		this.publishEvent('monographListChanged');
		this.publishEvent('monographSequencesChanged');

		// Bind for enter/exit of Feature mode
		this.bind('changeDragMode', this.changeDragModeHandler_);

		// Bind for setting a new sequence
		this.bind('setSequence', this.setSequenceHandler_);

		// position the (hidden) feature tools over the book cover image.
		var imagePosition = $monographsContainer.
				find('.pkp_manageCatalog_monograph_image').position(),
				featuresPosition = $monographsContainer.
						find('.pkp_manageCatalog_featureTools').position();

		$monographsContainer.find('.pkp_manageCatalog_featureTools').
				css('position', 'relative').
				css('top', imagePosition.top - featuresPosition.top);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.manageCatalog.MonographHandler,
			$.pkp.controllers.linkAction.LinkActionHandler);


	//
	// Private Properties
	//
	/**
	 * The sequence (sort order) of this monograph entry.
	 * @private
	 * @type {number?}
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.seq_ = null;


	/**
	 * The publication date of this monograph entry.
	 * @private
	 * @type {Date?}
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.datePublished_ = null;


	/**
	 * The ID of this monograph entry.
	 * @private
	 * @type {number?}
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.submissionId_ = null;


	/**
	 * The current state of the featured flag.
	 * @private
	 * @type {boolean?}
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.isFeatured_ = null;


	/**
	 * The current state of the new releasea flag.
	 * @private
	 * @type {boolean?}
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.isNewRelease_ = null;


	/**
	 * The URL to the workflow of this monograph.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.workflowUrl_ = null;


	/**
	 * The URL to the public catalog page for this monograph.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.catalogUrl_ = null;


	/**
	 * The URL template used to set the featured status of a monograph.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.manageCatalog.MonographHandler.
			prototype.setFeaturedUrlTemplate_ = null;


	/**
	 * The URL template used to set the new release status of a monograph.
	 * @private
	 * @type {string?}
	 */
	$.pkp.pages.manageCatalog.MonographHandler.
			prototype.setNewReleaseUrlTemplate_ = null;


	//
	// Public Methods
	//
	/**
	 * Get the date published for this monograph.
	 * @return {Date} Date published.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.getDatePublished =
			function() {

		return this.datePublished_;
	};


	/**
	 * Get the featured flag for this monograph.
	 * @return {boolean?} Featured flag.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.getFeatured =
			function() {

		return this.isFeatured_;
	};


	/**
	 * Get the sort sequence for this monograph.
	 * @return {number?} Sequence.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.getSeq =
			function() {

		return this.seq_;
	};


	/**
	 * Get the ID for this monograph.
	 * @return {number?} Monograph ID.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.getId =
			function() {

		return this.submissionId_;
	};


	//
	// Private Methods
	//
	/**
	 * Get the URL to set a monograph's published state.
	 * @private
	 * @return {string} The URL to use to set the monograph feature state.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.getSetFeaturedUrl_ =
			function() {

		return this.setFeaturedUrlTemplate_
				.replace('FEATURED_DUMMY', this.isFeatured_ ? '1' : '0')
				.replace('SEQ_DUMMY', this.isFeatured_ ?
				this.seq_ : $.pkp.cons.REALLY_BIG_NUMBER);
	};


	/**
	 * Get the URL to set a monograph's published state.
	 * @private
	 * @return {string} The URL to use to set the monograph feature state.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.getSetNewReleaseUrl_ =
			function() {

		return this.setNewReleaseUrlTemplate_
				.replace('RELEASE_DUMMY', this.isNewRelease_ ? '1' : '0');
	};


	/**
	 * Callback that will be activated when "feature" is toggled
	 *
	 * @private
	 *
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.featureButtonHandler_ =
			function() {

		// Invert "featured" state
		this.isFeatured_ = this.isFeatured_ ? false : true;

		// Tell the server
		$.get(this.getSetFeaturedUrl_(),
				this.callbackWrapper(this.handleSetFeaturedResponse_), 'json');

		// Stop further event processing
		return false;
	};


	/**
	 * Callback that will be activated when "new release" is toggled
	 *
	 * @private
	 *
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.releaseButtonHandler_ =
			function() {

		// Invert "release" state
		this.isNewRelease_ = this.isNewRelease_ ? false : true;

		// Tell the server
		$.get(this.getSetNewReleaseUrl_(),
				this.callbackWrapper(this.handleSetNewReleaseResponse_), 'json');

		// Stop further event processing
		return false;
	};


	/**
	 * Callback that will be activated when the submission
	 * workflow action is clicked
	 *
	 * @private
	 *
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.
			workflowButtonHandler_ = function() {

		if (this.workflowUrl_) {
			document.location = this.workflowUrl_;
		}
		// Stop further event processing
		return false;
	};


	/**
	 * Callback that will be activated when the title of the
	 * submission is clicked.
	 *
	 * @private
	 *
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.
			publicCatalogButtonHandler_ = function() {

		if (this.catalogUrl_) {
			document.location = this.catalogUrl_;
		}
		// Stop further event processing
		return false;
	};


	/**
	 * Handle a callback after a "set featured" request returns with
	 * a response.
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 * @return {boolean} Message handling result.
	 * @private
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.
			handleSetFeaturedResponse_ = function(ajaxContext, jsonData) {

		var processedJsonData = this.handleJson(jsonData),
				$htmlElement = this.getHtmlElement();

		// Record the new state of the isFeatured flag and sequence
		this.isFeatured_ = processedJsonData.content !== null ? true : false;
		this.seq_ = processedJsonData.content;

		// Update the UI
		if (this.isFeatured_) {
			// Now featured; previously not.
			$htmlElement.removeClass('not_sortable')
				.addClass('pkp_helpers_moveicon');
			$htmlElement.find('.star')
				.removeClass('star')
				.addClass('star_highlighted');
		} else {
			// No longer featured.
			$htmlElement.addClass('not_sortable')
				.removeClass('pkp_helpers_moveicon');
			$htmlElement.find('.star_highlighted')
				.addClass('star')
				.removeClass('star_highlighted');
		}

		// Let the container know to reset the sortable list
		this.trigger('monographListChanged');
		return false;
	};


	/**
	 * Handle a callback after a "set new release" request returns with
	 * a response.
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 * @return {boolean} Message handling result.
	 * @private
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.
			handleSetNewReleaseResponse_ = function(ajaxContext, jsonData) {

		var processedJsonData = this.handleJson(jsonData),
				$htmlElement = this.getHtmlElement();

		// Record the new state of the isNewRelease flag and sequence
		this.isNewRelease_ = processedJsonData.content !== null ? true : false;

		// Update the UI
		if (this.isNewRelease_) {
			// New release; previously not.
			$htmlElement.find('.release')
				.removeClass('release')
				.addClass('release_highlighted');
		} else {
			// No longer a new release.
			$htmlElement.find('.release_highlighted')
				.addClass('release')
				.removeClass('release_highlighted');
		}

		return false;
	};


	/**
	 * Handle the "drag mode changed" event to handle drag mode
	 * UI configuration (i.e. the drag icon upon mouseover)
	 *
	 * @private
	 *
	 * @param {jQueryObject} callingHandler The handler
	 *  that triggered the event.
	 * @param {Event} event The event.
	 * @param {number} canDrag 1/true iff the user should be able to drag.
	 * @return {boolean} The event handling chain status.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.
			prototype.changeDragModeHandler_ =
			function(callingHandler, event, canDrag) {

		var $htmlElement = this.getHtmlElement();
		if (canDrag) {
			if (!$htmlElement.hasClass('not_sortable')) {
				$htmlElement.addClass('pkp_helpers_moveicon');
			}
		} else {
			$htmlElement.removeClass('pkp_helpers_moveicon');
		}

		// Stop processing
		return false;
	};


	/**
	 * Handle the "set sequence" event to move a monograph
	 *
	 * @private
	 *
	 * @param {jQueryObject} callingHandler The handler
	 *  that triggered the event.
	 * @param {Event} event The event.
	 * @param {number} seq New sequence number.
	 * @param {boolean} informServer True if the server should be informed.
	 * Default true.
	 * @return {boolean} The event handling chain status.
	 */
	$.pkp.pages.manageCatalog.MonographHandler.
			prototype.setSequenceHandler_ =
			function(callingHandler, event, seq, informServer) {

		// Default param value for informServer: true.
		if (typeof(informServer) === 'undefined') {
			informServer = true;
		}

		// Set the new sequence
		this.seq_ = seq;

		// Inform the server if it's required of us
		var callback = this.callbackWrapper(this.handleSetSequenceResponse_);
		if (informServer) {
			$.get(this.getSetFeaturedUrl_(), callback, 'json');
		}

		// Stop processing
		return false;
	};


	/**
	 * Handle a callback after a "set sequence" request returns with
	 * a response.
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 * @return {boolean} Message handling result.
	 * @private
	 */
	$.pkp.pages.manageCatalog.MonographHandler.prototype.
			handleSetSequenceResponse_ = function(ajaxContext, jsonData) {

		var processedJsonData = this.handleJson(jsonData);

		// We've received a bunch of sequences back; report changes
		this.trigger('monographSequencesChanged', processedJsonData.content);

		return false;
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
