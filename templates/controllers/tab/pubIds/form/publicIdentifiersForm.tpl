{**
 * templates/controllers/tab/pubIds/form/publicIdentifiersForm.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @hook Templates::Controllers::Tab::PubIds::Form::PublicIdentifiersForm []
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#publicIdentifiersForm').pkpHandler(
			'$.pkp.controllers.form.AjaxFormHandler',
			{ldelim}
				trackFormChanges: true
			{rdelim}
		);
	{rdelim});
</script>
{if $pubObject instanceof \APP\submission\Submission}
	<form class="pkp_form" id="publicIdentifiersForm" method="post" action="{url router=PKP\core\PKPApplication::ROUTE_COMPONENT op="updateIdentifiers"}">
		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="publicationIdentifiersFormFieldsNotification"}
		<input type="hidden" name="submissionId" value="{$pubObject->getId()|escape}" />
		<input type="hidden" name="stageId" value="{$stageId|escape}" />
		<input type="hidden" name="tabPos" value="2" />
		<input type="hidden" name="displayedInContainer" value="{$formParams.displayedInContainer|escape}" />
		<input type="hidden" name="tab" value="identifiers" />

{elseif $pubObject instanceof \APP\monograph\Chapter}
	<form class="pkp_form" id="publicIdentifiersForm" method="post" action="{url router=PKP\core\PKPApplication::ROUTE_COMPONENT component="grid.users.chapter.ChapterGridHandler" op="updateIdentifiers"}">
		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="representationIdentifiersFormFieldsNotification"}
		<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
		<input type="hidden" name="publicationId" value="{$pubObject->getData('publicationId')|escape}" />
		<input type="hidden" name="chapterId" value="{$pubObject->getId()|escape}" />

{elseif $pubObject instanceof \PKP\submission\Representation}
	<form class="pkp_form" id="publicIdentifiersForm" method="post" action="{url router=PKP\core\PKPApplication::ROUTE_COMPONENT component="grid.catalogEntry.PublicationFormatGridHandler" op="updateIdentifiers"}">
		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="representationIdentifiersFormFieldsNotification"}
		<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
		<input type="hidden" name="publicationId" value="{$pubObject->getData('publicationId')|escape}" />
		<input type="hidden" name="representationId" value="{$pubObject->getId()|escape}" />

{elseif $pubObject instanceof \PKP\submissionFile\SubmissionFile}
	<form class="pkp_form" id="publicIdentifiersForm" method="post" action="{url component="api.file.ManageFileApiHandler" op="updateIdentifiers" escape=false}">
		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="fileIdentifiersFormFieldsNotification"}
		<input type="hidden" name="submissionFileId" value="{$pubObject->getId()|escape}" />
		<input type="hidden" name="submissionId" value="{$pubObject->getData('submissionId')|escape}" />
		<input type="hidden" name="stageId" value="{$stageId|escape}" />
		<input type="hidden" name="fileStageId" value="{$pubObject->getData('submissionId')|escape}" />

{/if}

{csrf}

{if $enablePublisherId}
	{fbvFormSection}
		{fbvElement type="text" label="submission.publisherId" id="publisherId" name="publisherId" value=$publisherId size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}
{/if}

{foreach from=$pubIdPlugins item=pubIdPlugin}
	{assign var=pubIdMetadataFile value=$pubIdPlugin->getPubIdMetadataFile()}
	{assign var=canBeAssigned value=$pubIdPlugin->canBeAssigned($pubObject)}
	{include file="$pubIdMetadataFile" pubObject=$pubObject canBeAssigned=$canBeAssigned}
{/foreach}
{call_hook name="Templates::Controllers::Tab::PubIds::Form::PublicIdentifiersForm"}
{fbvFormButtons id="publicIdentifiersFormSubmit" submitText="common.save"}

</form>
