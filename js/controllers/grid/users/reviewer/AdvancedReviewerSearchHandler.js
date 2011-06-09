/**
 * @file js/controllers/AdvancedReviewerSearchHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdvancedReviewerSearchHandler
 * @ingroup js_controllers
 *
 * @brief Handle the advanced reviewer search tab in the add reviewer modal.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $container the wrapped page element.
	 * @param {Object} options handler options.
	 */
	$.pkp.controllers.AdvancedReviewerSearchHandler =
			function($container, options) {
		this.parent($container, options);

		$container.find('.button').button();

		$('#selectReviewerButton').click(
				this.callbackWrapper(this.reviewerSelected));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.AdvancedReviewerSearchHandler, $.pkp.classes.Handler);


	//
	// Public methods
	//
	/**
	 * Callback that is triggered when a reviewer is selected.
	 *
	 * @param {HTMLElement} button The button element clicked.
	 */
	$.pkp.controllers.AdvancedReviewerSearchHandler.prototype.
			reviewerSelected = function(button) {

		// Get the selected reviewer's ID
		var $selectedInput = this.getHtmlElement().
				find('#reviewerSelectGridContainer').find('input:checked');
		var reviewerId = $selectedInput.val();

		if (reviewerId) {
			var reviewerName = $selectedInput.parent().next().children('span').html();

			// Update the hidden review id input
			$('#reviewerId').val(reviewerId);

			// Update the selected reviewer name container
			$('#selectedReviewerName').hide().html(reviewerName).show(300);
		}
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
