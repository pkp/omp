/**
 * @file js/controllers/modals/catalogEntry/form/AddContributorFormHandler.js
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddContributorFormHandler
 * @ingroup js_controllers_modal_catalogEntry_form
 *
 * @brief Handle form for adding contributors to a monograph
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.AjaxFormHandler
	 *
	 * @param {jQueryObject} $form the wrapped HTML form element.
	 * @param {Object} options form options.
	 */
	$.pkp.controllers.modals.catalogEntry.form.AddContributorFormHandler =
			function($form, options) {

		this.parent($form, options);

		this.volumeEditorGroupIds_ = options.volumeEditorGroupIds;

		var $userGroups = $('[name="userGroupId"]', $form);
		$userGroups.change(this.callbackWrapper(this.updateIsVolumeEditor));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.modals.catalogEntry.form.AddContributorFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	/**
	 * User groups which should select the option to display the contributor as a
	 * volume editor.
	 *
	 * @type {array}
	 */
	$.pkp.controllers.form.AjaxFormHandler.prototype.
			volumeEditorGroupIds_ = null;


	/**
	 * Update the volume editor display option when the user group is changed
	 *
	 * @param {string} $userGroup The user group selection
	 */
	$.pkp.controllers.form.AjaxFormHandler.prototype.
			updateIsVolumeEditor = function(userGroup) {

		var userGroupId = $(userGroup).val();
		if (this.volumeEditorGroupIds_.indexOf(userGroupId) > -1) {
			$('#isVolumeEditor', this.getHtmlElement()).prop('checked', true);
		} else {
			$('#isVolumeEditor', this.getHtmlElement()).prop('checked', false);
		}
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
