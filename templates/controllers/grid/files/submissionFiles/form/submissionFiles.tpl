{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files grid form

 *}

{assign var='uniqueId' value=""|uniqid}

{if $fileStage == $smarty.const.MONOGRAPH_FILE_COPYEDIT}
	{assign var=titleKey value="submission.submit.uploadCopyeditedVersion"}
{else}
	{if $isRevision}
		{assign var=titleKey value="submission.submit.uploadRevision"}
	{else}
		{assign var=titleKey value="submission.submit.uploadSubmissionFile"}
	{/if}
{/if}
{modal_title id="div#fileUploadTabs" key=$titleKey iconClass="fileManagement" canClose=1}

{init_tabs id="div#fileUploadTabs"}
<script type="text/javascript">
	{literal}
	$(function() {
		$(".ui-dialog-titlebar").remove(); // Make sure title bar is removed on successive calls to the same modal
		$('.fileUpload').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
	});
	{/literal}
</script>

<div id="fileUploadTabs" class="fileUpload ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-corner-top"><a href="{url op="displayFileForm" monographId=$monographId fileId=$fileId fileStage=$fileStage isRevision=$isRevision}">1. {translate key="submission.submit.upload"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url op="editMetadata" monographId=$monographId fileId=$fileId}">2. {translate key="submission.submit.metadata"}</a></li>
		{if !$fileId}<li class="ui-state-default ui-corner-top"><a href="{url op="finishFileSubmissions" monographId=$monographId fileId=$fileId}">3. {translate key="submission.submit.finishingUp"}</a></li>{/if}
	</ul>
</div>
