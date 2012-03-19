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

	<h3 class="pkp_grid_title">{translate key="manager.setup.reviewLibrary"}</h3>
	<p class="pkp_grid_description">{translate key="manager.setup.reviewLibraryDescription"}</p>
	{url|assign:reviewLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_REVIEW}
	{load_url_in_div id="reviewLibraryGridDiv" url=$reviewLibraryGridUrl}

	<div class="separator"></div>

	<div {if $wizardMode}class="pkp_form_hidden"{/if}>
		{fbvFormArea id="reviewOptions" title="manager.setup.reviewOptions" border="true"}
			<!-- FIXME: the following embedded p tags within fbvForm stuff can probably be done better -->
			<!-- FIXME: also, fbvStyles.size.SMALL needs to be switched to TINY once there's a TINY option available -->
			{fbvFormSection label="manager.setup.reviewOptions.reviewTime"}
				<p>{translate key="manager.setup.reviewOptions.numWeeksPerResponse"}: {fbvElement type="text" name="numWeeksPerResponse" id="numWeeksPerResponse" value=$numWeeksPerResponse size=$fbvStyles.size.SMALL inline=true} {translate key="common.weeks"}</p>
				<p>{translate key="manager.setup.reviewOptions.numWeeksPerReview"}: {fbvElement type="text" name="numWeeksPerReview" id="numWeeksPerReview" value=$numWeeksPerReview size=$fbvStyles.size.SMALL inline=true} {translate key="common.weeks"}</p>
				<p>{translate key="common.note"}: {translate key="manager.setup.reviewOptions.noteOnModification"}</p>
			{/fbvFormSection}
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
			{fbvFormSection label="manager.setup.reviewOptions.reviewerReminders" description="manager.setup.reviewOptions.automatedReminders" list=true}
				{fbvElement type="checkbox" id="remindForInvite" label="manager.setup.reviewOptions.remindForInvite1" value="1" checked=$remindForInvite disabled=!$scheduledTasksEnabled}
				<!-- FIXME: These two selects should be converted into fbvElemnts -->
				<select name="numDaysBeforeInviteReminder" size="1" class="selectMenu"{if not $remindForInvite || !$scheduledTasksEnabled} disabled="disabled"{/if}>
					{section name="inviteDayOptions" start=3 loop=11}
					<option value="{$smarty.section.inviteDayOptions.index}"{if $numDaysBeforeInviteReminder eq $smarty.section.inviteDayOptions.index or ($smarty.section.inviteDayOptions.index eq 5 and not $remindForInvite)} selected="selected"{/if}>{$smarty.section.inviteDayOptions.index}</option>
					{/section}
				</select>
				{translate key="manager.setup.reviewOptions.remindForInvite2"}
				<br />
				{fbvElement type="checkbox" id="remindForSubmit" label="manager.setup.reviewOptions.remindForSubmit1" value="1" checked=$remindForSubmit disabled=!$scheduledTasksEnabled}
				<select name="numDaysBeforeSubmitReminder" size="1" class="selectMenu"{if not $remindForSubmit || !$scheduledTasksEnabled} disabled="disabled"{/if}>
					{section name="submitDayOptions" start=0 loop=11}
						<option value="{$smarty.section.submitDayOptions.index}"{if $numDaysBeforeSubmitReminder eq $smarty.section.submitDayOptions.index} selected="selected"{/if}>{$smarty.section.submitDayOptions.index}</option>
				{/section}
				</select>
				{translate key="manager.setup.reviewOptions.remindForSubmit2"}
				<p>&nbsp;</p>
				{if !$scheduledTasksEnabled}
					<p>{translate key="manager.setup.reviewOptions.automatedRemindersDisabled"}</p>
				{/if}
			{/fbvFormSection}

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
					{fbvElement type="textarea" multilingual="true" name="reviewGuidelines" id="reviewGuidelines" value=$reviewGuidelines size=$fbvStyles.size.MEDIUM  rich=true}
				{/fbvFormSection}
				{fbvFormSection label="manager.setup.competingInterests" description="manager.setup.competingInterestsDescription"}
					{fbvElement type="textarea" multilingual="true" id="competingInterests" value=$competingInterests size=$fbvStyles.size.MEDIUM  rich=true}
				{/fbvFormSection}
			{/fbvFormArea}
		</div>
		{/fbvFormArea}
	{if !$wizardMode}
		{fbvFormButtons id="reviewStageFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>