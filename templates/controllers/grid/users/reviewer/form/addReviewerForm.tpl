{**
 * templates/controllers/grid/users/reviewer/addReviewerForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Add a reviewer to a submission (by one of four options).
 *
 *}
<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#addReviewerTabs').pkpHandler(
				'$.pkp.controllers.TabHandler',
				{ldelim}
					emptyLastTab: true
				{rdelim});
	{rdelim});
</script>
<div id="addReviewerTabs">
	<ul>
		<li><a href="{url op="showReviewerForm" selectionType=$smarty.const.REVIEWER_SELECT_SEARCH_BY_NAME monographId=$monographId|escape reviewAssignment=$reviewAssignmentId|escape reviewType=$reviewType|escape round=$round|escape}">{translate key="manager.reviewerSearch.searchByName.short"}</a></li>
		<li><a href="{url op="showReviewerForm" selectionType=$smarty.const.REVIEWER_SELECT_ADVANCED_SEARCH monographId=$monographId|escape reviewAssignment=$reviewAssignmentId|escape reviewType=$reviewType|escape round=$round|escape}">{translate key="manager.reviewerSearch.advancedSearch.short"}</a></li>
		<li><a href="{url op="showReviewerForm" selectionType=$smarty.const.REVIEWER_SELECT_CREATE monographId=$monographId|escape reviewAssignment=$reviewAssignmentId|escape reviewType=$reviewType|escape round=$round|escape}">{translate key="editor.review.createReviewer"}</a></li>
		<li><a href="{url op="showReviewerForm" selectionType=$smarty.const.REVIEWER_SELECT_ENROLL_EXISTING monographId=$monographId|escape reviewAssignment=$reviewAssignmentId|escape reviewType=$reviewType|escape round=$round|escape}">{translate key="editor.review.enrollReviewer.short"}</a></li>
	</ul>
</div>