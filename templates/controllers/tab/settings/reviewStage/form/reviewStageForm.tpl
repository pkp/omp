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
		$('#reviewStageForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler',
			{ldelim}
				enableDisablePairs: {ldelim}'remindForInvite':'numDaysBeforeInviteReminder','remindForSubmit':'numDaysBeforeSubmitReminder'{rdelim}
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="reviewStageForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="saveFormData" tab="reviewStage"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="reviewStageFormNotification"}
	{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

	{url|assign:reviewLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_REVIEW}
	{load_url_in_div id="reviewLibraryGridDiv" url=$reviewLibraryGridUrl}

	<div {if $wizardMode}class="pkp_form_hidden"{/if}>
		{fbvFormArea id="reviewOptions" title="manager.setup.reviewOptions" border="true"}
			<!-- FIXME: also, fbvStyles.size.SMALL needs to be switched to TINY once there's a TINY option available -->
			{fbvFormSection label="manager.setup.reviewOptions.reviewTime" description="manager.setup.reviewOptions.noteOnModification"}
				{fbvElement type="text" label="manager.setup.reviewOptions.numWeeksPerResponse" name="numWeeksPerResponse" id="numWeeksPerResponse" value=$numWeeksPerResponse size=$fbvStyles.size.SMALL inline=true}
				{fbvElement type="text" label="manager.setup.reviewOptions.numWeeksPerReview" name="numWeeksPerReview" id="numWeeksPerReview" value=$numWeeksPerReview size=$fbvStyles.size.SMALL inline=true}
			{/fbvFormSection}
		{/fbvFormArea}
		{fbvFormArea id="reviewReminderOptions" title="manager.setup.reviewOptions.reviewerReminders" border="true"}
			{capture assign="reviewReminderNote"}{translate key="manager.setup.reviewOptions.automatedReminders"} {translate key="manager.setup.reviewOptions.automatedRemindersDisabled"}{/capture}
			{fbvFormSection description=$reviewReminderNote translate=false}{/fbvFormSection}

			{fbvFormSection description="manager.setup.reviewOptions.remindForInvite"}

				{fbvElement type="checkbox" id="remindForInvite" value="1" checked=$remindForInvite disabled=$scheduledTasksDisabled inline=true}
				{if not $remindForInvite || $scheduledTasksDisabled}{assign var="disabled" value=true}{else}{assign var="disabled" value=false}{/if}
				{fbvElement type="select" from=$numDaysBeforeInviteReminderValues selected=$numDaysBeforeInviteReminder id="numDaysBeforeInviteReminder" disabled=$disabled translate=false size=$fbvStyles.size.SMALL inline=true}
			{/fbvFormSection}

			{fbvFormSection description="manager.setup.reviewOptions.remindForSubmit"}
				{fbvElement type="checkbox" id="remindForSubmit" value="1" checked=$remindForSubmit disabled=$scheduledTasksDisabled inline=true}
				{if not $remindForSubmit || $scheduledTasksDisabled}{assign var="disabled" value=true}{else}{assign var="disabled" value=false}{/if}
				{fbvElement type="select" from=$numDaysBeforeSubmitReminderValues selected=$numDaysBeforeSubmitReminder id="numDaysBeforeSubmitReminder" disabled=$disabled translate=false size=$fbvStyles.size.SMALL inline=true}
			{/fbvFormSection}
		{/fbvFormArea}

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
			{fbvFormSection label="manager.setup.reviewGuidelines" description="manager.setup.reviewGuidelinesDescription"}
				{fbvElement type="textarea" multilingual="true" name="reviewGuidelines" id="reviewGuidelines" value=$reviewGuidelines  rich=true}
			{/fbvFormSection}
			{fbvFormSection label="manager.setup.competingInterests" description="manager.setup.competingInterestsDescription"}
				{fbvElement type="textarea" multilingual="true" id="competingInterests" value=$competingInterests rich=true}
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	{if !$wizardMode}
		{fbvFormButtons id="reviewStageFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>
