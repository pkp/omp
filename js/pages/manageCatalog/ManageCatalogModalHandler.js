/**
 * @defgroup js_pages_manageCatalog
 */
/**
 * @file js/pages/manageCatalog/ManageCatalogModalHandler.js
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageCatalogModalHandler
 * @ingroup js_pages_manageCatalog
 *
 * @brief Handler for dealing with the modal catagory and series
 * management modals.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.FormHandler
	 *
	 * @param {jQueryObject} $handledElement The clickable element
	 *  the modal will be attached to.
	 * @param {Object} options non-default Dialog options
	 *  to be passed into the dialog widget.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogModalHandler =
			function($handledElement, options) {
		this.parent($handledElement, options);

		// Activate the cancel button (if present).
		$('#cancelFormButton', $handledElement).click(
				this.callbackWrapper(this.cancelForm));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.manageCatalog.ManageCatalogModalHandler,
			$.pkp.controllers.form.FormHandler);


	//
	// Public methods
	//
	/**
	 * Internal callback called to cancel the form.
	 *
	 * @param {HTMLElement} cancelButton The cancel button.
	 * @param {Event} event The event that triggered the
	 *  cancel button.
	 * @return {boolean} false.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogModalHandler.prototype.cancelForm =
			function(cancelButton, event) {

		// Trigger the event which will cause the DropdownHandler to
		// fetch its items again.
		this.trigger('containerReloadRequested');
		this.trigger('formCanceled');
		return false;
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
