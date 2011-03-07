/**
 * @file js/controllers/informationCenter/NewNoteHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewNoteHandler
 * @ingroup js_controllers_informationCenter
 *
 * @brief Information center "notes" handler.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.FormHandler
	 *
	 * @param {jQuery} $form A wrapped HTML element that
	 *  represents the form.
	 * @param {Object} options Object containing options.
	 */
	$.pkp.controllers.informationCenter.NewNoteHandler =
			function($form, options) {

		this.parent($form, options);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.informationCenter.NewNoteHandler,
			$.pkp.controllers.form.FormHandler
	);


	/**
	 * @inheritDoc
	 */
	$.pkp.controllers.informationCenter.NewNoteHandler.
			prototype.handleResponse = function(formElement, jsonData) {

		if (jsonData.status === true) {
			// Trigger the note added event.
			this.trigger('noteAdded');
		}

		this.parent('handleResponse', formElement, jsonData);
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
