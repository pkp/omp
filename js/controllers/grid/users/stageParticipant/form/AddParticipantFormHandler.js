/**
 * @defgroup js_controllers_grid_users_stageParticipant_form
 */
// Create the namespace.
jQuery.pkp.controllers.grid.users.stageParticipant =
			jQuery.pkp.controllers.grid.users.stageParticipant ||
			{ form: { } };

/**
 * @file js/controllers/grid/users/stageParticipant/AddParticipantFormHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddParticipantFormHandler
 * @ingroup js_controllers_grid_users_stageParticipant_form
 *
 * @brief Handle the "add participant" form.
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
	$.pkp.controllers.grid.users.stageParticipant.form.AddParticipantFormHandler =
			function($form, options) {

		this.parent($form, options);

		$('#userGroupId', $form).change(
				this.callbackWrapper(this.addUserIdToAutocompleteUrl));

	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.grid.users.stageParticipant.form.AddParticipantFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Public methods
	//
	/**
	 * Method to add the userGroupId to autocomplete URL for finding users
	 * @param {Object} eventObject The html element that changed.
	 */
	$.pkp.controllers.grid.users.stageParticipant.form.AddParticipantFormHandler.prototype.addUserIdToAutocompleteUrl =
			function(eventObject) {

		// FIXME: Should this js handler know the _container part?
		// It is inside the FBV autocomplete field.
		// We could add a pkp_controller_ class to the container <div>
		var $autocompleteContainer = $('#userId_container');

		// Clear the selection of the inputs (both hidden and visible)
		$autocompleteContainer.find(':input').each(
				function(index) { $(this).val(''); }
		);

		var autocompleteHandler =
				$.pkp.classes.Handler.getHandler($autocompleteContainer);

		var oldUrl = autocompleteHandler.getAutocompleteUrl();
		// Match with &amp;userGroupId or without and append userGroupId
		var newUrl = oldUrl.replace(
				/(&userGroupId=\d+)?$/, '&userGroupId=' + eventObject.value);
		autocompleteHandler.setAutocompleteUrl(newUrl);
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
