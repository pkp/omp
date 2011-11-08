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

		// Bind for changes in the note list (e.g.  new note or delete)
		this.bind('selectMonograph', this.selectMonographHandler);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.modals.submissionMetadata.MonographlessCatalogEntryHandler,
			$.pkp.classes.Handler
	);


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

		// TBI: Load the metadata form into #metadataFormContainer
		// for the given monographId.
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
