/**
 * @defgroup js_controllers_grid_files_signoff_form
 */
// Create the namespace.
jQuery.pkp.controllers.grid.files = jQuery.pkp.controllers.grid.files ||
			{ signoff: { form: { } } };


/**
 * @file js/controllers/grid/files/signoff/AddAuditorFormHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddAuditorFormHandler
 * @ingroup js_controllers_grid_files_signoff_form
 *
 * @brief Handle the "add auditor" form.
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
	$.pkp.controllers.grid.files.signoff.form.AddAuditorFormHandler =
			function($form, options) {

		this.parent($form, options);

		$('input[id^="responseDueDate"]').datepicker({ dateFormat: 'mm-dd-yy', minDate: '0' });
		// Set response due date to one week in the future
		// FIXME: May want to make a press setting
		var currentTime = new Date();
		var month = currentTime.getMonth() + 1;
		var day = currentTime.getDate() + 7;
		var year = currentTime.getFullYear();
		$('#responseDueDate').datepicker('setDate', month + '-' + day + '-' + year);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.grid.files.signoff.form.
					AddAuditorFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
