{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files grid form
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// On newFile button click, return row then restart modal
		/*	$('#newFile').click(function() {
	   		saveAndUpdate('{/literal}{url op="fetchRow" monographId=$monographId fileId=$fileId escape=false}{literal}',
	   	    		'append',
	   	    		'#component-'+'{/literal}{$gridId}{literal}'+'-table',
	   	    		'div#fileUploadTabs', true, '#component-'+'{/literal}{$gridId}{literal}'+'-addFile-button');
	   	    return false;
		}); */
	{rdelim});
</script>

<div id="finishSubmissionForm" class="text_center">
		<h2>{translate key="submission.submit.fileAdded"}</h2>
		<br />
		<br />
		<button type="button" name="newFile" id="newFile">{translate key='submission.submit.newFile'}</button>
		<br />
		<br />
</div>
