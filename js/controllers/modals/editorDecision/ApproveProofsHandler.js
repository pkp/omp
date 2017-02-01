/**
 * @defgroup js_controllers_modal_editorDecision_form
 */
/**
 * @file js/controllers/modals/editorDecision/ApproveProofsHandler.js
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApproveProofsHandler
 * @ingroup js_controllers_modal_editorDecision
 *
 * @brief Handle approve proofs content.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQueryObject} $container the wrapped HTML element.
	 * @param {Object} options form options.
	 */
	$.pkp.controllers.modals.editorDecision.ApproveProofsHandler =
			function($container, options) {

		this.parent($container, options);

		this.bind('gridRefreshRequested', this.callbackWrapper(function() {
			this.trigger('dataChanged');
		}));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.modals.editorDecision.ApproveProofsHandler,
			$.pkp.classes.Handler);


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
