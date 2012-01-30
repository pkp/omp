/**
 * @defgroup js_controllers_informationCenter_form
 */
jQuery.pkp.controllers.informationCenter.form =
			jQuery.pkp.controllers.informationCenter.form || { };


/**
 * @file js/controllers/informationCenter/form/InformationCenterNotifyHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationCenterNotifyHandler
 * @ingroup js_controllers_informationCenter_form
 *
 * @brief Handle Information Center notification forms.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.AjaxFormHandler
	 *
	 * @param {jQuery} $form the wrapped HTML form element.
	 * @param {Object} options form options.
	 */
	$.pkp.controllers.informationCenter.form.InformationCenterNotifyHandler =
			function($form, options) {

		this.parent($form, options);

		// Set the URL to retrieve templates from.
		if (options.templateUrl) {
			this.templateUrl_ = options.templateUrl;
		}

		// Attach form elements events.
		$form.find('#template').change(
				this.callbackWrapper(this.selectTemplateHandler_));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.informationCenter.form.InformationCenterNotifyHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Private properties
	//
	/**
	 * The URL to use to retrieve template bodies
	 * @private
	 * @type {string?}
	 */
	$.pkp.controllers.informationCenter.form.InformationCenterNotifyHandler.prototype.templateUrl_ = null;


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
	$.pkp.controllers.informationCenter.form.InformationCenterNotifyHandler.prototype.selectTemplateHandler_ =
			function(sourceElement, event) {

		var $form = this.getHtmlElement();
		$.post(this.templateUrl_, $form.find('#template').serialize(),
				this.callbackWrapper(this.updateTemplate), 'json');
	};


	/**
	 * Internal callback to replace the textarea with the contents of the
	 * template body.
	 *
	 * @param {HTMLElement} formElement The wrapped HTML form.
	 * @param {Object} jsonData The data returned from the server.
	 * @return {boolean} The response status.
	 */
	$.pkp.controllers.informationCenter.form.InformationCenterNotifyHandler.prototype.updateTemplate =
			function(formElement, jsonData) {

		$form = this.getHtmlElement();
		jsonData = this.handleJson(jsonData);
		if (jsonData !== false) {
			if (jsonData.content !== '') {
				$form.find('textarea[name="message"]').val(jsonData.content);
			}
		}
		return jsonData.status;
	};

/** @param {jQuery} $ jQuery closure. */
})(jQuery);
