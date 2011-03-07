/**
 * @file js/controllers/informationCenter/NotesHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotesHandler
 * @ingroup js_controllers_informationCenter
 *
 * @brief Information center "notes" tab handler.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $notesDiv A wrapped HTML element that
	 *  represents the "notes" interface element.
	 * @param {Object} options Tabbed modal options.
	 */
	$.pkp.controllers.informationCenter.NotesHandler =
			function($notesDiv, options) {
		this.parent($notesDiv, options);

		// Store the list fetch URL for later
		this.fetchUrl_ = options.fetchUrl;

		// Bind for changes in the note list (e.g. on new note publication)
		this.bind('noteAdded', this.handleNoteAdded);

		// Load a list of the current notes.
		this.trigger('noteAdded');
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.informationCenter.NotesHandler,
			$.pkp.classes.Handler
	);


	/**
	 * Handle the "note added" event triggered by the
	 * note form whenever a new note is added.
	 *
	 * @param {$.pkp.controllers.form.FormHandler} callingForm The form
	 *  that triggered the event.
	 * @param {Event} event The upload event.
	 */
	$.pkp.controllers.informationCenter.NotesHandler.
			prototype.handleNoteAdded = function(callingForm, event) {

		$.get(this.fetchUrl_, this.callbackWrapper(function(formElement, jsonData) {
			$('#notesList').replaceWith(jsonData.content);
		}), 'json');
	};


	//
	// Private properties
	//
	/**
	 * The URL to be called to fetch a list of notes.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.informationCenter.NotesHandler.
			prototype.fetchUrl_ = '';

/** @param {jQuery} $ jQuery closure. */
})(jQuery);
