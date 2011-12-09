/**
 * @file js/pages/catalog/CatalogSearchFormHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogSearchFormHandler
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
	 * @param {jQuery} $form the wrapped HTML form element.
	 * @param {Object} options form options.
	 */
	$.pkp.pages.catalog.CatalogSearchFormHandler =
			function($form, options) {

		options.submitHandler = this.submitForm;
		this.parent($form, options);

		// Expose the searchCatalog event to the containing element.
		this.publishEvent('searchCatalog');
	};

	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.CatalogSearchFormHandler,
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
	$.pkp.pages.catalog.CatalogSearchFormHandler.prototype.submitForm =
			function(validator, formElement) {

		var $form = this.getHtmlElement();
		var formAction = $form.attr('action');
		var searchText = $form.find(':input').val();

		// Trigger the searchCatalog event for the container
		// to deal with. Attach the URL to the search to be
		// performed.
		this.trigger('searchCatalog',
				formAction.replace('SEARCH_TEXT_DUMMY', encodeURIComponent(searchText)));
	};

})(jQuery);
