/**
 * @defgroup js_pages_workflow
 */
// Create the pages_workflow namespace.
$.pkp.pages.workflow = $.pkp.pages.workflow || {};

/**
 * @file js/pages/production/SubmissionHeaderHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHeaderHandler
 * @ingroup js_pages_production
 *
 * @brief Handler for the production stage.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $submissionHeader The HTML element encapsulating
	 *  the header div.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.workflow.SubmissionHeaderHandler =
			function($submissionHeader, options) {

		this.parent($submissionHeader, options);

		// FIXME: #6834 this CSS should not live here.
		var $link = $('#stageParticipantToggle');
		$link.css('float', 'right');

		var $stageParticipantsGridContainer = $submissionHeader.find('.pkp_stage_participant_popover');
		$stageParticipantsGridContainer.width(300);
		$stageParticipantsGridContainer.css('position', 'absolute');
		$stageParticipantsGridContainer.css('z-index', 10);

		$('#stageParticipantToggle').hover(function() {
			var $popover = $(this).find('.pkp_stage_participant_popover');
			$popover.show();
			$popover.position({
				my: "right top",
				at: "right top",
				of: $(this),
				offset: "10 0",
				collision: "none"
			});
		}, function() {
			var $popover = $(this).find('.pkp_stage_participant_popover');
			$popover.hide();
		});
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.workflow.SubmissionHeaderHandler,
			$.pkp.classes.Handler);


	//
	// Private static properties
	//


	//
	// Private properties
	//


	//
	// Private methods
	//


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
