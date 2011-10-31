/**
 * @defgroup js_pages_workflow
 */
// Create the pages_workflow namespace.
$.pkp.pages.workflow = $.pkp.pages.workflow || {};

/**
 * @file js/pages/workflow/SubmissionHeaderHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHeaderHandler
 * @ingroup js_pages_workflow
 *
 * @brief Handler for the workflow header.
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

		// show and hide on click of link
		$('#participantToggle').click(function() {
			$('.participant_popover').toggle();
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
