{**
 * templates/controllers/grid/user/reviewer/form/advancedSearchReviewerFilterForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
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
<form id="reviewerFilterForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.users.reviewerSelect.ReviewerSelectGridHandler" op="fetchGrid" monographId=$monographId}" method="post" class="pkp_controllers_reviewerSelector">

	{include file="common/formErrors.tpl"}

	<input type="hidden" id="monographId" name="monographId" value="{$monographId}" />
	{fbvFormArea id="reviewerSearchForm"}
		{fbvFormSection float=$fbvStyles.float.LEFT}
			{fbvElement type="rangeSlider" id="done" label="manager.reviewerSearch.doneAmount" min=$reviewerValues.done_min max=$reviewerValues.done_max}
		{/fbvFormSection}
		{fbvFormSection float=$fbvStyles.float.RIGHT}
			{fbvElement type="rangeSlider" id="avg" label="manager.reviewerSearch.avgAmount" min=$reviewerValues.avg_min max=$reviewerValues.avg_max}
		{/fbvFormSection}
		{fbvFormSection float=$fbvStyles.float.LEFT}
			{fbvElement type="rangeSlider" id="last" label="manager.reviewerSearch.lastAmount" min=$reviewerValues.last_min max=$reviewerValues.last_max}
		{/fbvFormSection}
		{fbvFormSection float=$fbvStyles.float.RIGHT}
			{fbvElement type="rangeSlider" id="active" label="manager.reviewerSearch.activeAmount" min=$reviewerValues.active_min max=$reviewerValues.active_max}
		{/fbvFormSection}
		{fbvFormSection}
			{fbvKeywordInput id="interestSearch" available=$interestSearchKeywords label="manager.reviewerSearch.interests"}
		{/fbvFormSection}
		{fbvFormSection}
			<input type="submit" class="button" id="submitFilter" value="{translate key="common.refresh"}" />
		{/fbvFormSection}
	{/fbvFormArea}
</form>
`