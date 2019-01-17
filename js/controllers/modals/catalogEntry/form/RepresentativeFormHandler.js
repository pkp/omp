/**
 * @defgroup js_controllers_modal_catalogEntry_form
 */
/**
 * @file js/controllers/modals/catalogEntry/form/RepresentativeFormHandler.js
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RepresentativeFormHandler
 * @ingroup js_controllers_modal_catalogEntry_form
 *
 * @brief Handle monograph representative forms.
 */
(function($) {

	/** @type {Object} */
	$.pkp.controllers.modals.catalogEntry =
			$.pkp.controllers.modals.catalogEntry || {form: { } };



	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.AjaxFormHandler
	 *
	 * @param {jQueryObject} $form the wrapped HTML form element.
	 * @param {Object} options form options.
	 */
	$.pkp.controllers.modals.catalogEntry.form.RepresentativeFormHandler =
			function($form, options) {

		this.parent($form, options);
		// Attach form elements events.
		$form.find(':radio').change(
				this.callbackWrapper(this.radioToggleHandler_));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.modals.catalogEntry.form.RepresentativeFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Private methods
	//
	/**
	 * Respond to an "item selected" call by triggering a published event.
	 *
	 * @param {HTMLElement} sourceElement The element that
	 *  issued the event.
	 * @param {Event} event The triggering event.
	 * @private
	 */
	$.pkp.controllers.modals.catalogEntry.form.RepresentativeFormHandler.
			prototype.radioToggleHandler_ = function(sourceElement, event) {

		var $form = this.getHtmlElement();
		if (sourceElement.id == 'agent') {
			// this 'hidden' class on parent may be set from within the template
			$form.find('#agentRole').parent().removeClass('hidden');
			$form.find('#agentRole').show();
			$form.find('#supplierRole').hide();
		} else if (sourceElement.id == 'supplier') {
			$form.find('#agentRole').hide();
			$form.find('#supplierRole').parent().removeClass('hidden');
			$form.find('#supplierRole').show();
		}
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
