/**
 * @defgroup js_controllers_tab_settings_homepage_form
 */
// Create the namespace.
jQuery.pkp.controllers.tab.settings.homepage =
			jQuery.pkp.controllers.tab.settings.homepage || {form: { } };


/**
 * @file js/controllers/tab/settings/homepage/form/HomepageFormHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HomepageFormHandler
 * @ingroup js_controllers_tab_settings_homepage_form
 *
 * @brief Handle the press homepage settings form.
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
	$.pkp.controllers.tab.settings.homepage.form.HomepageFormHandler =
			function($form, options) {

		this.parent($form, options);

		// Attach form elements events.
		$('#enableAnnouncementsHomepage', $form).click(
				this.callbackWrapper(this.toggleEnableAnnouncementsHomepage));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.tab.settings.homepage.form.HomepageFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Public methods.
	//
	/**
	 * Event handler that is called when the suggest username button is clicked.
	 * @param {HTMLElement} element The checkbox input element.
	 */
	$.pkp.controllers.tab.settings.homepage.form.HomepageFormHandler.prototype.
		toggleEnableAnnouncementsHomepage = function(element) {
		$numAnnouncementsHomepage = $('#numAnnouncementsHomepage', this.getHtmlElement());
		$numAnnouncementsHomepage.attr("disabled", !$numAnnouncementsHomepage.attr("disabled"));
	};


	/** @param {jQuery} $ jQuery closure. */
}(jQuery));