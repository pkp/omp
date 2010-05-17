{**
 * approveAndReview.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to accept the submission and send it for review
 *}

<form name="approveForm-{$monographId}" id="approveForm-{$monographId}" action="{url component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="saveApproveAndReview" monographId=$monographId|escape reviewType=$reviewType|escape round=$round|escape}" method="post">
	<h4>{translate key="editor.monograph.approve"}</h4>

	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="reviewType" value="{$reviewType|escape}" />
	<input type="hidden" name="round" value="{$round|escape}" />
	
	<p>{translate key="editor.monograph.selectFiles"}</p>
	{** FIXME: need to set escape=false due to bug 5265 *}
	{url|assign:reviewFileSelectionGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.ReviewFilesGridHandler" op="fetchGrid" isSelectable=1 monographId=$monographId reviewType=$reviewType round=$round escape=false}
	{load_url_in_div id="reviewFileSelection" url=$reviewFileSelectionGridUrl}
</form>