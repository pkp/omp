/**
 * @defgroup js_controllers_dashboard_form
 */
// Create the namespace.
jQuery.pkp.controllers.dashboard =
			jQuery.pkp.controllers.dashboard || {form: { } };


/**
 * @file js/controllers/dashboard/form/DashboardTaskFormHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DashboardTaskFormHandler
 * @ingroup js_controllers_dashboard_form
 *
 * @brief Handle the styling and actions on the 'start new submission' form
 *  on the Task tab in the dashboard.
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
	$.pkp.controllers.dashboard.form.DashboardTaskFormHandler =
			function($form, options) {

		this.parent($form, options);
		this.singlePressSubmissionUrl_ = options.singlePressSubmissionUrl;

		$('#singlePress', $form).click(
				this.callbackWrapper(this.startSinglePressSubmission_));

		$('#multiplePress', $form).change(
				this.callbackWrapper(this.startMultiplePressSubmission_));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.dashboard.form.DashboardTaskFormHandler,
			$.pkp.controllers.form.FormHandler);


	//
	// Private properties
	//
	/**
	 * The URL to be called to fetch a spotlight item via autocomplete.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.dashboard.form.DashboardTaskFormHandler.
			prototype.singlePressSubmissionUrl_ = null;


	//
	// Private Methods
	//
	/**
	 * Redirect to the wizard for single press submissions
	 * @private
	 */
	$.pkp.controllers.dashboard.form.DashboardTaskFormHandler.
			prototype.startSinglePressSubmission_ = function() {

		window.location.href = this.singlePressSubmissionUrl_;
	};


	/**
	 * Redirect to the wizard for multiple press submissions
	 * @private
	 */
	$.pkp.controllers.dashboard.form.DashboardTaskFormHandler.
			prototype.startMultiplePressSubmission_ = function() {

		var $form = this.getHtmlElement();
		var url = $form.find('#multiplePress').val();
		if (url !== 0) { // not the default
			window.location.href = url;
		}
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
