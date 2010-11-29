{**
 * submissionFiles.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * A tabbed interface to add files or revisions of files.
 *}

{if $isRevision}
	{assign var=titleKey value="submission.submit.uploadRevision"}
{else}
	{assign var=titleKey value="submission.submit.uploadSubmissionFile"}
{/if}
{modal_title id="div#fileUploadTabs" key=$titleKey iconClass="fileManagement" canClose=1}

{init_tabs id="div#fileUploadTabs"}
<script type="text/javascript">{literal}
	$(function() {
		// Make sure title bar is removed on successive calls to the same modal.
		$(".ui-dialog-titlebar").remove();

		// Clear out default modal buttons.
		$('.fileUpload').parent().dialog('option', 'buttons', null);
	});
{/literal}</script>

<div id="fileUploadTabs" class="fileUpload ui-tabs ui-widget ui-widget-content ui-corner-all">
	<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<li class="ui-state-default ui-corner-top"><a href="{url op="displayFileForm" monographId=$monographId"}">1. {translate key="submission.submit.upload"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url op="editMetadata" monographId=$monographId fileId=$fileId}">2. {translate key="submission.submit.metadata"}</a></li>
		<li class="ui-state-default ui-corner-top"><a href="{url op="finishFileSubmissions" monographId=$monographId fileId=$fileId}">3. {translate key="submission.submit.finishingUp"}</a></li>
	</ul>
</div>
