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
<script type="text/javascript">{literal}
	$(function() {
		getAutocompleteSource("{/literal}{url op="getReviewerAutocomplete" monographId=$monographId}", "{$randomId}{literal}");
		$("#responseDueDate").datepicker({ dateFormat: 'yy-mm-dd' });
		$("#reviewDueDate").datepicker({ dateFormat: 'yy-mm-dd' });
		$("#reviewerSearch").accordion({
			autoHeight: false,
			collapsible: true
		});
	});
{/literal}</script>

<form name="addReviewerForm" id="addReviewer-{$randomId}" method="post" action="{url op="updateReviewer"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="reviewAssignmentId" value="{$reviewAssignmentId}" />
	<input type="hidden" name="reviewType" value="{$reviewType|escape}" />
	<input type="hidden" name="round" value="{$round|escape}" />
	
	<div id="reviewerSearch" style="margin:7px;">
	<!--  Reviewer autosuggest selector -->
	<h3><a href="#">{translate key="manager.reviewerSearch.searchByName"}</a></h3>
		<div id="reviewerNameSearch">
			{fbvFormSection}
				{fbvElement type="text" id="sourceTitle-"|concat:$randomId name="reviewerSelectAutocomplete" label="user.role.reviewer" value=$userNameString|escape }
				<input type="hidden" id="sourceId-{$randomId}" name="reviewerId" />
			{/fbvFormSection}
		</div>
		
		<!--  Advanced reviewer search -->
		<h3><a href="#">{translate key="manager.reviewerSearch.advancedSearch"}</a></h3>
		<div id="reviewerAdvancedSearch">
			{url|assign:reviewerSelectorUrl router=$smarty.const.ROUTE_COMPONENT component="reviewerSelector.ReviewerSelectorHandler" op="fetchForm" monographId=$monographId}
			{load_url_in_div id="reviewerSelectorContainer" url="$reviewerSelectorUrl"}
		</div>
	</div>

	<!--  Message to reviewer textarea -->
	{fbvFormSection}
		{fbvElement type="textarea" name="personalMessage" id="personalMessage" label="editor.review.personalMessageToReviewer" value=$personalMessage|escape measure=$fbvStyles.measure.3OF4 size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	<!--  Reviewer due dates (see http://jqueryui.com/demos/datepicker/) -->
	{fbvFormSection layout=$fbvStyles.layout.TWO_COLUMNS}
		{fbvElement type="text" id="responseDueDate" name="responseDueDate" label="editor.review.responseDueDate" value=$responseDueDate }
		{fbvElement type="text" id="reviewDueDate" name="reviewDueDate" label="editor.review.reviewDueDate" value=$reviewDueDate }
	{/fbvFormSection}

	<!--  Ensuring a blind review for this reviewer -->
	{if $reviewMethod == 1}
		{assign var='blindReview' value=true}
	{elseif $reviewMethod == 2}
		{assign var='doubleBlindReview' value=true}
	{elseif $reviewMethod == 3}
		{assign var='openReview' value=true}
	{/if}
	{fbvFormSection title="editor.submissionReview.reviewType"}
		{fbvElement type="radio" name="reviewMethod" id="blindReview" label="editor.submissionReview.blind" checked=$blindReview}
		{fbvElement type="radio" name="reviewMethod" id="doubleBlindReview" label="editor.submissionReview.doubleBlind" checked=$doubleBlindReview}
		{fbvElement type="radio" name="reviewMethod" id="openReview" label="editor.submissionReview.open" checked=$openReview}
	{/fbvFormSection}
</form>