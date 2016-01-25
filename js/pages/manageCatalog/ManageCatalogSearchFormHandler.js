/**
 * @file js/pages/manageCatalog/ManageCatalogSearchFormHandler.js
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageCatalogSearchFormHandler
 * @ingroup js_pages_catalog
 *
 * @brief Handler for the search form in catalog administration.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.FormHandler
	 *
	 * @param {jQueryObject} $form the wrapped HTML form element.
	 * @param {Object} options form options.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogSearchFormHandler =
			function($form, options) {

		options.submitHandler = this.submitForm;
		this.parent($form, options);

		// Expose the searchCatalog event to the containing element.
		this.publishEvent('searchCatalog');
	};

	$.pkp.classes.Helper.inherits(
			$.pkp.pages.manageCatalog.ManageCatalogSearchFormHandler,
			$.pkp.controllers.form.FormHandler);


	//
	// Public methods
	//
	/**
	 * Internal callback called after form validation to handle form
	 * submission.
	 *
	 * @param {Object} validator The validator plug-in.
	 * @param {HTMLElement} formElement The wrapped HTML form.
	 */
	$.pkp.pages.manageCatalog.ManageCatalogSearchFormHandler.prototype.submitForm =
			function(validator, formElement) {

		var $form = this.getHtmlElement(),
				formAction = $form.attr('action'),
				searchText = /** @type {string} */ ($form.find(':input').val());

		// Trigger the searchCatalog event for the container
		// to deal with. Attach the URL to the search to be
		// performed.

		this.trigger('searchCatalog',
				[formAction.replace('SEARCH_TEXT_DUMMY',
				encodeURIComponent(searchText))]);
	};


}(jQuery));
