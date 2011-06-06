/**
 * @defgroup js_controllers_tab_settings_form
 */
// Create the namespace.
jQuery.pkp.controllers.tab =
			jQuery.pkp.controllers.tab ||
			{ settings: { form: { } } };


/**
 * @file js/controllers/tab/settings/form/FileViewFormHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileViewFormHandler
 * @ingroup js_controllers_tab_settings_form
 *
 * @brief This handles a form that needs to present information about
 * uploaded files, and refresh itself when a file is saved, but refreshing
 * only the uploaded file part. This is necessary when we don´t want to
 * fetch the entire form and unnecessarily fetch other widgets inside the
 * form too (listbuilders or grids).
 *
 * To start the refresh, this class binds the 'dataChanged' event to know
 * when the file is saved and the setting name of the file. So, this handler
 * assumes that your save file action will trigger a 'dataChanged' event,
 * and that this event will pass a parameter with the setting name of the file
 * that have been uploaded.
 *
 * This handler implement two ways to handle the fetched file HTML markup:
 * with and without a wrapper. Use a wrapper when more HTML elements
 * need to go inside the file HTML element (see the image upload example,
 * the alternate text input field). When these related HTML elements go
 * inside the file HTML element, we can make them disappear when the file
 * is deleted.
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
	$.pkp.controllers.tab.settings.form.FileViewFormHandler =
			function($form, options) {

		this.parent($form, options);

		this.fetchFileUrl_ = options.fetchFileUrl;

		this.bind('dataChanged', this.refreshForm_);
	};

	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.tab.settings.form.FileViewFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Private properties
	//
	/**
	 * The url to fetch a file.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.tab.settings.form.FileViewFormHandler.prototype.fetchFileUrl_ = null;


	//
	// Private helper methods
	//
	/**
	 * Refresh the form, fetching a file.
	 *
	 * @param {HTMLElement} sourceElement The element that
	 *  issued the event.
	 * @param {Event} event The triggering event.
	 * @param {string} settingName The setting name of the uploaded file.
	 */
	$.pkp.controllers.tab.settings.form.FileViewFormHandler.prototype.refreshForm_ =
			function(sourceElement, event, settingName) {

		$.get(this.fetchFileUrl_, {settingName: settingName},
				this.callbackWrapper(this.refreshResponseHandler_), 'json');

	};

	/**
	 * Show the file rendered data in the form.
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} jsonData A parsed JSON response object.
	 */
	$.pkp.controllers.tab.settings.form.FileViewFormHandler.prototype.refreshResponseHandler_ =
			function(ajaxContext, jsonData) {

		jsonData = this.handleJson(jsonData);
		if (jsonData.noData) {

			// The file setting data was deleted, we can remove
			// its markup from the form.
			this.hideFileData_(jsonData.noData);
		}
		else {

			// The server returned mark-up to replace
			// or insert the file data in form.
			this.showFileData_(jsonData.elementId, jsonData.content);
		}
	};

	/**
	 * Hide all the data in form related to the file.
	 *
	 * @param {string} settingName The file setting name.
	 */
	$.pkp.controllers.tab.settings.form.FileViewFormHandler.prototype.hideFileData_ =
			function(settingName) {

		$fileElement = this.getFileHtmlElement_(settingName);

		// Check if the element has a wrapper.
		$fileViewWrapper = this.getViewWrapper_($fileElement);
		if ($fileViewWrapper.attr("id") != null) {

			// Remove markup only from the wrapper.
			$fileViewWrapper.html('');

			// Set the file HTML element to invisible.
			$fileElement.addClass('pkp_form_hidden');

			// The file HTML element possibly has other inputs
			// inside of it, so we try to clean them.
			$fileElement.find('input').attr('value', '');
		} else {

			// The file HTML element has no other HTML elements.
			// Only remove its current markup.
			$fileElement.html('');
		}



	};

	/**
	 * Show the file data that have been uploaded in form.
	 *
	 * @param {string} settingName The file setting name.
	 * @param {string} fileMarkup The file HTML markup.
	 */
	$.pkp.controllers.tab.settings.form.FileViewFormHandler.prototype.showFileData_ =
			function(settingName, fileMarkup) {

		$fileElement = this.getFileHtmlElement_(settingName);

		// Check if the element has a wrapper.
		$fileViewWrapper = this.getViewWrapper_($fileElement);
		if ($fileViewWrapper.attr("id") != null) {
			$fileViewWrapper.html(fileMarkup);
			$fileElement.removeClass('pkp_form_hidden');
		} else {
			$fileElement.html(fileMarkup);
		}
	};

	/**
	 * Get the file HTML element that contains all the file data markup.
	 * We assume that the id of the file HTML element it is equal
	 * to the file setting name.
	 *
	 * @param {string} settingName The file setting name.
	 * @return {jQuery}
	 */
	$.pkp.controllers.tab.settings.form.FileViewFormHandler.prototype.getFileHtmlElement_ =
			function(settingName) {
		$form = this.getHtmlElement();
		$fileHtmlElement = $('#' + settingName, $form);

		return $fileHtmlElement;
	};

	/**
	 * Get the file HTML element that is a wrapper for the file element HTML markup.
	 *
	 * @param {jQuery} $fileHtmlElement The HTML file element.
	 * @return {jQuery}
	 */
	$.pkp.controllers.tab.settings.form.FileViewFormHandler.prototype.getViewWrapper_ =
			function($fileHtmlElement) {

		$fileViewWrapper = $('.file_view_wrapper', $fileHtmlElement);

		return $fileViewWrapper;
	};


	/** @param {jQuery} $ jQuery closure. */
}(jQuery));