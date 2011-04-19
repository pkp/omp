{**
 * templates/controllers/grid/user/reviewer/form/advancedSearchReviewerForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Advanced Search and assignment reviewer form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Handle moving the reviewer ID from the grid to the second form
		$('#advancedReviewerSearch').pkpHandler('$.pkp.controllers.AdvancedReviewerSearchHandler');
	{rdelim});
</script>

<div id="advancedReviewerSearch" class="pkp_form_advancedReviewerSearch">
	{** The grid that will display reviewers.  We have a JS handler for handling selections of this grid which will update a hidden element in the form below **}
	{url|assign:reviewerSelectGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.users.reviewerSelect.ReviewerSelectGridHandler" op="fetchGrid" monographId=$monographId escape=false}
	{load_url_in_div id='reviewerSelectGridContainer' url="$reviewerSelectGridUrl"}

	{** This button will get the reviewer selected in the grid and insert their ID into the form below **}
	{fbvButton id="selectReviewerButton" label="editor.monograph.selectReviewer"}
	<br />

	{** Display the name of the selected reviewer so the user knows their button click caused an action **}
	{fbvFormSection title="editor.monograph.selectedReviewer"}
		<span id="selectedReviewerName">{translate key="editor.monograph.selectReviewerInstructions"}</span>
	{/fbvFormSection}
	<br />

	{include file="controllers/grid/users/reviewer/form/advancedSearchReviewerAssignmentForm.tpl"}
</div>
