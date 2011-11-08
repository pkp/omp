/**
 * @defgroup js_site_form
 */
// Create the namespace.
jQuery.pkp.controllers.modals.submissionMetadata =
			jQuery.pkp.controllers.modals.submissionMetadata ||
			{ };


/**
 * @file js/controllers/modals/submissionMetadata/SelectMonographFormHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectMonographFormHandler
 * @ingroup js_controlelrs_modals_submissionMetadata
 *
 * @brief Handler for the monograph selection form, part of catalog entry
 *   creation.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.FormHandler
	 *
	 * @param {jQuery} $form the wrapped HTML form element.
	 * @param {Object} options form options.
	 */
	$.pkp.controllers.modals.submissionMetadata.SelectMonographFormHandler =
			function($form, options) {

		this.parent($form, options);

		// Store the monograph list fetch URL for later
		this.getSubmissionsUrl_ = options.getSubmissionsUrl;

		// Expose the selectMonograph event to the containing element.
		this.publishEvent('selectMonograph');

		// Attach form elements events.
		$('#monographSelect', $form).change(
				this.callbackWrapper(this.selectMonographHandler_));

		// Load the list of submissions.
		$.get(this.getSubmissionsUrl_,
				this.callbackWrapper(this.setSubmissionList_), 'json');
	};

	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.modals.submissionMetadata.SelectMonographFormHandler,
			$.pkp.controllers.form.FormHandler);


	//
	// Private properties
	//
	/**
	 * The URL to be called to fetch a list of submissions.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.modals.submissionMetadata.SelectMonographFormHandler.
			prototype.getSubmissionsUrl_ = '';


	//
	// Private helper methods
	//
	/**
	 * Switch between monographs.
	 *
	 * @param {HTMLElement} sourceElement The element that
	 *  issued the event.
	 * @param {Event} event The triggering event.
	 * @private
	 */
	$.pkp.controllers.modals.submissionMetadata.SelectMonographFormHandler.prototype.selectMonographHandler_ =
			function(sourceElement, event) {

		var $sourceElement = $(sourceElement);
		var monographId = $sourceElement.val();

		this.trigger('selectMonograph', monographId);
	};


	/**
	 * Set the list of available monographs.
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 * @private
	 */
	$.pkp.controllers.modals.submissionMetadata.SelectMonographFormHandler.prototype.setSubmissionList_ =
			function(ajaxContext, jsonData) {

		jsonData = this.handleJson(jsonData);
		var $form = this.getHtmlElement();
		var $select = $form.find('#monographSelect');

		// Clear the "Loading..." message
		$select.empty();

		// For each supplied option, add it to the select menu.
		for (var monographId in jsonData.content) {
			var $option = $('<option/>');
			$option.attr('value', monographId);
			$option.text(jsonData.content[monographId]);
			$select.append($option);
		}
	};


})(jQuery);
