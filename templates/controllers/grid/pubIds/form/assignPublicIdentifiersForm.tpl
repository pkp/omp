{**
 * templates/controllers/grid/pubIds/form/assignPublicIdentifiersForm.tpl
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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
	<form class="pkp_form" id="assignPublicIdentifierForm" method="post" action="{url component="grid.catalogEntry.PublicationFormatGridHandler" op="setApproved" submissionId=$pubObject->getSubmissionId() representationId=$pubObject->getId() newApprovedState=$approval submissionVersion=$pubObject->getSubmissionVersion() confirmed=true escape=false}">
		{assign var=remoteObject value=$pubObject->getRemoteURL()}
{elseif $pubObject instanceof SubmissionFile}
	<form class="pkp_form" id="assignPublicIdentifierForm" method="post" action="{url component="grid.catalogEntry.PublicationFormatGridHandler" op="setProofFileCompletion" fileId=$pubObject->getFileId() revision=$pubObject->getRevision() submissionId=$pubObject->getSubmissionId() submissionVersion=$pubObject->getSubmissionVersion() approval=$approval confirmed=true escape=false}">
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
