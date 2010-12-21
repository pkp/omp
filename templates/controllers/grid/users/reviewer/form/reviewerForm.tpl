{**
 * reviewerForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Review assignment form
 *
 *}

{modal_title id="#addReviewer" key='editor.monograph.addReviewer' iconClass="fileManagement" canClose=1}

<script type="text/javascript">{literal}
	<!--
	$(function() {
		getAutocompleteSource("{/literal}{url op="getReviewerAutocomplete" monographId=$monographId round=$round escape=false}{literal}", "reviewerSearch");
		getAutocompleteSource("{/literal}{url op="getReviewerRoleAssignmentAutocomplete" monographId=$monographId round=$round escape=false}{literal}", "enrollSearch");
		$("#responseDueDate").datepicker({ dateFormat: 'yy-mm-dd' });
		$("#reviewDueDate").datepicker({ dateFormat: 'yy-mm-dd' });
		$("#sourceTitle").addClass('required');
		{/literal}{if $selectionType}{assign var=selectedTab value=$selectionType-1}{else}{assign var=selectedTab value=0}{/if}{literal}
		$("#reviewerSearch").tabs({
			collapsible: true,
			selected: {/literal}{$selectedTab}{literal},
			select: function(event, ui) {
				var selected = ui.tab.id;

				// Make current selection type's required fields required
				switch(selected){
					case 'tab-1': // Search by name
						$("#selectionType").val({/literal}{$smarty.const.REVIEWER_SELECT_SEARCH}{literal}); // Set selection type input to id of open tab
						$("#sourceTitle-reviewerSearch").addClass('required');
						$("#firstname, #lastname, #username, #email, .advancedReviewerSelect, #sourceTitle-enrollSearch").removeClass('required');
						break;
					case 'tab-2': // Advanced search
						$("#selectionType").val({/literal}{$smarty.const.REVIEWER_SELECT_ADVANCED}{literal}); // Set selection type input to id of open tab
						$(".advancedReviewerSelect").addClass('required');
						$("#firstname, #lastname, #username, #email, #sourceTitle-enrollSearch, #sourceTitle-enrollSearch").removeClass('required');
						break;
					case 'tab-3': // Create new reviewer
						$("#selectionType").val({/literal}{$smarty.const.REVIEWER_SELECT_CREATE}{literal}); // Set selection type input to id of open tab
						$("#firstname, #lastname, #username, #email").addClass('required');
						$(".advancedReviewerSelect, #sourceTitle-enrollSearch, #sourceTitle-enrollSearch").removeClass('required');
						break;
					case 'tab-4': // Enroll existing user as reviewer
						$("#selectionType").val({/literal}{$smarty.const.REVIEWER_SELECT_ENROLL}{literal}); // Set selection type input to id of open tab
						$("#sourceTitle-enrollSearch").addClass('required');
						$("#firstname, #lastname, #username, #email, .advancedReviewerSelect, #sourceTitle-enrollSearch").removeClass('required');
						break;
				}

			}
		});

		$("#interests").tagit({
			// This is the list of interests in the system used to populate the autocomplete
			availableTags: [{/literal}{foreach name=existingInterests from=$existingInterests item=interest}"{$interest|escape|escape:'javascript'}"{if !$smarty.foreach.existingInterests.last}, {/if}{/foreach}]{literal},
			currentTags: []
		});
	});
	// -->
{/literal}</script>

<form name="addReviewerForm" id="addReviewer" method="post" action="{url op="updateReviewer" monographId=$monographId}" >
	<input type="hidden" name="reviewAssignmentId" value="{$reviewAssignmentId}" />
	<input type="hidden" name="reviewType" value="{$reviewType|escape}" />
	<input type="hidden" name="round" value="{$round|escape}" />
	<input type="hidden" name="selectionType" id="selectionType" value="{if $selectionType == 0}1{else}{$selectionType}{/if}" /> <!--  Holds the type of reviewer selection being used -->

	<div id="reviewerSearch">
		<ul>
			<li><a id="tab-1" href="#nameSearch">{translate key="manager.reviewerSearch.searchByName.short"}</a></li>
			<li><a id="tab-2" href="#advancedSearch">{translate key="manager.reviewerSearch.advancedSearch.short"}</a></li>
			<li><a id="tab-3" href="#createNew">{translate key="editor.review.createReviewer"}</a></li>
			<li><a id="tab-4" href="#assignExisting">{translate key="editor.review.enrollReviewer.short"}</a></li>
		</ul>
		<!-- Reviewer autosuggest selector -->
		<div id="nameSearch">
			<h3>{translate key="manager.reviewerSearch.searchByName"}</h3>
			{fbvFormSection}
				{fbvElement type="text" id="sourceTitle-reviewerSearch" name="reviewerSelectAutocomplete" label="user.role.reviewer" required="true" class="required" value=$userNameString|escape }
				<input type="hidden" id="sourceId-reviewerSearch" name="reviewerId" />
			{/fbvFormSection}
		</div>

		<!-- Advanced reviewer search -->
		<div id="advancedSearch">
			<h3>{translate key="manager.reviewerSearch.advancedSearch"}</h3>
			{url|assign:reviewerSelectorUrl router=$smarty.const.ROUTE_COMPONENT component="reviewerSelector.ReviewerSelectorHandler" op="fetchForm" monographId=$monographId}
			{load_url_in_div id="reviewerSelectorContainer" url="$reviewerSelectorUrl"}
		</div>

		<!-- Create New Reviewer -->
		<div id="createNew">
			<h3>{translate key="editor.review.createReviewer"}</h3>
			{fbvFormSection title="user.group"}
				{fbvElement type="select" name="userGroupId" id="userGroupId" from=$userGroups translate=false label="editor.review.userGroupSelect" required="true"}
			{/fbvFormSection}
			{fbvFormSection title="common.name"}
				{fbvElement type="text" label="user.firstName" id="firstname" value=$firstName required="true"}
				{fbvElement type="text" label="user.middleName" id="middlename" value=$middleName}
				{fbvElement type="text" label="user.lastName" id="lastname" value=$lastName required="true"}
			{/fbvFormSection}

			{fbvFormSection title="user.affiliation" for="affiliation"}
				{fbvElement type="textarea" id="affiliation" value=$affiliation size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}
			{/fbvFormSection}

			{fbvFormSection title="user.interests" for="interests"}
				<ul id="interests"></ul>
			{/fbvFormSection}

			{fbvFormSection title="user.accountInformation"}
				{fbvElement type="text" label="user.username" id="username" value=$username required="true"} <br />
			{/fbvFormSection}

			{fbvFormSection for="email"}
				{fbvElement type="text" label="user.email" id="email" class="email" value=$email required="true"}
				{fbvElement type="checkbox" id="sendNotify" value="1" label="manager.people.createUserSendNotify" checked=$sendNotify}
			{/fbvFormSection}
		</div>

		<!-- Assign reviewer role to existing reviewer -->
		<div id="assignExisting">
			<h3>{translate key="editor.review.enrollReviewer"}</h3>
			{fbvFormSection title="user.group"}
				{fbvElement type="select" name="userGroupId" id="userGroupId" from=$userGroups translate=false label="editor.review.userGroupSelect" required="true"}
			{/fbvFormSection}
			{fbvFormSection}
				{fbvElement type="text" id="sourceTitle-enrollSearch" name="userEnrollmentAutocomplete" label="user.role.reviewer" required="true" class="required" value=$userNameString|escape }
				<input type="hidden" id="sourceId-enrollSearch" name="userId" />
			{/fbvFormSection}
		</div>
	</div>

	<!--  Message to reviewer textarea -->
	{fbvFormSection}
		{fbvElement type="textarea" name="personalMessage" id="personalMessage" label="editor.review.personalMessageToReviewer" value=$personalMessage|escape measure=$fbvStyles.measure.3OF4 size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}

	<!--  Reviewer due dates (see http://jqueryui.com/demos/datepicker/) -->
	{fbvFormSection}
		{fbvElement type="text" id="responseDueDate" name="responseDueDate" label="editor.responseDueDate" value=$responseDueDate }
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

