/**
 * @file js/pages/authorDashboard/AuthorDashboardHandler.js
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorDashboardHandler
 * @ingroup js_pages_authorDashboard
 *
 * @brief Handler for the author dashboard.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.pages.authorDashboard.PKPAuthorDashboardHandler
	 *
	 * @param {jQueryObject} $dashboard The HTML element encapsulating
	 *  the author dashboard page.
	 * @param {{currentStage: number}} options Handler options.
	 *  currentStage: the current workflow stage, one of the
	 *      WORKFLOW_ID_* constants.
	 */
	$.pkp.pages.authorDashboard.AuthorDashboardHandler =
			function($dashboard, options) {

		this.parent($dashboard, options);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.authorDashboard.AuthorDashboardHandler,
			$.pkp.pages.authorDashboard.PKPAuthorDashboardHandler);


	//
	// Public static properties
	//
	/**
	 * An object that assigns stage identifiers to the
	 * corresponding CSS section selectors.
	 * @type {Object.<number,string>}
	 * @const
	 * FIXME: Is there a less verbose way to define this object?
	 */
	$.pkp.pages.authorDashboard.AuthorDashboardHandler.CSS_SELECTORS =
			$.pkp.pages.authorDashboard.PKPAuthorDashboardHandler.CSS_SELECTORS;
	$.pkp.pages.authorDashboard.AuthorDashboardHandler.CSS_SELECTORS[
			$.pkp.cons.WORKFLOW_STAGE_ID_INTERNAL_REVIEW] = '#internalReview';


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
