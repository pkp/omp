/**
 * @defgroup js_controllers_modals_expeditedSubmission_form
 */
/**
 * @file js/controllers/modals/expeditedSubmission/form/ExpeditedSubmissionMetadataForm.js
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ExpeditedSubmissionMetadataFormHandler
 * @ingroup js_controllers_modals_expeditedSubmission_form
 *
 * @brief Handle the expedited submission form.
 */
(function($) {

	/** @type {Object} */
	$.pkp.controllers.modals.expeditedSubmission =
			$.pkp.controllers.modals.expeditedSubmission || {form: { } };



	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.AjaxFormHandler
	 *
	 * @param {jQueryObject} $form the wrapped HTML form element.
	 * @param {Object} options form options.
	 */
	$.pkp.controllers.modals.expeditedSubmission.form.
			ExpeditedSubmissionMetadataFormHandler = function($form, options) {

		this.parent($form, options);

		$('[name^="salesType"]', $form).change(
				this.callbackWrapper(this.setPrices));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.modals.expeditedSubmission.form.
					ExpeditedSubmissionMetadataFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	/**
	 * Method to add the contents of the Name field to the end
	 * of the autocomplete URL
	 * @param {Object} eventObject The html element that changed.
	 */
	$.pkp.controllers.modals.expeditedSubmission.form.
			ExpeditedSubmissionMetadataFormHandler.prototype.setPrices =
			function(eventObject) {

		var $form = this.getHtmlElement(),
				salesType = $form.find('[name^="salesType"]:checked').val(),
				$price = $form.find('[id^="price"]');

		if (salesType == 'openAccess') {
			$price.attr('disabled', 'disabled');
			$price.val('0');
		} else if (salesType == 'notAvailable') {
			$price.attr('disabled', 'disabled');
			$price.val('');
		} else {
			$price.removeAttr('disabled');
		}
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
