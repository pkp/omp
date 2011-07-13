{**
 * copyeditingFileForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Copyediting Files grid form -- Allow users to upload files to their copyediting responses
 *}

{modal_title id="#uploadForm" key='submission.addFile' iconClass="fileManagement"}

<script type="text/javascript">
	<!--
	{literal}
	$(function() {
		// Handle upload form
		$('#uploadForm').ajaxForm({
			url: '{/literal}{url op="uploadCopyeditedFile" monographId=$monographId signoffId=$signoffId escape=false}{literal}',
			target: '#uploadOutput',  // target identifies the element(s) to update with the server response
			iframe: true,
			dataType: 'json',
			beforeSubmit: function() {
				$('#loading').show();
				$('#loadingText').fadeIn('slow');
			},
			// success identifies the function to invoke when the server response
			// has been received; here we show a success message and enable the next tab
			success: function(returnString) {
				$('#loading').hide();
				if (returnString.status == true) {
					$('#copyeditingFile').attr("disabled", "disabled");
					$('#copyeditingFileSubmit').button("option", "disabled", true);
					$("#submitModalButton").button( "option", "disabled", false);
					$('#deleteUrl').val(returnString.deleteUrl);
					$("#metadataRowId").val(returnString.elementId);
				}
				$('#loadingText').text(returnString.content);  // Set to error or success message
			}
		});
		// Handle metadata form
		$('#metadataForm').ajaxForm({
			dataType: 'json',
			success: function(returnString) {
				if (returnString.status == true) {
					newFile = $('#newFile').val();
					if(newFile != undefined && newFile != "") {
						actType = 'append';
					} else {
						actType = 'replace';
					}
					updateItem(actType, '#component-'+'{/literal}{$gridId}{literal}'+'-table>tbody:first', returnString.content);
					$('#uploadForm').parent().dialog('close');
				}
				$('#loadingText').text(returnString.content);  // Set to error or success message
			}
		});

		$("#cancelModalButton").click(function() {
			// User has uploaded a file then pressed cancel--delete the file
			newFile = $('#newFile').val();
			deleteUrl = $('#deleteUrl').val();
			if(deleteUrl != undefined && newFile != undefined && deleteUrl != "" && newFile != "") {
				$.post(deleteUrl);
			}

			$('#uploadForm').parent().dialog('close');
			return false;
		});

	});
	{/literal}
	// -->
</script>


<form class="pkp_form" id="uploadForm" action="{url op="returnSignoffRow" monographId=$monographId signoffId=$signoffId escape=false}" method="post">
	{fbvFormArea id="file"}
		{if !$copyeditingFile}
			{fbvFormSection title="common.file"}
				<input type="file" id="copyeditingFile" name="copyeditingFile" />
				<input type="submit" id="copyeditingFileSubmit" name="submitFile" value="{translate key="common.upload"}" class="button" />
			{/fbvFormSection}
		{else}
			{fbvFormSection title="common.file"}
				{include file="controllers/grid/settings/library/form/fileInfo.tpl"}
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}
	<div id="uploadOutput">
		<div id='loading' class='pkp_controllers_grid_throbber' style="margin: 0px;"></div>
		<ul><li id='loadingText' style='display:none;'>{translate key='submission.loadMessage'}</li></ul>
	</div>

	<input type="hidden" id="deleteUrl" value="" />
	<input type="hidden" id="signoffId" value="" />
	<input type="hidden" id="newFile" value="{$newFile}" />

	{fbvFormButtons}
</form>


