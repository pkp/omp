{**
 * templates/controllers/grid/pubIds/form/assignPublicIdentifiersForm.tpl
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 *}
 <script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#assignPublicIdentifierForm').pkpHandler(
			'$.pkp.controllers.form.AjaxFormHandler',
			{ldelim}
				trackFormChanges: true
			{rdelim}
		);
	{rdelim});
</script>
{if $pubObject instanceof Representation}
	<form class="pkp_form" id="assignPublicIdentifierForm" method="post" action="{url component="grid.catalogEntry.PublicationFormatGridHandler" op="setApproved" submissionId=$submissionId publicationId=$pubObject->getData('publicationId') representationId=$pubObject->getId() newApprovedState=$approval confirmed=true escape=false}">
		{assign var=remoteObject value=$pubObject->getData('urlRemote')}
{elseif $pubObject instanceof SubmissionFile}
	<form class="pkp_form" id="assignPublicIdentifierForm" method="post" action="{url component="grid.catalogEntry.PublicationFormatGridHandler" op="setProofFileCompletion" submissionFileId=$pubObject->getId() submissionId=$pubObject->getData('submissionId') publicationId=$publicationId approval=$approval confirmed=true escape=false}">
{/if}
{csrf}
{fbvFormArea id="confirmationText"}
	<p>{$confirmationText}</p>
{/fbvFormArea}
{if $approval}
	{if !$remoteObject}
		{foreach from=$pubIdPlugins item=pubIdPlugin}
			{assign var=pubIdAssignFile value=$pubIdPlugin->getPubIdAssignFile()}
			{assign var=canBeAssigned value=$pubIdPlugin->canBeAssigned($pubObject)}
			{include file="$pubIdAssignFile" pubIdPlugin=$pubIdPlugin pubObject=$pubObject canBeAssigned=$canBeAssigned}
		{/foreach}
	{/if}
{/if}
{fbvFormButtons id="assignPublicIdentifierForm" submitText="common.ok"}
</form>
