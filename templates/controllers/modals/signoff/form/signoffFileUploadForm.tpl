{**
 * templates/controllers/modals/signoff/form/
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 *}


<script type="text/javascript">
	$(function() {ldelim}
		// Attach the upload form handler.
		$('#uploadForm').pkpHandler(
			'$.pkp.controllers.form.FileUploadFormHandler',
			{ldelim}
				$uploader: $('#plupload'),
				uploaderOptions: {ldelim}
					uploadUrl: '{url|escape:javascript op="uploadFile" submissionId=$submissionId stageId=$stageId escape=false}',
					baseUrl: '{$baseUrl|escape:javascript}'
				{rdelim}
			{rdelim});
	{rdelim});
</script>

<form class="pkp_form" id="uploadForm" action="{url op="signoff"}" method="post">
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />

	{** Make sure there is at least one available signoff *}
	{if $signoffId || count($availableSignoffs) gt 0}
		{fbvFormArea id="signoff"}
			<input type="hidden" name="symbolic" value="{$symbolic|escape}" />

			{fbvFormSection title="submission.signoff.signoffOnFile"}
			{if $signoffId}
				<input type="hidden" name="signoffId" value="{$signoffId|escape}" />
				{fbvElement type="text" id="signoffOnFile" disabled=true value=$signoffFileName}
			{else}
				{fbvElement type="select" id="signoffId" from=$availableSignoffs translate=false}
			{/if}
			{/fbvFormSection}

			{fbvFormSection title="common.note"}
				{fbvElement type="textarea" id="newNote" size=$fbvStyles.size.MEDIUM}<br/>
			{/fbvFormSection}

			<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />
			{fbvFormSection title="submission.submit.selectFile" required=1}
				{* The uploader widget *}
				{include file="controllers/fileUploadContainer.tpl" id="plupload"}
			{/fbvFormSection}
			{fbvFormButtons}
		{/fbvFormArea}
	{else}
		{* Put a marker in place so the form just closes with no attempt to validate *}
		<input type="hidden" name="noSignoffs" value="1" />
		{translate key="submission.signoff.noAvailableSignoffs"}
		{fbvFormButtons id="closeButton" hideCancel=true submitText="common.close"}
	{/if}
</form>
<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
