/**
 * @defgroup js_controllers_grid_files_copyedit_addCopyeditingUser_form
 */
// Create the namespace.
jQuery.pkp.controllers.grid.files = jQuery.pkp.controllers.grid.files ||
			{ copyedit: { form: { } } };


/**
 * @file js/controllers/grid/files/copyedit/AddCopyeditingUserFormHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantFormHandler
 * @ingroup js_controllers_grid_files_copyedit_addCopyeditingUser_form
 *
 * @brief Handle the "add copyediting user" form.
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
	$.pkp.controllers.grid.files.copyedit.form.AddCopyeditingUserFormHandler =
			function($form, options) {

		this.parent($form, options);

		$('#responseDueDate').datepicker({ dateFormat: 'mm-dd-yy', minDate: '0' });
		// Set response due date to one week in the future
		// FIXME: May want to make a press setting
		var currentTime = new Date();
		var month = currentTime.getMonth() + 1;
		var day = currentTime.getDate() + 7;
		var year = currentTime.getFullYear();
		$('#responseDueDate').datepicker('setDate', month + '-' + day + '-' + year);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.grid.files.copyedit.form.
					AddCopyeditingUserFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Private properties
	//


	//
	// Public methods
	//
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
