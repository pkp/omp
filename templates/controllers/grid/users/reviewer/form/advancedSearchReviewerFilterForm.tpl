{**
 * templates/controllers/grid/user/reviewer/form/advancedSearchReviewerFilterForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays the widgets that generate the filter sent to the reviewerSelect grid.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Handle filter form submission
		$('#reviewerFilterForm').pkpHandler('$.pkp.controllers.form.ClientFormHandler');
	{rdelim});
</script>

{** This form contains the inputs that will be used to filter the list of reviewers in the grid below **}
<form class="pkp_form" id="reviewerFilterForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.reviewerSelect.ReviewerSelectGridHandler" op="fetchGrid"}" method="post" class="pkp_controllers_reviewerSelector">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="reviewerFilterFormNotification"}
	{fbvFormArea id="reviewerSearchForm"}
		<input type="hidden" id="monographId" name="monographId" value="{$monographId|escape}" />
		<input type="hidden" id="stageId" name="stageId" value="{$stageId|escape}" />
		<input type="hidden" name="done_min" value="0" />
		<input type="hidden" name="avg_min" value="0" />
		<input type="hidden" name="last_min" value="0" />
		<input type="hidden" name="active_min" value="0" />
		{fbvFormSection}
			{fbvElement type="text" id="done_max" name="done_max" value=$reviewerValues.done_max|escape label="manager.reviewerSearch.doneAmount" inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" id="avg_max" name="avg_max" value=$reviewerValues.done_max|escape label="manager.reviewerSearch.avgAmount"  inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" id="last_max" name="last_max" value=$reviewerValues.last_max|escape label="manager.reviewerSearch.lastAmount" inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" id="active_max" name="active_max" value=$reviewerValues.done_max|escape label="manager.reviewerSearch.activeAmount" inline=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}
		<br /><br /><br /><br /><br /><br />
		{fbvFormSection}
			{fbvElement type="interests" id="interests" interestKeywords=$interestsKeywords interestsTextOnly=$interestsTextOnly}
		{/fbvFormSection}
		{fbvFormSection class="center"}
			{fbvElement type="submit" id="submitFilter" label="common.search"}
		{/fbvFormSection}
	{/fbvFormArea}
</form>
`