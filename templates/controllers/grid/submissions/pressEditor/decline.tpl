{**
 * decline.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to decline the submission and send a message to the author
 *}
 <script type="text/javascript">
	{literal}
	$(function() {
//		$('.button').button();
//		$('#declineForm-{/literal}{$fileId}{literal}').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
//
//		$('#declineButton-{/literal}{$monographId}{literal}').click(function() {
//    		submitModalForm("#"+$("#declineForm-{/literal}{$monographId}{literal}").parent().attr('id'), 'GRID_ACTION_TYPE_REMOVE', 'component-grid-submissions-presseditor-presseditorsubmissionslistgrid-table');
//		});
		var modalId = "#"+$("#declineForm-{/literal}{$monographId}{literal}").parent().attr('id');
		$(modalId).dialog( "option", "buttons", { "{/literal}{translate key="editor.monograph.declineAndNotify"}{literal}": function() { 
				submitModalForm(modalId, 'remove', 'component-grid-submissions-presseditor-presseditorsubmissionslistgrid-row-{/literal}{$monographId}{literal}'); 
			} 
		});
		
	});
	{/literal}
</script>

<form name="declineForm-{$monographId}" id="declineForm-{$monographId}" action="{url component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="saveDecline" monographId=$monographId}" method="post">
	<h4>{translate key="editor.monograph.decline"}</h4>
	<br />
	<p>{translate key="editor.monograph.messageToAuthor"}:</p>
	{fbvElement type="textarea" id="personalMessage" value=$personalMessage size=$fbvStyles.size.SMALL measure=$fbvStyles.measure.3OF4}<br/>
	
	<!-- <div style="float:right;">{fbvButton type="submit" id="declineButton" label="editor.monograph.declineAndNotify" float=$fbvStyles.float.RIGHT}</div> -->
</form>
