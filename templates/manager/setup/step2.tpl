{**
 * step2.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 2 of press setup.
 *
 * $Id$
 *}
{assign var="pageTitle" value="manager.setup.pressPolicies"}
{include file="manager/setup/setupHeader.tpl"}

<form name="setupForm" method="post" action="{url op="saveSetup" path="2"}">
{include file="common/formErrors.tpl"}

{if count($formLocales) > 1}
{fbvFormArea id="locales"}
{fbvFormSection title="form.formLanguage" for="languageSelector"}
	{fbvCustomElement}
		{url|assign:"setupFormUrl" op="setup" path="2"}
		{form_language_chooser form="setupForm" url=$setupFormUrl}
		<span class="instruct">{translate key="form.formLanguage.description"}</span>
	{/fbvCustomElement}
{/fbvFormSection}
{/fbvFormArea}
{/if} {* count($formLocales) > 1*}

<h3>2.1 {translate key="manager.setup.focusAndScopeOfPress"}</h3>

<p>{translate key="manager.setup.focusAndScopeDescription"}</p>

{fbvFormArea id="focusAndScopeDescription"}
{fbvFormSection}
	{fbvElement type="textarea" name="focusScopeDesc[$formLocale]" id="focusScopeDesc" value=$focusScopeDesc[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

<h3>2.2 {translate key="manager.setup.divisionsAndSeries"}</h3>

{url|assign:divisionsUrl router=$smarty.const.ROUTE_COMPONENT component="listbuilder.settings.DivisionsListbuilderHandler" op="fetch"}
{load_url_in_div id="divisionsContainer" url=$divisionsUrl}

{url|assign:seriesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.series.SeriesGridHandler" op="fetchGrid"}
{load_url_in_div id="seriesGridDiv" url=$seriesGridUrl}

<div class="separator"></div>

<h3>2.3 {translate key="manager.setup.authorGuidelines"}</h3>

<p>{translate key="manager.setup.authorGuidelinesDescription"}</p>

{fbvFormArea id="focusAndScopeDescription"}
{fbvFormSection}
	{fbvElement type="textarea" name="authorGuidelines[$formLocale]" id="authorGuidelines" value=$authorGuidelines[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
{/fbvFormSection}
{/fbvFormArea}

<h4>{translate key="manager.setup.submissionPreparationChecklist"}</h4>

<p>{translate key="manager.setup.submissionPreparationChecklistDescription"}</p>

{url|assign:submissionChecklistGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.submissionChecklist.SubmissionChecklistGridHandler" op="fetchGrid"}
{load_url_in_div id="submissionChecklistGridDiv" url=$submissionChecklistGridUrl}

<div class="separator"></div>

<h3>2.4 {translate key="manager.setup.peerReviewPolicy"}</h3>

<p>{translate key="manager.setup.peerReviewDescription"}</p>

{fbvFormArea id="peerReviewPolicy"}
	{fbvFormSection title="manager.setup.reviewPolicy"}
		{fbvElement type="textarea" name="reviewPolicy[$formLocale]" id="reviewPolicy" value=$reviewPolicy[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}
	{fbvFormSection title="manager.setup.reviewGuidelines"}
		{url|assign:"reviewFormsUrl" op="reviewForms"}
		<p>{translate key="manager.setup.reviewGuidelinesDescription" reviewFormsUrl=$reviewFormsUrl}</p>
		{fbvElement type="textarea" name="reviewGuidelines[$formLocale]" id="reviewGuidelines" value=$reviewGuidelines[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
	{/fbvFormSection}
	{fbvFormSection title="manager.setup.reviewProcess" layout=$fbvStyles.layout.ONE_COLUMN}
		<p>{translate key="manager.setup.reviewProcessDescription"}</p>
		{fbvElement type="radio" name="mailSubmissionsToReviewers" id="mailSubmissionsToReviewers-0" value="0" checked=!$mailSubmissionsToReviewers label="manager.setup.reviewProcessStandard"}
		{fbvElement type="radio" name="mailSubmissionsToReviewers" id="mailSubmissionsToReviewers-1" value="1" checked=$mailSubmissionsToReviewers label="manager.setup.reviewProcessEmail"}
	{/fbvFormSection}
{/fbvFormArea}

<h4>{translate key="manager.setup.reviewOptions"}</h4>

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
	<strong>{translate key="manager.setup.reviewOptions.reviewTime"}</strong><br/>
	{translate key="manager.setup.reviewOptions.numWeeksPerResponse"}: <input type="text" name="numWeeksPerResponse" id="numWeeksPerResponse" value="{$numWeeksPerResponse|escape}" size="2" maxlength="8" class="textField" /> {translate key="common.weeks"}<br/>
	{translate key="manager.setup.reviewOptions.numWeeksPerReview"}: <input type="text" name="numWeeksPerReview" id="numWeeksPerReview" value="{$numWeeksPerReview|escape}" size="2" maxlength="8" class="textField" /> {translate key="common.weeks"}<br/>
	{translate key="common.note"}: {translate key="manager.setup.reviewOptions.noteOnModification"}
</p>

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
	<p>{translate key="manager.setup.reviewOptions.reviewerAccessKeysEnabled.description"}</p><br />
	{fbvElement type="checkbox" id="restrictReviewerFileAccess" value="1" checked=$restrictReviewerFileAccess label="manager.setup.reviewOptions.restrictReviewerFileAccess"}
{/fbvFormSection}
{fbvFormSection title="manager.setup.reviewOptions.blindReview" layout=$fbvStyles.layout.ONE_COLUMN}
{fbvCustomElement}
	{fbvCheckbox id="showEnsuringLink" value="1" checked=$showEnsuringLink}
	{get_help_id|assign:"blindReviewHelpId" key="editorial.seriesEditorsRole.review.blindPeerReview" url="true"}
	<label for="showEnsuringLink" class="choice">{translate key="manager.setup.reviewOptions.showEnsuringLink" blindReviewHelpId=$blindReviewHelpId}</label><br/>
{/fbvCustomElement}
{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

<h3>2.5 {translate key="manager.setup.authorCopyrightNotice"}</h3>

{url|assign:"sampleCopyrightWordingUrl" page="information" op="sampleCopyrightWording"}
<p>{translate key="manager.setup.authorCopyrightNoticeDescription" sampleCopyrightWordingUrl=$sampleCopyrightWordingUrl}</p>

{fbvFormArea id="authorCopyrightNotice"}
{fbvFormSection}
	{fbvElement type="textarea" name="copyrightNotice[$formLocale]" id="copyrightNotice" value=$copyrightNotice[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
{/fbvFormSection}
{fbvFormSection layout=$fbvStyles.layout.TWO_COLUMNS}
	{fbvElement type="checkbox" id="includeCreativeCommons" value="1" checked=$includeCreativeCommons label="manager.setup.includeCreativeCommons"}
	{fbvElement type="checkbox" id="copyrightNoticeAgree" value="1" checked=$copyrightNoticeAgree label="manager.setup.authorCopyrightNoticeAgree"}
{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

<h3>2.6 {translate key="manager.setup.privacyStatement"}</h3>

{fbvFormArea id="privacyStatementContainer"}
{fbvFormSection}
	{fbvElement type="textarea" name="privacyStatement[$formLocale]" id="privacyStatement" value=$privacyStatement[$formLocale] size=$fbvStyles.size.MEDIUM measure=$fbvStyles.measure.3OF4}
{/fbvFormSection}
{/fbvFormArea}

<div class="separator"></div>

<p><input type="submit" value="{translate key="common.saveAndContinue"}" class="button defaultButton" /> <input type="button" value="{translate key="common.cancel"}" class="button" onclick="document.location.href='{url op="setup" escape=false}'" /></p>

<p><span class="formRequired">{translate key="common.requiredField"}</span></p>

</form>
</div>

{include file="common/footer.tpl"}
