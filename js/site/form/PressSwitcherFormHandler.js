/**
 * @defgroup js_site_form
 */
// Create the namespace.
jQuery.pkp.site =
			jQuery.pkp.site ||
			{ form: { } };


/**
 * @file js/site/form/PressSwitcherFormHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSwitcherFormHandler
 * @ingroup js_site_form
 *
 * @brief Handler for the press switcher.
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
	$.pkp.site.form.PressSwitcherFormHandler =
			function($form, options) {

		this.parent($form, options);

		// Attach form elements events.
		$('#pressSwitcherSelect', $form).change(
				this.callbackWrapper(this.switchPressHandler_));
	};

	$.pkp.classes.Helper.inherits(
			$.pkp.site.form.PressSwitcherFormHandler,
			$.pkp.controllers.form.FormHandler);


	//
	// Private helper methods
	//
	/**
	 * Switch between presses.
	 *
	 * @param {HTMLElement} sourceElement The element that
	 *  issued the event.
	 * @param {Event} event The triggering event.
	 * @private
	 */
	$.pkp.site.form.PressSwitcherFormHandler.prototype.switchPressHandler_ =
			function(sourceElement, event) {

		var $sourceElement = $(sourceElement);
		var link = $sourceElement.val();

		if (link !== '') {
			this.trigger('redirectRequested', link);
		}
	};


})(jQuery);
