{**
 * controllers/tab/settings/reviewStage/form/reviewStageForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
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

<form class="pkp_form" id="reviewStageForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="saveFormData" tab="reviewStage"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="reviewStageFormNotification"}
	{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

	{url|assign:reviewLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_REVIEW}
	{load_url_in_div id="reviewLibraryGridDiv" url=$reviewLibraryGridUrl}

	<div class="separator"></div>

	<div {if $wizardMode}class="pkp_form_hidden"{/if}>
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
			<p>{translate key="common.note"}: {translate key="manager.setup.reviewOptions.noteOnModification"}</p>
		{/fbvFormArea}

		<!-- FIXME: create a function in form handler for this functionality. See #6654#  -->
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
			{fbvFormSection title="manager.setup.reviewOptions.reviewerRatings" list=true}
				{fbvElement type="checkbox" id="rateReviewerOnQuality" value="1" checked=$rateReviewerOnQuality label="manager.setup.reviewOptions.onQuality"}
			{/fbvFormSection}
			{fbvFormSection title="manager.setup.reviewOptions.blindReview" list=true}
				{fbvElement type="checkbox" id="showEnsuringLink" value="1" checked=$showEnsuringLink label=manager.setup.reviewOptions.showEnsuringLink}
			{/fbvFormSection}
			{include file="linkAction/linkAction.tpl" action=$ensuringLink contextId="uploadForm"}
		{/fbvFormArea}

		{fbvFormArea id="review"}
			{fbvFormSection title="manager.setup.reviewGuidelines"}
				<p>{translate key="manager.setup.reviewGuidelinesDescription"}</p>
				{fbvElement type="textarea" multilingual="true" name="reviewGuidelines" id="reviewGuidelines" value=$reviewGuidelines size=$fbvStyles.size.MEDIUM  rich=true}
			{/fbvFormSection}
			{fbvFormSection title="manager.setup.competingInterests"}
				<p>{translate key="manager.setup.competingInterestsDescription"}</p>
				{fbvElement type="textarea" multilingual="true" id="competingInterests" value=$competingInterests size=$fbvStyles.size.MEDIUM  rich=true}
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	{if !$wizardMode}
		{fbvFormButtons id="reviewStageFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>