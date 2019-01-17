/**
 * @defgroup js_controllers_catalog_form
 */
/**
 * @file js/controllers/catalog/form/CatalogMetadataFormHandler.js
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogMetadataFormHandler
 * @ingroup js_controllers_catalog_form
 *
 * @brief Catalog Metadata form handler.
 */
(function($) {

	/** @type {Object} */
	$.pkp.controllers.catalog =
			$.pkp.controllers.catalog || { form: { } };



	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.FileUploadFormHandler
	 *
	 * @param {jQueryObject} $form A wrapped HTML element that
	 *  represents the tabbed interface element.
	 * @param {Object} options Tabbed modal options.
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler =
			function($form, options) {
		this.parent($form, options);

		if (options.workTypeEditedVolume) {
			this.workTypeEditedVolume_ = options.workTypeEditedVolume;
		}
		if (options.workTypeAuthoredWork) {
			this.workTypeAuthoredWork_ = options.workTypeAuthoredWork;
		}
		$('#workType', $form).change(
				this.callbackWrapper(this.toggleVolumeEditors));

		$('#audienceRangeExact', $form).change(
				this.callbackWrapper(this.ensureValidAudienceRanges_));

		// Permissions: If any of the permissions fields are filled, check the box
		if (options.arePermissionsAttached) {
			$form.find('#attachPermissions').prop('checked', true);
		}

		this.coverImageMessage_ = options.coverImageMessage;

		$('input[id^="copyrightHolder-"]', $form)
				.keyup(this.callbackWrapper(this.checkAttachMetadata));
		$('input[id^="copyrightYear-"]', $form)
				.keyup(this.callbackWrapper(this.checkAttachMetadata));
		$('input[id^="licenseURL-"]', $form)
				.keyup(this.callbackWrapper(this.checkAttachMetadata));
		$('input[id="confirm"]', $form).change(function() {
			if (this.checked) {
				$('#attachPermissions').prop('checked', true);
			}
		});

		this.bind('fileUploaded', this.callbackWrapper(this.handleCoverImageUpload));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.catalog.form.CatalogMetadataFormHandler,
			$.pkp.controllers.form.FileUploadFormHandler
	);


	//
	// Private properties
	//
	/**
	 * An array to store the temporary values of the audienceTo and From
	 * select items, in case the user decides to reuse them again.
	 * @private
	 * @type {Array?}
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler.prototype.
			audienceValues_ = null;


	/**
	 * A message to display in place of the cover image when it has been
	 * replaced.
	 * @private
	 * @type {String?}
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler.prototype.
			coverImageMessage_ = null;


	/**
	 * Value matching the edited volume worktype
	 * @private
	 * @type {?number}
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler.prototype.
			workTypeEditedVolume_ = null;


	/**
	 * Value matching the single author worktype
	 * @private
	 * @type {?number}
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler.prototype.
			workTypeAuthoredWork_ = null;


	//
	// Private methods
	//
	/**
	 * Respond to someone toggling the audience range select item for an 'exact'
	 * range.  Disable the other two types of audience ranges and set their
	 * values to empty.
	 *
	 * @param {HTMLElement} sourceElement The element that
	 *  issued the event.
	 * @param {Event} event The triggering event.
	 * @private
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler.prototype.
			ensureValidAudienceRanges_ = function(sourceElement, event) {

		var $form = this.getHtmlElement();
		if ($(sourceElement).val() !== '') {
			this.audienceValues_ = [$form.find('#audienceRangeFrom').val(),
			                        $form.find('#audienceRangeTo').val()];
			$form.find('#audienceRangeFrom, #audienceRangeTo').val('').
					attr('disabled', 'disabled');
		} else {
			$form.find('#audienceRangeFrom, #audienceRangeTo').
					attr('disabled', '');
			$form.find('#audienceRangeFrom').val(this.audienceValues_[0]);
			$form.find('#audienceRangeTo').val(this.audienceValues_[1]);
		}
	};


	/**
	 * Callback for when the selected issue changes.
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler.prototype.
			checkAttachMetadata = function() {

		var $element = this.getHtmlElement();
		$element.find('#attachPermissions').prop('checked', true);
	};


	/**
	 * Callback for when a new cover image is uploaded
	 *
	 * @param {Object} caller The original context in which the callback was called.
	 * @param {Event} event The event triggered on caller.
	 * @see $.pkp.controllers.form.FileUploadFormHandler.prototype.handleUploadResponse
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler.prototype.
			handleCoverImageUpload = function(caller, event) {

		var $coverImage = this.getHtmlElement().find('.currentCoverImage')
				.addClass('changed');

		$coverImage.find('img').remove();
		$coverImage.find('.coverImageMessage').html(
				/** @type {string} */ (this.coverImageMessage_));
	};


	/**
	 * Callback for showing or hiding the volume editor control when the workflow
	 * type has changed
	 *
	 * @param {HTMLElement} sourceElement The element that
	 *  issued the event.
	 * @param {Event} event The triggering event.
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler.prototype.
			toggleVolumeEditors = function(sourceElement, event) {

		var $workType = $(sourceElement),
				$volumeEditors = $('#volumeEditors', this.getHtmlElement());

		if ($workType.val() == this.workTypeEditedVolume_) {
			$volumeEditors.fadeIn();
		} else {
			$volumeEditors.fadeOut();
		}
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
