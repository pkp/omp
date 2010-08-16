<!-- templates/controllers/grid/files/submissionFiles/form/fileSubmissionComplete.tpl -->

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
    		saveAndUpdate('{/literal}{url op="returnFileRow" monographId=$monographId fileId=$fileId escape=false}{literal}',
    	    		'append',
    	    		'#component-'+'{/literal}{$gridId}{literal}'+'-table',
    	    		'div#fileUploadTabs', true);
		});

		// On exit button click, return row and close modal
		$('#exit').click(function() {
    		saveAndUpdate('{/literal}{url op="returnFileRow" monographId=$monographId fileId=$fileId escape=false}{literal}',
    	    		'append',
    	    		'#component-'+'{/literal}{$gridId}{literal}'+'-table',
    	    		'div#fileUploadTabs');
		});
	});
	{/literal}
</script>

<div class="text_center">
	<h2>{translate key="submission.submit.fileAdded"}</h2> <br /> <br /> <br />
	<form name="finishSubmissionForm" id="finishSubmissionForm" action="{url router=$smarty.const.ROUTE_COMPONENT op="returnFileRow" monographId=$monographId fileId=$fileId escape=false}" method="post">
		<input class="button" type="button" name="newFile" value="{translate key='submission.submit.newFile'}" id="newFile" /> <br /> <br /> <br />
		<input class="button" type="button" name="exit" value="{translate key='submission.submit.finishedUploading'}" id="exit" /> <br />
	</form>
</div>
<!-- / templates/controllers/grid/files/submissionFiles/form/fileSubmissionComplete.tpl -->

