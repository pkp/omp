/**
 * @defgroup js_controllers_grid_files_proof_form
 */
/**
 * @file js/controllers/grid/files/proof/form/ApprovedProofFormHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApprovedProofFormHandler
 * @ingroup js_controllers_grid_files_proof_form
 *
 * @brief Approved proof files form handler.
 */
(function($) {

	/** @type {Object} */
	$.pkp.controllers.grid.files.proof =
			$.pkp.controllers.grid.files.proof || {form: {} };



	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.AjaxFormHandler
	 *
	 * @param {jQuery} $formElement A wrapped HTML element that
	 *  represents the approved proof form interface element.
	 * @param {Object} options Tabbed modal options.
	 */
	$.pkp.controllers.grid.files.proof.form.ApprovedProofFormHandler =
			function($formElement, options) {
		this.parent($formElement, options);

		// Disable/enable the price field based on sales mode
		$formElement.find('#notAvailable, #openAccess, #directSales')
				.click(this.callbackWrapper(this.checkHandler_));

		var $priceElement = $('input[id^="price"]');

		// Disable/enable the submit controls based on a price being entered
		$priceElement.change(this.callbackWrapper(this.changeHandler_));

		// Set up the default enabled/disabled state of the checkbox controls
		if ($priceElement.attr('value') === '') {
			$('#notAvailable').attr('checked', true);
			$priceElement.attr('disabled', true);
		} else if ($priceElement.attr('value') === '0') {
			$('#openAccess').attr('checked', true);
			$priceElement.attr('disabled', true).attr('value', '');
		} else {
			$('#directSales').attr('checked', true);
		}
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.grid.files.proof.form.ApprovedProofFormHandler,
			$.pkp.controllers.form.AjaxFormHandler
	);


	//
	// Private Methods
	//
	/**
	 * Callback that will be activated when payment mode is changed.
	 *
	 * @private
	 *
	 * @param {string} radioButton The element the event was triggered on.
	 * @return {boolean} Always returns true.
	 */
	$.pkp.controllers.grid.files.proof.form.ApprovedProofFormHandler.prototype.
			checkHandler_ = function(radioButton) {

		var $priceElement = $('input[id^="price"]');
		if ($(radioButton).attr('id') === 'directSales') {
			$priceElement.attr('disabled', false);
			if ($priceElement.val() === '') {
				this.disableFormControls_();
			} else {
				this.enableFormControls_();
			}
		} else {
			$priceElement.attr('disabled', true);
			this.enableFormControls_();
		}

		return true;
	};


	/**
	 * Callback that will be activated when the price field is changed.
	 *
	 * @private
	 *
	 * @param {string} textControl The element the event was triggered on.
	 * @return {boolean} Always returns true.
	 */
	$.pkp.controllers.grid.files.proof.form.ApprovedProofFormHandler.prototype.
			changeHandler_ = function(textControl) {

		var $priceElement = $(textControl);
		if ($priceElement.val() === '' || isNaN($priceElement.val())) {
			this.disableFormControls_();
		} else {
			this.enableFormControls_();
		}

		return true;
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
