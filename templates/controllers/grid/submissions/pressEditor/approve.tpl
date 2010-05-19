{**
 * approve.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to accept the submission and send it for review
 *}
<script type="text/javascript">
	{literal}
	$(function() {
//		$('.button').button();
//		$('#approveForm-{/literal}{$monographId}{literal}').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
//
//		$('#approveButton-{/literal}{$monographId}{literal}').click(function() {
//    		submitModalForm("#"+$("#approveForm-{/literal}{$monographId}{literal}").parent().attr('id'), 'remove', 'component-grid-submissions-presseditor-presseditorsubmissionslistgrid-row-{/literal}{$monographId}{literal}');
//		});
		var modalId = "#"+$("#approveForm-{/literal}{$monographId}{literal}").parent().attr('id');
		$(modalId).dialog( "option", "buttons", { "{/literal}{translate key="editor.monograph.acceptToEditorial"}{literal}": function() { 
				submitModalForm(modalId, 'remove', 'component-grid-submissions-presseditor-presseditorsubmissionslistgrid-row-{/literal}{$monographId}{literal}'); 
			} 
		});
	});
	{/literal}
</script>

<form name="approveForm-{$monographId}" id="approveForm-{$monographId}" action="{url component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="saveApprove" monographId=$monographId}" method="post">
	<h4>{translate key="editor.monograph.approve"}</h4>

	<p>{translate key="editor.monograph.selectFiles"}</p> 
	{url|assign:reviewFileSelectionGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.ReviewFilesGridHandler" op="fetchGrid" isSelectable=1 monographId=$monographId reviewType=$reviewType round=$round escape=false}
	{load_url_in_div id="reviewFileSelectionToEditor" url=$reviewFileSelectionGridUrl}
	
	<br />
	<p>{translate key="editor.monograph.messageToAuthor"}:</p>
	{fbvElement type="textarea" id="personalMessage" value=$personalMessage size=$fbvStyles.size.MEDIUM}<br/>

	<!-- <div style="float:right;">{fbvButton type="submit" id="approveButton-$monographId" label="editor.monograph.acceptToEditorial"}</div> -->
</form>
