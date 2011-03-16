/**
 * @defgroup js_controllers_grid_users_stageParticipant_form
 */
// Create the namespace.
jQuery.pkp.controllers.grid.users = jQuery.pkp.controllers.grid.users ||
			{ stageParticipant: { form: { } } };


/**
 * @file js/controllers/grid/users/stageParticipant/form/StageParticipantFormHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantFormHandler
 * @ingroup js_controllers_grid_users_stageParticipant_form
 *
 * @brief Handle the stage participant form.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.ClientFormHandler
	 *
	 * @param {jQuery} $form the wrapped HTML form element.
	 * @param {Object} options form options.
	 */
	$.pkp.controllers.grid.users.stageParticipant.form.StageParticipantFormHandler =
			function($form, options) {

		this.parent($form, options);

		this.listBuilderUrl_ = options.listBuilderUrl;
		$('#userGroupId', $form).change(
				this.callbackWrapper(this.refreshListBuilder));

		// Issue a generic "data changed" event when submitting the
		// form. This can be catched by any parent widget to update
		// dependent widgets.
		this.bind('formSubmitted', function() {
			this.trigger('dataChanged');
		});
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.grid.users.stageParticipant.form.
					StageParticipantFormHandler,
			$.pkp.controllers.form.ClientFormHandler);


	//
	// Private properties
	//
	/**
	 * The URL of the "fetch list builder" operation.
	 * @private
	 * @type {?string}
	 */
	$.pkp.controllers.grid.users.stageParticipant.form.StageParticipantFormHandler.
			listBuilderUrl_ = null;


	//
	// Public methods
	//
	/**
	 * Fetch a changed list builder from the server.
	 *
	 * @param {HTMLElement} select The user group drop down.
	 * @param {Event} event The change event.
	 */
	$.pkp.controllers.grid.users.stageParticipant.form.StageParticipantFormHandler.
			prototype.refreshListBuilder = function(select, event) {

		// FIXME: Implement this with a list builder refresh event, see #6193.
		var $form = this.getHtmlElement();
		$.post(this.listBuilderUrl_, $form.serialize(),
				this.callbackWrapper(this.replaceListBuilder), 'json');
	};


	/**
	 * Insert the updated list builder into the form.
	 *
	 * @param {Object} ajaxOptions The options that were passed into
	 *  the AJAX call.
	 * @param {Object} jsonData The data returned from the server.
	 */
	$.pkp.controllers.grid.users.stageParticipant.form.StageParticipantFormHandler.
			prototype.replaceListBuilder = function(ajaxOptions, jsonData) {

		jsonData = this.handleJson(jsonData);
		if (jsonData !== false) {
			// Load new listbuilder into #submissionParticipantsContainer
			var $form = this.getHtmlElement();
			$('#submissionParticipantsContainer', $form).html(jsonData.content);
		}
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
