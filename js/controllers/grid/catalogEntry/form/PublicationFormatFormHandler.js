/**
 * @defgroup js_controllers_grid_catalogEntry_form
 */
/**
 * @file js/controllers/grid/catalogEntry/form/PublicationFormatFormHandler.js
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatFormHandler
 * @ingroup js_controllers_grid_catalogEntry_form
 *
 * @brief Handle the publication format form.
 */
(function($) {

	/** @type {Object} */
	$.pkp.controllers.grid.catalogEntry =
			$.pkp.controllers.grid.catalogEntry ||
			{ form: { } };



	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.AjaxFormHandler
	 *
	 * @param {jQueryObject} $form the wrapped page element.
	 * @param {Object} options handler options.
	 */
	$.pkp.controllers.grid.catalogEntry.form.PublicationFormatFormHandler =
			function($form, options) {
		this.parent($form, options);

		this.remoteFormat_ = options.remoteFormat;
		if (this.remoteFormat_) {
			$('#remotelyHostedContent').prop('checked', true);
			$('#remote').show(20);
		} else {
			$('#remotelyHostedContent').prop('checked', false);
			$('#remote').hide(20);
		}

		$('#remotelyHostedContent').change(this.callbackWrapper(this.toggleRemote_));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.grid.catalogEntry.form.PublicationFormatFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Private methods.
	//
	/**
	 * Internal callback called on checkbox change to show or hide
	 * remote URL input field.
	 * @private
	 * @param {HTMLElement} element The remotely hosted content checkbox.
	 * @param {Event} event The event that triggered the checkbox.
	 * @return {boolean} true.
	 */
	$.pkp.controllers.grid.catalogEntry.form.PublicationFormatFormHandler.
			prototype.toggleRemote_ = function(element, event) {

		if ($('#remotelyHostedContent').prop('checked')) {
			// show the remote URL input field
			$('#remote').show(20);
		} else {
			// hide and clear the remote URL input field
			$('#remote').hide(20);
			$('input[id^="remoteURL"]').val('');
		}
		return true;
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
