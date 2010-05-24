{**
 * approveAndReview.tpl
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
//		$('#approveToReviewForm-{/literal}{$monographId}{literal}').parent().dialog('option', 'buttons', null);  // Clear out default modal buttons
//
//		$('#approveToReviewButton-{/literal}{$monographId}{literal}').click(function() {
//    		submitModalForm("#"+$("#approveForm-{/literal}{$monographId}{literal}").parent().attr('id'), 'remove', 'component-grid-submissions-presseditor-presseditorsubmissionslistgrid-row-{/literal}{$monographId}{literal}');
//		});
		var modalId = "#"+$("#approveToReviewForm-{/literal}{$monographId}{literal}").parent().attr('id');
		$(modalId).dialog( "option", "buttons", { "{/literal}{translate key="editor.monograph.acceptToReview"}{literal}": function() { 
				submitModalForm(modalId, 'remove', 'component-grid-submissions-presseditor-presseditorsubmissionslistgrid-row-{/literal}{$monographId}{literal}'); 
			} 
		});
	});
	{/literal}
</script>

<form name="approveToReviewForm-{$monographId}" id="approveToReviewForm-{$monographId}" action="{url component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="saveApproveAndReview" monographId=$monographId|escape reviewType=$reviewType|escape round=$round|escape}" method="post">
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="reviewType" value="{$reviewType|escape}" />
	<input type="hidden" name="round" value="{$round|escape}" />
	
	<p>{translate key="editor.monograph.selectFilesForReview"}</p>
	{** FIXME: need to set escape=false due to bug 5265 *}
	{url|assign:reviewFileSelectionGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.ReviewFilesGridHandler" op="fetchGrid" isSelectable=1 monographId=$monographId reviewType=$reviewType round=$round escape=false}
	{load_url_in_div id="reviewFileSelectionToReviewer" url=$reviewFileSelectionGridUrl}
</form>