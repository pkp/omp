/**
 * @defgroup js_controllers_grid_users_user_form
 */
// Create the namespace.
jQuery.pkp.controllers.grid.users.user =
			jQuery.pkp.controllers.grid.users.user || { form: { } };

/**
 * @file js/controllers/grid/users/user/form/UserFormHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserFormHandler
 * @ingroup js_controllers_grid_users_user_form
 *
 * @brief Handle the user settings form.
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
	$.pkp.controllers.grid.users.user.form.UserFormHandler =
			function($form, options) {

		this.parent($form, options);

		// Set data to private variables.
		this.fetchUsernameSuggestionUrl_ = options.fetchUsernameSuggestionUrl;
		this.usernameSuggestionTextAlert_ = options.usernameSuggestionTextAlert;

		// Attach form elements events.
		$('#generatePassword', $form).click(
				this.callbackWrapper(this.setGenerateRandom));
		$('#suggestUsernameButton', $form).
				click(this.callbackWrapper(this.generateUsername));

		// Execute code for interests text tagit.
		var existingInterests = options.existingInterests;
		var currentInterests = options.currentInterests;
		this.handleInterestsKeywords_(existingInterests, currentInterests);

		// Check the generate password check box.
		if ($('#generatePassword', $form).attr('checked')) {
			this.setGenerateRandom('#generatePassword');
		}

	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.grid.users.user.form.UserFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Private properties
	//
	/**
	 * The URL to be called to fetch a username suggestion.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.grid.users.user.form.UserFormHandler.
			prototype.fetchUsernameSuggestionUrl_ = '';


	/**
	 * The message that will be displayed if users click on suggest
	 * username button with no data in lastname.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.grid.users.user.form.UserFormHandler.
			prototype.usernameSuggestionTextAlert_ = '';


	//
	// Public methods.
	//
	/**
	 * @see AjaxFormHandler::submitForm
	 * @param {Object} validator The validator plug-in.
	 * @param {HTMLElement} formElement The wrapped HTML form.
	 */
	$.pkp.controllers.grid.users.user.form.UserFormHandler.prototype.
			submitForm = function(validator, formElement) {

		var $form = this.getHtmlElement();
		$('#password', $form).attr('disabled', 0);
		$('#password2', $form).attr('disabled', 0);
		var result = this.parent('submitForm', validator, formElement);
	};


	/**
	 * Event handler that is called when generate password checkbox is
	 * clicked.
	 *
	 * @param {String} checkbox
	 */
	$.pkp.controllers.grid.users.user.form.UserFormHandler.prototype.
			setGenerateRandom = function(checkbox) {

		// JQuerify the element
		var $checkbox = $(checkbox);
		var $form = this.getHtmlElement();
		var passwordValue = '';
		var activeAndCheck = 0;
		if ($checkbox.attr('checked')) {
			passwordValue = '********';
			activeAndCheck = 1;
		}
		$('#password, #password2', $form).
				attr('disabled', activeAndCheck).val(passwordValue);
		$('#sendNotify', $form).attr('disabled', activeAndCheck).
				attr('checked', activeAndCheck);
	};

	/**
	 * Event handler that is called when the suggest username button is clicked.
	 */
	$.pkp.controllers.grid.users.user.form.UserFormHandler.prototype.generateUsername =
			function() {

		var $form = this.getHtmlElement();

		if ($('#lastName', $form).val() === "") {
			alert(this.usernameSuggestionTextAlert_);
			return;
		}
		var firstName = $('#firstName', $form).val();
		var lastName = $('#lastName', $form).val();
		var fetchUrl = this.fetchUsernameSuggestionUrl_.replace('firstNameDummy', firstName).replace('lastNameDummy', lastName);
		$.get(fetchUrl, this.callbackWrapper(this.setUsername), 'json');
	};

	/**
	 * Check JSON message and set it to username, back on form.
	 */
	$.pkp.controllers.grid.users.user.form.UserFormHandler.prototype.setUsername =
			function(formElement, jsonData) {

		jsonData = this.handleJson(jsonData);

		if(jsonData === false) {
			throw Error('JSON response must be set to true!');
		}

		var $form = this.getHtmlElement();
		$('#username', $form).val(jsonData.content);
	};


	//
	// Private methods.
	//
	/**
	 * Handle interests keywords.
	 *
	 * @param {Array} existingInterests
	 * @param {Array} currentInterests
	 */
	$.pkp.controllers.grid.users.user.form.UserFormHandler.prototype.handleInterestsKeywords_ =
			function(existingInterests, currentInterests) {

		var $form = this.getHtmlElement();
		$("#interestsTextOnly", $form).hide();
		$("#interests", $form).tagit({
			availableTags: existingInterests,
			currentTags: currentInterests
		});
	};


	/** @param {jQuery} $ jQuery closure. */
})(jQuery);
