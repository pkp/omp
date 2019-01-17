/**
 * @defgroup js_controllers_grid_content_spotlights_form
 */
/**
 * @file js/controllers/grid/content/spotlights/form/SpotlightFormHandler.js
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightFormHandler.js
 * @ingroup js_controllers_grid_content_spotlights_form
 *
 * @brief Handle the spotlight form.
 */
(function($) {

	/** @type {Object} */
	$.pkp.controllers.grid.content =
			$.pkp.controllers.grid.content ||
			{ spotlights: { form: { } } };



	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.AjaxFormHandler
	 *
	 * @param {jQueryObject} $form the wrapped HTML form element.
	 * @param {Object} options form options.
	 */
	$.pkp.controllers.grid.content.spotlights.form.SpotlightFormHandler =
			function($form, options) {

		this.parent($form, options);

		this.autocompleteUrl_ = options.autocompleteUrl;

		$('[id^="assocId_input"]', $form).keyup(
				this.callbackWrapper(this.addNameToAutocompleteUrl));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.grid.content.spotlights.form.SpotlightFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Private properties
	//
	/**
	 * The URL to be called to fetch a spotlight item via autocomplete.
	 * @private
	 * @type {string?}
	 */
	$.pkp.controllers.grid.content.spotlights.form.SpotlightFormHandler.
			prototype.autocompleteUrl_ = null;


	/**
	 * Method to add the contents of the Name field to the end
	 * of the autocomplete URL
	 * @param {Object} eventObject The html element that changed.
	 */
	$.pkp.controllers.grid.content.spotlights.form.SpotlightFormHandler.
			prototype.addNameToAutocompleteUrl = function(eventObject) {

		var $form = this.getHtmlElement(),
				$autocompleteContainer = $form.find('#assocId_container'),
				autocompleteHandler =
						$.pkp.classes.Handler.getHandler($autocompleteContainer),
				oldUrl = this.autocompleteUrl_,
				// Remove the old Name from the URL
				regExp = '/(&name=[^&]*)/',
				newUrl = oldUrl.replace(regExp, '');

		newUrl += '&name=' + encodeURIComponent(eventObject.value);
		autocompleteHandler.setAutocompleteUrl(newUrl);
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
