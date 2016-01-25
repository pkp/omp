/**
 * @file js/pages/manageCatalog/MonographManagementListHandler.js
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographManagementListHandler
 * @ingroup js_pages_manageCatalog
 *
 * @brief Handler for monograph list.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.monographList.MonographListHandler
	 *
	 * @param {jQueryObject} $monographsContainer The HTML element encapsulating
	 *  the monograph list div.
	 * @param {Object} options Handler options.
	 */
	$.pkp.pages.manageCatalog.MonographManagementListHandler =
			function($monographsContainer, options) {

		this.parent($monographsContainer, options);

		// Attach the fature button handler, if button exists
		$monographsContainer.find('.feature').click(
				this.callbackWrapper(this.featureButtonHandler_));

		// React to "monograph list changed" events.
		this.bind('monographListChanged',
				this.monographListChangedHandler_);
		this.bind('monographSequencesChanged',
				this.monographSequencesChangedHandler_);

		// Start in grid view
		this.useGridView();
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.manageCatalog.MonographManagementListHandler,
			$.pkp.controllers.monographList.MonographListHandler);


	//
	// Private Properties
	//
	/**
	 * Whether or not we're currently in Feature mode
	 * @private
	 * @type {boolean}
	 */
	$.pkp.pages.manageCatalog.MonographManagementListHandler.
			prototype.inFeatureMode_ = false;


	/**
	 * Whether or not we're currently in Grid mode
	 * @private
	 * @type {boolean?}
	 */
	$.pkp.pages.manageCatalog.MonographManagementListHandler.
			prototype.inGridMode_ = null;


	//
	// Public Methods
	//
	/**
	 * Switch to Grid View mode.
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.manageCatalog.MonographManagementListHandler.
			prototype.useGridView = function() {

		var $htmlElement = $(this.getHtmlElement()),
				$actionsContainer;

		$htmlElement.find('.pkp_manageCatalog_monographList')
			.removeClass('list_view')
			.addClass('grid_view');

		this.resetElementHeights_();
		// Control enabled/disabled state of buttons
		$actionsContainer = $htmlElement.find('.submission_actions');
		$actionsContainer.find('.grid_view').addClass('ui-state-active');
		$actionsContainer.find('.list_view').removeClass('ui-state-active');

		this.inGridMode_ = true;

		// In case called as event handler, stop further processing
		return false;
	};


	//
	// Extendeded protected methods from MonographListHandler
	//
	/**
	 * @inheritDoc
	 */
	$.pkp.pages.manageCatalog.MonographManagementListHandler.
			prototype.getMonographs = function() {
		return this.getHtmlElement().find('.pkp_manageCatalog_monograph');
	};


	//
	// Private Methods
	//
	/**
	 * Callback that will be activated when "feature" is clicked
	 *
	 * @private
	 *
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.manageCatalog.MonographManagementListHandler.
			prototype.featureButtonHandler_ = function() {

		// Toggle the "feature" flag.
		this.inFeatureMode_ = !this.inFeatureMode_;

		var $htmlElement = $(this.getHtmlElement()),
				// Find the button elements
				$actionsContainer = $htmlElement.find('.submission_actions'),
				$featureButton = $actionsContainer.find('.feature'),
				// Find the monograph list
				$monographList = $htmlElement
						.find('ul.pkp_manageCatalog_monographList'),
				// Find the feature links
				$featureLinks = $monographList
						.find('.pkp_manageCatalog_featureTools');

		if (this.inFeatureMode_) {
			// We've just entered "Feature" mode.
			$featureButton.css('font-weight', 'bold');
			$featureLinks.removeClass('pkp_helpers_invisible');
		} else {
			// We've just left "Feature" mode.
			$featureButton.css('font-weight', 'normal');
			$featureLinks.addClass('pkp_helpers_invisible');
		}
		$monographList.children().trigger('changeDragMode', [this.inFeatureMode_]);
		// Update the enabled/disabled state of the sortable list
		this.trigger('monographListChanged');

		// Stop further event processing
		return false;
	};


	/**
	 * Handle the "monograph list changed" event to reset the sortable
	 * JQueryUI initialization.
	 *
	 * @private
	 *
	 * @param {Object} callingHandler The handler
	 *  that triggered the event.
	 * @param {Event} event The event.
	 * @return {boolean} The event handling chain status.
	 */
	$.pkp.pages.manageCatalog.MonographManagementListHandler.
			prototype.monographListChangedHandler_ =
			function(callingHandler, event) {

		var $listContainer = this.getHtmlElement()
				.find('ul.pkp_manageCatalog_monographList');

		// In case the list has changed sort order, re-sort it.
		$listContainer.children('li').sortElements(function(aNode, bNode) {
			var a = $.pkp.classes.Handler.getHandler($(aNode)),
					b = $.pkp.classes.Handler.getHandler($(bNode));

			// One is featured and the other is not
			if (a.getFeatured() && !b.getFeatured()) {
				return -1;
			}
			if (b.getFeatured() && !a.getFeatured()) {
				return 1;
			}

			// Both are featured: use sequence.
			if (a.getFeatured() && b.getFeatured()) {
				return a.getSeq() - b.getSeq();
			}

			// Neither are featured: use publication date.
			return b.getDatePublished() - a.getDatePublished();
		});

		// Initialize sortable, but disabled unless "feature" selected.
		this.getHtmlElement().sortable('destroy');
		this.getHtmlElement().sortable({
			disabled: !this.inFeatureMode_,
			items: 'li.pkp_manageCatalog_monograph:not(.not_sortable)',
			update: this.callbackWrapper(this.sortUpdateHandler_),
			start: function(e, ui) {
				$(ui.placeholder).slideUp();
			},
			change: function(e, ui) {
				$(ui.placeholder).hide().slideDown();
			}
		});

		this.resetElementHeights_();

		// No further processing
		return false;
	};


	/**
	 * Handle the "monograph sequences changed" event to record sequences
	 *
	 * @private
	 *
	 * @param {jQueryObject} callingHandler The handler
	 *  that triggered the event.
	 * @param {Event} event The event.
	 * @param {Array} newSequences The new sequences to store.
	 * @return {boolean} The event handling chain status.
	 */
	$.pkp.pages.manageCatalog.MonographManagementListHandler.
			prototype.monographSequencesChangedHandler_ =
			function(callingHandler, event, newSequences) {

		var $listContainer = this.getHtmlElement()
				.find('ul.pkp_manageCatalog_monographList');

		// Store the provided sequences in each entry
		$listContainer.children('li').each(function(index, node) {
			var handler = $.pkp.classes.Handler.getHandler($(node)),
					newSequence = newSequences[handler.getId()];
			if (newSequence) {
				handler.trigger('setSequence', [newSequence, false]);
			}
		});

		// Now trigger a re-ordering of displayed elements.
		this.trigger('monographListChanged');

		return false;
	};


	/**
	 * Handle DOM change events when sortables are rearranged.
	 *
	 * @private
	 *
	 * @param {jQueryObject} callingHandler The handler
	 *  that triggered the event.
	 * @param {Event} event The event.
	 * @param {Object} ui The UI element that has changed.
	 * @return {boolean} The event handling chain status.
	 */
	$.pkp.pages.manageCatalog.MonographManagementListHandler.prototype.
			sortUpdateHandler_ = function(callingHandler, event, ui) {
		// Figure out where we are in the DOM and choose a new seq num
		var $monographElement = ui.item,
				$prevElement = $monographElement.prev(),
				$nextElement = $monographElement.next(),
				newSequence,
				prevHandler, nextHandler;

		if ($prevElement.length) {
			// Move to the previous nodes's sequence plus one.
			prevHandler = $.pkp.classes.Handler.getHandler($prevElement);
			newSequence = prevHandler.getSeq() + 1;
		} else if ($nextElement.length) {
			// Move to the next node's sequence minus one.
			nextHandler = $.pkp.classes.Handler.getHandler($nextElement);
			newSequence = nextHandler.getSeq() - 1;
		} else {
			// It's a one-element list and sorting is irrelevant.
			return false;
		}

		// Tell the monograph what the new sequence number is.
		$monographElement.trigger('setSequence', [newSequence]);

		// End processing here.
		return false;
	};


	/**
	 * Apply the format list function (either when the grid is
	 * first loaded, or after they are re-sorted.
	 *
	 * @private
	 *
	 * @param {$.pkp.classes.Handler=} opt_callingHandler The handler
	 *  that triggered the event.
	 * @param {Event=} opt_event The event.
	 */
	$.pkp.pages.manageCatalog.MonographManagementListHandler.prototype.
			resetElementHeights_ = function(opt_callingHandler, opt_event) {
		this.formatList();
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
