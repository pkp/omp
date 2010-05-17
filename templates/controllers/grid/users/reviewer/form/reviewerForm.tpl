{**
 * reviewerForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Review assignment form
 *
 *}
{assign var='randomId' value=1|rand:99999}
<script type='text/javascript'>
	getAutocompleteSource("{url op="getReviewerAutocomplete" monographId=$monographId}", "{$randomId}");
</script>

<form name="addReviewerForm" id="addReviewer-{$randomId}" method="post" action="{url op="updateReviewer"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="reviewAssignmentId" value="{$reviewAssignmentId}" />
	<input type="hidden" name="reviewType" value="{$reviewType|escape}" />
	<input type="hidden" name="round" value="{$round|escape}" />
	
	<!--  Reviewer autosuggest selector -->
	{fbvFormSection}
		{fbvElement type="text" id="sourceTitle-"|concat:$randomId name="reviewerSelectAutocomplete" label="user.role.reviewer" value=$userNameString|escape }
		<input type="hidden" id="sourceId-{$randomId}" name="reviewerId" />
	{/fbvFormSection}

	<!--  Message to reviewer textarea -->
	{fbvFormSection}
		{fbvElement type="textarea" name="personalMessage" id="personalMessage" label="editor.review.personalMessageToReviewer" value=$personalMessage|escape measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}

	<!--  Reviewer due dates (see http://jqueryui.com/demos/datepicker/) -->
	{fbvFormSection layout=$fbvStyles.layout.TWO_COLUMNS}
		{fbvElement type="text" id="responseDueDate" name="responseDueDate" label="editor.review.responseDueDate" value="" }
		{fbvElement type="text" id="reviewDueDate" name="reviewDueDate" label="editor.review.reviewDueDate" value="" }
	{/fbvFormSection}
	{literal}
	<script type="text/javascript">
	$(function() {
		$("#responseDueDate").datepicker();
		$("#reviewDueDate").datepicker();
	});
	</script>
	{/literal}
	
	<!--  Ensuring a blind review for this reviewer -->
	{fbvFormSection}
		{fbvElement type="checkbox" name="blindReview" id="blindReview" label="editor.review.thisIsBlindReview" checked=$blindReview|escape}
	{/fbvFormSection}

	<!--  File selection grid -->
	{** FIXME: need to set escape=false due to bug 5265 *}
	{url|assign:reviewFilesSelectionGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.ReviewFilesGridHandler" op="fetchGrid" monographId=$monographId reviewType=$reviewType round=$round escape=false}
	{load_url_in_div id="reviewFileSelection"|concat:$randomId url=$reviewFilesSelectionGridUrl}
</form>