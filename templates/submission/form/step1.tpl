{**
 * templates/submission/form/step1.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 1 of author monograph submission.
 *}
{assign var="pageTitle" value="submission.submit"}
{include file="submission/form/submitStepHeader.tpl"}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#submitStep1Form').pkpHandler('$.pkp.controllers.form.AjaxFormHandler', {ldelim}
			baseUrl: '{$baseUrl|escape:"javascript"}'
		{rdelim});
	{rdelim});
</script>

<form class="pkp_form" id="submitStep1Form" method="post" action="{url op="saveStep" path=$submitStep}">
{if $monographId}<input type="hidden" name="monographId" value="{$monographId|escape}"/>{/if}
	<input type="hidden" name="submissionChecklist" value="1"/>

{include file="controllers/notification/inPlaceNotification.tpl" notificationId="submitStep1FormNotification"}

{fbvFormArea id="submissionStep1"}
	<!-- Author user group selection (only appears if user has > 1 author user groups) -->
	{if count($authorUserGroupOptions) > 1}
		{fbvFormSection title="submission.submitterUserGroup" inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" id="authorUserGroupId" from=$authorUserGroupOptions translate=false}
		{/fbvFormSection}
	{else}
		{foreach from=$authorUserGroupOptions key="key" item="authorUserGroupName"}{assign var=authorUserGroupId value=$key}{/foreach}
		{fbvElement type="hidden" id="authorUserGroupId" value=$authorUserGroupId}
	{/if}

	<!-- Submission Type -->
	{fbvFormSection list="true" label="submission.workType" description="submission.workType.description"}
		{fbvElement type="radio" name="isEditedVolume" id="isEditedVolume-0" value="1" checked=$isEditedVolume label="submission.workType.editedVolume"}
		{if $isEditedVolume}{assign var=notIsEditedVolume value=0}{else}{assign var=notIsEditedVolume value=1}{/if}
		{fbvElement type="radio" name="isEditedVolume" id="isEditedVolume-1" value="0" checked=$notIsEditedVolume label="submission.workType.authoredWork"}
	{/fbvFormSection}

	{if count($supportedSubmissionLocaleNames) == 1}
	{* There is only one supported submission locale; choose it invisibly *}
		{foreach from=$supportedSubmissionLocaleNames item=localeName key=locale}
			{fbvElement type="hidden" id="locale" value=$locale|escape}
		{/foreach}
		{else}
	{* There are several submission locales available; allow choice *}
		{fbvFormSection title="submission.submit.submissionLocale" inline=true size=$fbvStyles.size.MEDIUM for="locale"}
			{fbvElement label="submission.submit.submissionLocaleDescription" required="true" type="select" id="locale" from=$supportedSubmissionLocaleNames selected=$locale translate=false}
		{/fbvFormSection}
	{/if}{* count($supportedSubmissionLocaleNames) == 1 *}

	<!-- Submission Placement -->
	{if count($seriesOptions) > 1} {* only display the series picker if there are series configured for this press *}
		{fbvFormSection title="submission.submit.placement" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="select" id="seriesId" from=$seriesOptions selected=$seriesId translate=false}
		{/fbvFormSection}
	
		{fbvFormSection title="submission.submit.seriesPosition" size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" id="seriesPosition" name="seriesPosition" value=$seriesPosition|escape maxlength="255"}
		{/fbvFormSection}
	{/if}

	{fbvFormSection size=$fbvStyles.size.MEDIUM}
		<div id="monographCategoriesContainer">
			{url|assign:monographCategoriesUrl router=$smarty.const.ROUTE_COMPONENT component="submission.CategoriesListbuilderHandler" op="fetch" monographId=$monographId}
			{load_url_in_div id="monographCategoriesContainer" url=$monographCategoriesUrl}
		</div>
	{/fbvFormSection}

	<!-- Submission checklist -->
	{if $currentPress->getLocalizedSetting('submissionChecklist')}
		{fbvFormSection list="true" label="submission.submit.submissionChecklist" description="submission.submit.submissionChecklistDescription"}
			{foreach name=checklist from=$currentPress->getLocalizedSetting('submissionChecklist') key=checklistId item=checklistItem}
				{if $checklistItem.content}
					{fbvElement type="checkbox" id="checklist-$checklistId" required=true value=1 label=$checklistItem.content translate=false checked=$monographId}
				{/if}
			{/foreach}
		{/fbvFormSection}
	{/if}

	<!-- Cover Note To Editor-->
	{fbvFormSection for="commentsToEditor" title="submission.submit.coverNote"}
		{fbvElement type="textarea" name="commentsToEditor" id="commentsToEditor" value=$commentsToEditor rich=true}
	{/fbvFormSection}

	<!-- Privacy Statement -->
	{fbvFormSection for="privacyStatement" title="submission.submit.privacyStatement"}
		{fbvElement type="textarea" name="privacyStatement" id="privacyStatement" disabled=true value=$currentPress->getLocalizedSetting('privacyStatement') rich=true}
	{/fbvFormSection}

	{if $submissionProgress > 1}
		{assign var="confirmCancelMessage" value="submission.submit.cancelSubmission"}
	{else}
		{assign var="confirmCancelMessage" value="submission.submit.cancelSubmissionStep1"}
	{/if}

	<!-- Buttons -->
	{fbvFormButtons id="step1Buttons" submitText="common.saveAndContinue" confirmCancel=$confirmCancelMessage}

{/fbvFormArea}

</form>
</div>
{include file="common/footer.tpl"}
