{**
 * controllers/tab/settings/reviewStage/form/reviewStageForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Review stage management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#reviewStageForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form id="reviewStageForm" class="pkp_controllers_form" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="saveFormData" tab="reviewStage"}">
	{include file="common/formErrors.tpl"}
	{include file="controllers/tab/settings/wizardMode.tpl wizardMode=$wizardMode}

	<h3>{translate key="manager.setup.reviewLibrary"}</h3>

	{url|assign:reviewLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_REVIEW}
	{load_url_in_div id="reviewLibraryGridDiv" url=$reviewLibraryGridUrl}

	<div class="separator"></div>

	<h3>{translate key="manager.setup.reviewProcess"}</h3>
	<p>{translate key="manager.setup.reviewProcessDescription"}</p>

	{fbvFormArea id="reviewProcess"}
		{fbvFormSection layout=$fbvStyles.layout.ONE_COLUMN}
			{fbvElement type="radio" name="mailSubmissionsToReviewers" id="mailSubmissionsToReviewers-0" value="0" checked=!$mailSubmissionsToReviewers label="manager.setup.reviewProcessStandard"}
			{fbvElement type="radio" name="mailSubmissionsToReviewers" id="mailSubmissionsToReviewers-1" value="1" checked=$mailSubmissionsToReviewers label="manager.setup.reviewProcessEmail"}
		{/fbvFormSection}
	{/fbvFormArea}

	<!-- Create a function in form handler for this functionality. See *6654*  -->
	<script type="text/javascript">
		{literal}
		<!--
			function toggleAllowSetInviteReminder(form) {
				form.numDaysBeforeInviteReminder.disabled = !form.numDaysBeforeInviteReminder.disabled;
			}
			function toggleAllowSetSubmitReminder(form) {
				form.numDaysBeforeSubmitReminder.disabled = !form.numDaysBeforeSubmitReminder.disabled;
			}
		// -->
		{/literal}
	</script>

	<h3>{translate key="manager.setup.reviewOptions"}</h3>
	<p><strong>{translate key="manager.setup.reviewOptions.reviewTime"}</strong></p>

	{fbvFormArea id="reviewTime"}
		{fbvFormSection}
			<span>{translate key="manager.setup.reviewOptions.numWeeksPerResponse"}:</span>
			{fbvElement type="text" name="numWeeksPerResponse" id="numWeeksPerResponse" value=$numWeeksPerResponse size=$fbvStyles.size.SMALL}
			<span>{translate key="common.weeks"}</span>
		{/fbvFormSection}
		{fbvFormSection}
			<span>{translate key="manager.setup.reviewOptions.numWeeksPerReview"}:</span>
			{fbvElement type="text" name="numWeeksPerReview" id="numWeeksPerReview" value=$numWeeksPerReview size=$fbvStyles.size.SMALL}
			<span>{translate key="common.weeks"}</span>
		{/fbvFormSection}
	{/fbvFormArea}

	<p>{translate key="common.note"}: {translate key="manager.setup.reviewOptions.noteOnModification"}</p>

	<p>
		<strong>{translate key="manager.setup.reviewOptions.reviewerReminders"}</strong><br/>
		{translate key="manager.setup.reviewOptions.automatedReminders"}:<br/>
		<input type="checkbox" name="remindForInvite" id="remindForInvite" value="1" onclick="toggleAllowSetInviteReminder(this.form)"{if !$scheduledTasksEnabled} disabled="disabled" {elseif $remindForInvite} checked="checked"{/if} />&nbsp;
		<label for="remindForInvite">{translate key="manager.setup.reviewOptions.remindForInvite1"}</label>
		<select name="numDaysBeforeInviteReminder" size="1" class="selectMenu"{if not $remindForInvite || !$scheduledTasksEnabled} disabled="disabled"{/if}>
			{section name="inviteDayOptions" start=3 loop=11}
			<option value="{$smarty.section.inviteDayOptions.index}"{if $numDaysBeforeInviteReminder eq $smarty.section.inviteDayOptions.index or ($smarty.section.inviteDayOptions.index eq 5 and not $remindForInvite)} selected="selected"{/if}>{$smarty.section.inviteDayOptions.index}</option>
			{/section}
		</select>
		{translate key="manager.setup.reviewOptions.remindForInvite2"}
		<br/>

		<input type="checkbox" name="remindForSubmit" id="remindForSubmit" value="1" onclick="toggleAllowSetSubmitReminder(this.form)"{if !$scheduledTasksEnabled} disabled="disabled"{elseif $remindForSubmit} checked="checked"{/if} />&nbsp;
		<label for="remindForSubmit">{translate key="manager.setup.reviewOptions.remindForSubmit1"}</label>
		<select name="numDaysBeforeSubmitReminder" size="1" class="selectMenu"{if not $remindForSubmit || !$scheduledTasksEnabled} disabled="disabled"{/if}>
			{section name="submitDayOptions" start=0 loop=11}
				<option value="{$smarty.section.submitDayOptions.index}"{if $numDaysBeforeSubmitReminder eq $smarty.section.submitDayOptions.index} selected="selected"{/if}>{$smarty.section.submitDayOptions.index}</option>
		{/section}
		</select>
		{translate key="manager.setup.reviewOptions.remindForSubmit2"}
		{if !$scheduledTasksEnabled}
		<br/>
		{translate key="manager.setup.reviewOptions.automatedRemindersDisabled"}
		{/if}
	</p>

	{fbvFormArea id="reviewProcessDetails"}
		{fbvFormSection title="manager.setup.reviewOptions.reviewerRatings" layout=$fbvStyles.layout.ONE_COLUMN}
			{fbvElement type="checkbox" id="rateReviewerOnQuality" value="1" checked=$rateReviewerOnQuality label="manager.setup.reviewOptions.onQuality"}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.reviewOptions.reviewerAccess" layout=$fbvStyles.layout.ONE_COLUMN}
			{fbvElement type="checkbox" id="reviewerAccessKeysEnabled" value="1" checked=$reviewerAccessKeysEnabled label="manager.setup.reviewOptions.reviewerAccessKeysEnabled"}
			<span><p>{translate key="manager.setup.reviewOptions.reviewerAccessKeysEnabled.description"}</p></span>
			{fbvElement type="checkbox" id="restrictReviewerFileAccess" value="1" checked=$restrictReviewerFileAccess label="manager.setup.reviewOptions.restrictReviewerFileAccess"}
		{/fbvFormSection}
		{fbvFormSection title="manager.setup.reviewOptions.blindReview" layout=$fbvStyles.layout.ONE_COLUMN}
			{fbvElement type="checkbox" id="showEnsuringLink" value="1" checked=$showEnsuringLink label="manager.setup.reviewOptions.showEnsuringLink"}
		{/fbvFormSection}
	{/fbvFormArea}

	<div class="separator"></div>

	<h3>{translate key="manager.setup.reviewForms"}</h3>

	{url|assign:reviewFormGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.reviewForm.ReviewFormGridHandler" op="fetchGrid"}
	{load_url_in_div id="reviewFormGridDiv" url=$reviewFormGridUrl}

	<div class="separator"></div>

	<div {if $wizardMode}class="pkp_form_hidden"{/if}>
		<h3>{translate key="manager.setup.reviewGuidelines"}</h3>
		<p>{translate key="manager.setup.reviewGuidelinesDescription"}</p>

		{fbvFormArea id="review"}
			{fbvFormSection}
				{fbvElement type="textarea" multilingual="true" name="reviewGuidelines" id="reviewGuidelines" value=$reviewGuidelines size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4 rich=true}
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	{include file="form/formButtons.tpl" submitText="common.save"}
</form>