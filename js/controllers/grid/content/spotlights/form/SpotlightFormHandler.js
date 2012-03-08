/**
 * @defgroup js_controllers_grid_content_spotlights_form
 */
// Create the namespace.
jQuery.pkp.controllers.grid.content =
		jQuery.pkp.controllers.grid.content || { spotlights: { form: { } } };

/**
 * @file js/controllers/grid/content/spotlights/form/SpotlightFormHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightFormHandler.js
 * @ingroup js_controllers_grid_content_spotlights_form
 *
 * @brief Handle the spotlight form.
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
	$.pkp.controllers.grid.content.spotlights.form.SpotlightFormHandler =
			function($form, options) {

		this.parent($form, options);

		this.autocompleteUrl_ = options.autocompleteUrl;

		$('#type', $form).change(
				this.callbackWrapper(this.addTypeToAutocompleteUrl));

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
	 * @type {string}
	 */
	$.pkp.controllers.grid.content.spotlights.form.SpotlightFormHandler.
			prototype.autocompleteUrl_ = null;


	//
	// Public methods
	//
	/**
	 * Method to add the type to autocomplete URL for finding spotlight items
	 * @param {Object} eventObject The html element that changed.
	 */
	$.pkp.controllers.grid.content.spotlights.form.SpotlightFormHandler.prototype.addTypeToAutocompleteUrl =
			function(eventObject) {

		var $form = this.getHtmlElement();
		var $autocompleteContainer = $form.find('#assocId_container');

		// Clear the selection of the inputs (both hidden and visible)
		$autocompleteContainer.find(':input').each(
				function(index) { $(this).val(''); }
		);

		var autocompleteHandler =
				$.pkp.classes.Handler.getHandler($autocompleteContainer);

		var oldUrl = this.autocompleteUrl_;

		// Match with &amp;type or without and append type
		var newUrl = null;
		if (oldUrl.match(/&type=\d+/)) {
			newUrl = oldUrl.replace(/(&type=\d+)/, '&type=' + eventObject.value);
		} else {
			newUrl = oldUrl + '&type=' + eventObject.value;
		}
		autocompleteHandler.setAutocompleteUrl(newUrl);
	};


	/**
	 * Method to add the contents of the Name field to the end of the autocomplete URL
	 * @param {Object} eventObject The html element that changed.
	 */
	$.pkp.controllers.grid.content.spotlights.form.SpotlightFormHandler.prototype.addNameToAutocompleteUrl =
			function(eventObject) {

		var $form = this.getHtmlElement();
		var $autocompleteContainer = $form.find('#assocId_container');

		var autocompleteHandler =
				$.pkp.classes.Handler.getHandler($autocompleteContainer);

		var oldUrl = this.autocompleteUrl_;

		// Remove the old Name from the URL
		var newUrl = oldUrl.replace(/(&name=[^&]*)/, '');
		newUrl += '&name=' + encodeURIComponent(eventObject.value);
		autocompleteHandler.setAutocompleteUrl(newUrl);
	};

/** @param {jQuery} $ jQuery closure. */
})(jQuery);
