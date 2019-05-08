/**
 * @file js/controllers/modals/catalogEntry/form/PublicationFormatMetadataFormHandler.js
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatMetadataFormHandler
 * @ingroup js_controllers_modal_catalogEntry_form
 *
 * @brief Handle monograph publication format forms on the catalog entry modal.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.AjaxFormHandler
	 *
	 * @param {jQueryObject} $form the wrapped HTML form element.
	 * @param {Object} options form options.
	 */
	$.pkp.controllers.modals.catalogEntry.form.
			PublicationFormatMetadataFormHandler = function($form, options) {

		this.parent($form, options);
		// Attach form elements events.
		$form.find('#override').click(
				this.callbackWrapper(this.overrideToggleHandler_));

		// initial setup.
		$form.find('#override').triggerHandler('click');
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.modals.catalogEntry.form.
					PublicationFormatMetadataFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Private methods
	//
	/**
	 * Toggles the availability of the fileSize field, to override the automatic
	 * calculation of the file sizes based on approved proofs.
	 *
	 * @param {HTMLElement} sourceElement The element that
	 *  issued the event.
	 * @param {Event} event The triggering event.
	 * @private
	 */
	$.pkp.controllers.modals.catalogEntry.form.
			PublicationFormatMetadataFormHandler.prototype.
					overrideToggleHandler_ = function(sourceElement, event) {

		var $form = this.getHtmlElement(),
				$fileSize = $form.find('[id^="fileSize"]');

		if ($(sourceElement).is(':checked')) {
			$fileSize.attr('disabled', '');
		} else {
			$fileSize.attr('disabled', 'disabled');
		}
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
