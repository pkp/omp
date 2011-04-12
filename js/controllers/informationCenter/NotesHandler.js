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

		// Bind for changes in the note list (e.g.  new note or delete)
		this.bind('formSubmitted', this.handleRefreshNoteList);

		// Load a list of the current notes.
		this.loadNoteList_();
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.informationCenter.NotesHandler,
			$.pkp.classes.Handler
	);


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


	//
	// Public methods
	//
	/**
	 * Handle the "note added" event triggered by the
	 * note form whenever a new note is added.
	 *
	 * @param {$.pkp.controllers.form.AjaxFormHandler} callingForm The form
	 *  that triggered the event.
	 * @param {Event} event The upload event.
	 */
	$.pkp.controllers.informationCenter.NotesHandler.
			prototype.handleRefreshNoteList = function(callingForm, event) {
		this.loadNoteList_();
	};


	//
	// Private methods
	//
	$.pkp.controllers.informationCenter.NotesHandler.prototype.
			loadNoteList_ = function() {

		$.get(this.fetchUrl_, this.callbackWrapper(this.setNoteList_), 'json');
	};

	$.pkp.controllers.informationCenter.NotesHandler.prototype.
			setNoteList_ = function(formElement, jsonData) {

		jsonData = this.handleJson(jsonData);
		$('#notesList').replaceWith(jsonData.content);
	}


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
