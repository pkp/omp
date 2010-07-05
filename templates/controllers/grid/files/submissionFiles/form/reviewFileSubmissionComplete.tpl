{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files grid form
 *
 * $Id$
 *}
<script type="text/javascript">
	{literal}
	$(function() {
		// On newFile button click, return row then restart modal
		$('.button').button();
		$('#newFile').click(function() {
    		saveAndUpdate('{/literal}{url component="grid.files.submissionFiles.SubmissionReviewFilesGridHandler" op="returnFileRow" fileId=$fileId isSelectable=1}{literal}',
    	    		'append',
    	    		'component-reviewFilesSelect-table > tbody:first',
    	    		'#fileUploadTabs-{/literal}{$fileId}{literal}', true);
		});

		// On exit button click, return row and close modal
		$('#exit').click(function() {
    		saveAndUpdate('{/literal}{url component="grid.files.submissionFiles.SubmissionReviewFilesGridHandler" op="returnFileRow" fileId=$fileId isSelectable=1}{literal}',
    	    		'append',
    	    		'component-reviewFilesSelect-table > tbody:first',
    	    		'#fileUploadTabs-{/literal}{$fileId}{literal}');
		});
	});
	{/literal}
</script>

<div class="text_center">
	<h2>{translate key="author.submit.fileAdded"}</h2> <br /> <br /> <br />
	<form name="finishSubmissionForm" id="finishSubmissionForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.files.submissionFiles.SubmissionReviewFilesGridHandler" op="returnFileRow" fileId=$fileId}" method="post">
		<input class="button" type="button" name="newFile" value="{translate key='author.submit.newFile'}" id="newFile" /> <br /> <br /> <br />
		<input class="button" type="button" name="exit" value="{translate key='author.submit.finishedUploading'}" id="exit" /> <br />
	</form>
</div>

{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
{if $fileId}
	<input type="hidden" name="fileId" value="{$fileId|escape}" />
{/if}
<br />
