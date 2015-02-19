{**
 * templates/controllers/modals/signoff/readSignoff.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the upload form handler.
		$('#signoffForm').pkpHandler(
			'$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="signoffForm" action="{url op="signoffRead"}" method="post">
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />

	{fbvFormArea id="signoff"}
		<input type="hidden" name="signoffId" value="{$signoffId|escape}" />

		{fbvFormSection title="submission.signoff.signedOffOnFile"}
			{fbvElement type="text" id="signoffOnFile" disabled=true value=$signoffFileName}
		{/fbvFormSection}

		{fbvFormSection title="common.note"}
			{fbvElement type="textarea" id="newNote" size=$fbvStyles.size.MEDIUM value=$noteText disabled=true}
		{/fbvFormSection}

		<br />
		{if $downloadSignoffResponseFileAction}
			<div class="pkp_linkActions">
				{translate key="submission.signoff.fileResponse"} {include file="linkAction/linkAction.tpl" action=$downloadSignoffResponseFileAction contextId="signoffForm"}
			</div>
		{/if}
		{fbvFormButtons id="closeButton" hideCancel=true submitText="common.close"}
	{/fbvFormArea}
</form>
