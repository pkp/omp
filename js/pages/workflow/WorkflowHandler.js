/**
 * @defgroup js_pages_workflow
 */
// Create the pages_workflow namespace.
$.pkp.pages.workflow = $.pkp.pages.workflow || {};

/**
 * @file js/pages/workflow/WorkflowHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowHandler
 * @ingroup js_pages_workflow
 *
 * @brief Base handler for the workflow pages.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $workflowElement The HTML element encapsulating
	 *  the production page.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.workflow.WorkflowHandler =
			function($workflowElement, options) {

		this.parent($workflowElement, options);

		this.bind('stageParticipantsChanged', this.handleStageParticipantsChanged_);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.workflow.WorkflowHandler,
			$.pkp.classes.Handler);


	//
	// Private functions
	//
	/**
	 * Potentially refresh workflow content on participant change.
	 *
	 * @param {JQuery} callingElement The calling element.
	 *  that triggered the event.
	 * @param {Event} event The event.
	 * @private
	 */
	$.pkp.pages.workflow.WorkflowHandler.prototype.handleStageParticipantsChanged_ =
			function(callingElement, event) {

		// Find and reload editor decision action divs.
		this.getHtmlElement().find('.editorDecisionActions').each(function() {
			var handler = $.pkp.classes.Handler.getHandler($(this));
			handler.reload();
		});
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
