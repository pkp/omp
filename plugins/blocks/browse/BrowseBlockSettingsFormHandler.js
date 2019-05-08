/**
 * @file plugins/blocks/browse/BrowseBlockSettingsFormHandler.js
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.blocks.browse
 * @class BrowseBlockSettingsFormHandler
 *
 * @brief Browse block settings page form handler.
 */
(function($) {

	/** @type {Object} */
	$.pkp.plugins.blocks.browse =
		$.pkp.plugins.blocks.browse || { };



	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.AjaxFormHandler
	 *
	 * @param {jQueryObject} $formElement A wrapped HTML element that
	 *  represents the approved proof form interface element.
	 * @param {Object} options Tabbed modal options.
	 */
	$.pkp.plugins.blocks.browse.BrowseBlockSettingsFormHandler =
			function($formElement, options) {
		this.parent($formElement, options);

	};
	$.pkp.classes.Helper.inherits(
			$.pkp.plugins.blocks.browse.BrowseBlockSettingsFormHandler,
			$.pkp.controllers.form.AjaxFormHandler
	);


	$.pkp.plugins.blocks.browse.BrowseBlockSettingsFormHandler.prototype.submitForm =
			function(validator, formElement) {
		this.parent('submitForm', validator, formElement);

		// Cause the sidebar to reload, reflecting any changes.
		$('body').trigger('updateSidebar');
	};
/** @param {jQuery} $ jQuery closure. */
}(jQuery));
