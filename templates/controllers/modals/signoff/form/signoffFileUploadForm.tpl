{**
 * signoffFileUploadForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 *}


<script type="text/javascript">
	$(function() {ldelim}
		// Attach the upload form handler.
		$('#uploadForm').pkpHandler(
			'$.pkp.controllers.form.FileUploadFormHandler',
			{ldelim}
				$uploader: $('#uploadForm #plupload'),
				uploaderOptions: {ldelim}
					uploadUrl: '{url|escape:javascript op="uploadFile" monographId=$monographId stageId=$stageId escape=false}',
					baseUrl: '{$baseUrl|escape:javascript}'
				{rdelim}
			{rdelim});
	{rdelim});
</script>

<form class="pkp_form" id="uploadForm" action="{url op="signoff"}" method="post">
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />

	{** Make sure there is at least one available signoff *}
	{if $signoffId || count($availableSignoffs) gt 0}
		{fbvFormArea id="signoff"}
			<input type="hidden" name="symbolic" value="{$symbolic|escape}" />

			{if $signoffId}
				<input type="hidden" name="signoffId" value="{$signoffId|escape}" />
				{translate key="submission.signoff.signoffOnFile"}: <br />
				<div id="{$downloadFileAction->getId()}">
					{include file="linkAction/linkAction.tpl" action=$downloadFileAction contextId="uploadForm"}
				</div>
			{/if}

			<input type="hidden" name="temporaryFileId" id="temporaryFileId" value="" />

			{fbvFormSection title="common.note"}
				{fbvElement type="textarea" id="note" height=$fbvStyles.height.SHORT disabled=$signoffReadOnly}
			{/fbvFormSection}

			{if !$signoffReadOnly}
				{fbvFormSection title="submission.submit.selectFile" required=1}
					{* The uploader widget *}
					<div id="plupload"></div>
				{/fbvFormSection}
				{fbvFormButtons}
			{else}
				{include file="linkAction/linkAction.tpl" action=$downloadSignoffFileAction contextId="uploadForm"}
				{fbvFormButtons id="closeButton" hideCancel=true submitText="common.close"}
			{/if}
		{/fbvFormArea}
	{else}
		{** Put a marker in place so the form just closes with no attempt to validate **}
		<input type="hidden" name="noSignoffs" value="1" />
		{translate key="submission.signoff.noAvailableSignoffs"}
		{fbvFormButtons id="closeButton" hideCancel=true submitText="common.close"}
	{/if}
</form>
