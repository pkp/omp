<!-- templates/controllers/grid/users/reviewer/form/reviewerForm.tpl -->

{**
 * reviewerForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Review assignment form
 *
 *}
{assign var='uniqueId' value=""|uniqid}

{modal_title id="#addReviewer" key='editor.monograph.addReviewer' iconClass="fileManagement" canClose=1}

<script type="text/javascript">{literal}
	$(function() {
		getAutocompleteSource("{/literal}{url op="getReviewerAutocomplete" monographId=$monographId}", "{}{literal}");
		$("#responseDueDate").datepicker({ dateFormat: 'yy-mm-dd' });
		$("#reviewDueDate").datepicker({ dateFormat: 'yy-mm-dd' });
		$("#sourceTitle").addClass('required');
		$("#reviewerSearch").accordion({
			autoHeight: false,
			collapsible: true,
			change: function(event, ui) {
				var newId = ui.newHeader.attr("id");
				$("#selectionType").val(newId); // Set selection type input to id of open tab

				// Make current selection type's required fields required
				switch(newId){
					case 'searchByName':
						$("#sourceTitle").addClass('required');
						$(".advancedReviewerSelect").removeClass('required');
						$("#firstname, #lastname, #username, #email").removeClass('required');
						break;
					case 'advancedSearch':
						$("#sourceTitle").removeClass('required');
						$(".advancedReviewerSelect").addClass('required');
						$("#firstname, #lastname, #username, #email").removeClass('required');
						break;
					case 'createNew':
						$("#sourceTitle").removeClass('required');
						$(".advancedReviewerSelect").removeClass('required');
						$("#firstName, #lastName, #username, #email").addClass('required');
						$("#email").addClass('email');
						break;
				}

			}
		});

		$("#interests").tagit({
			availableTags: [{/literal}{$existingInterests}{literal}]
		});
	});
{/literal}</script>

<form name="addReviewerForm" id="addReviewer" method="post" action="{url op="updateReviewer"}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="reviewAssignmentId" value="{$reviewAssignmentId}" />
	<input type="hidden" name="reviewType" value="{$reviewType|escape}" />
	<input type="hidden" name="round" value="{$round|escape}" />
	<input type="hidden" name="selectionType" id="selectionType" value="searchByName" /> <!--  Holds the type of reviewer selection being used -->

	<div id="reviewerSearch" style="margin:7px;">
		<!--  Reviewer autosuggest selector -->
		<h3 id="searchByName"><a href="#">{translate key="manager.reviewerSearch.searchByName"}</a></h3>
		<div id="reviewerNameSearch">
			{fbvFormSection}
				{fbvElement type="text" id="sourceTitle"|concat: name="reviewerSelectAutocomplete" label="user.role.reviewer" class="required" value=$userNameString|escape }
				<input type="hidden" id="sourceId" name="reviewerId" />
			{/fbvFormSection}
		</div>

		<!--  Advanced reviewer search -->
		<h3 id="advancedSearch"><a href="#">{translate key="manager.reviewerSearch.advancedSearch"}</a></h3>
		<div id="reviewerAdvancedSearch">
			{url|assign:reviewerSelectorUrl router=$smarty.const.ROUTE_COMPONENT component="reviewerSelector.ReviewerSelectorHandler" op="fetchForm" monographId=$monographId}
			{load_url_in_div id="reviewerSelectorContainer" url="$reviewerSelectorUrl"}
		</div>

		<!--  Create New Reviewer -->
		<h3 id="createNew"><a href="#">{translate key="seriesEditor.review.createReviewer"}</a></h3>
		<div id="reviewerCreationForm">
			{fbvFormSection title="common.name"}
				{fbvElement type="text" label="user.firstName" id="firstName" value=$firstName required="true"}
				{fbvElement type="text" label="user.middleName" id="middleName" value=$middleName}
				{fbvElement type="text" label="user.lastName" id="lastName" value=$lastName required="true"}
			{/fbvFormSection}

			{fbvFormSection title="user.affiliation" for="affiliation" float=$fbvStyles.float.LEFT}
				{fbvElement type="textarea" id="affiliation" value=$affiliation size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}
			{/fbvFormSection}

			{fbvFormSection title="user.interests" for="interests"}
				<ul id="interests"></ul>
			{/fbvFormSection}

			{fbvFormSection title="user.accountInformation"}
				{fbvElement type="text" label="user.username" id="username" value=$username required="true"} <br />
			{/fbvFormSection}

			{fbvFormSection title="user.email" for="email"}
				{fbvElement type="text" id="email" value=$email required="true"}
				{fbvElement type="checkbox" id="sendNotify" value="1" label="manager.people.createUserSendNotify" checked=$sendNotify}
			{/fbvFormSection}
		</div>
	</div>

	<!--  Message to reviewer textarea -->
	{fbvFormSection}
		{fbvElement type="textarea" name="personalMessage" id="personalMessage" label="editor.review.personalMessageToReviewer" value=$personalMessage|escape measure=$fbvStyles.measure.3OF4 size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	<!--  Reviewer due dates (see http://jqueryui.com/demos/datepicker/) -->
	{fbvFormSection}
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

{init_button_bar id="#addReviewer" submitText="editor.monograph.addReviewer"}

<!-- / templates/controllers/grid/users/reviewer/form/reviewerForm.tpl -->
