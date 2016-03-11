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
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.manageCatalog.ManageCatalogModalHandler,
			$.pkp.controllers.form.FormHandler);


	//
	// Public methods
	//
	/**
	 * Bind a handler for container (e.g. modal) close events to permit forms
	 * to clean up.blur handler on tinyMCE instances inside this form
	 * @param {HTMLElement} input The input element that triggered the
	 * event.
	 * @param {Event} event The initialized event.
	 * @return {boolean} Event handling success.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogModalHandler
			.prototype.containerCloseHandler = function(input, event) {
		this.parent('containerCloseHandler', input, event);

		// Make sure any containing tab reloads in Catalog Management
		// (e.g. if a new category has been created that needs listing)
		this.trigger('containerReloadRequested');

		return true;
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
