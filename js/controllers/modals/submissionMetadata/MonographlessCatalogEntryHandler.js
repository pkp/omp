/**
 * @file js/controllers/modals/submissionMetadata/MonographlessCatalogEntryHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographlessCatalogEntryHandler
 * @ingroup js_controllers_modals_submissionMetadata
 *
 * @brief JS controller for container element for the "add catalog entry" form, used
 * when a monograph needs to be chosen to work with.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $containerDiv A wrapped HTML element that
	 *  represents the container div interface element.
	 * @param {Object} options Optional Options.
	 */
	$.pkp.controllers.modals.submissionMetadata.MonographlessCatalogEntryHandler =
			function($containerDiv, options) {
		this.parent($containerDiv, options);

		// Save the URL template for the metadata form.
		this.metadataFormUrlTemplate_ = options.metadataFormUrlTemplate;

		// Bind for changes in the note list (e.g.  new note or delete)
		this.bind('selectMonograph', this.selectMonographHandler);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.modals.submissionMetadata.MonographlessCatalogEntryHandler,
			$.pkp.classes.Handler
	);

	//
	// Private properties
	//
	/**
	 * The URL template used to fetch the metadata edit form.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.modals.submissionMetadata.MonographlessCatalogEntryHandler.
			prototype.metadataFormUrlTemplate_ = '';


	//
	// Protected methods
	//
	/**
	 * Get the metadata edit form URL for the given stage and monograph ID.
	 *
	 * @param {String} monographId The monograph ID for the edit form.
	 * @param {String} stageId The stage ID for the edit form.
	 * @return {String} The URL for the metadata edit form.
	 */
	$.pkp.controllers.modals.submissionMetadata.MonographlessCatalogEntryHandler.
			prototype.getMetadataEditFormUrl_ = function(monographId, stageId) {

		// Look for MONOGRAPH_ID and STAGE_ID tokens in the URL and replace them.
		return this.metadataFormUrlTemplate_.replace('MONOGRAPH_ID', monographId).replace('STAGE_ID', stageId);
	};


	//
	// Public methods
	//
	/**
	 * Handle the "monograph selected" event triggered by the
	 * monograph select form to load the respective metadata form.
	 *
	 * @param {$.pkp.controllers.form.AjaxFormHandler} callingForm The form
	 *  that triggered the event.
	 * @param {Event} event The upload event.
	 * @param {String} monographId The selected monograph ID.
	 */
	$.pkp.controllers.modals.submissionMetadata.MonographlessCatalogEntryHandler.
			prototype.selectMonographHandler = function(callingForm, event, monographId) {

		// Fetch the form
		$.get(this.getMetadataEditFormUrl_(monographId, $.pkp.cons.WORKFLOW_STAGE_ID_PRODUCTION),
				this.callbackWrapper(this.showFetchedMetadataForm_), 'json');
	};

	/**
	 * Show a fetched metadata edit form.
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 * @private
	 */
	$.pkp.controllers.modals.submissionMetadata.MonographlessCatalogEntryHandler.
			prototype.showFetchedMetadataForm_ = function(ajaxContext, jsonData) {

		jsonData = this.handleJson(jsonData);

		// Find the container and remove all children.
		$metadataFormContainer = $('#metadataFormContainer');
		$metadataFormContainer.children().remove();

		// Replace it with the form content.
		$metadataFormContainer.append(jsonData.content);
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
