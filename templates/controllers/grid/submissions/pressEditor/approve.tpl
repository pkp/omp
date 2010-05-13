{**
 * approve.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to accept the submission and send it for review
 *}

<form name="approveForm-{$monographId}" id="approveForm-{$monographId}" action="{url component="grid.submissions.pressEditor.PressEditorSubmissionsListGridHandler" op="saveApprove" monographId=$monographId}" method="post">
	<h4>{translate key="editor.monograph.approve"}</h4>

	<p>{translate key="editor.monograph.selectFiles"}</p> 
	{url|assign:reviewFileSelectionGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.editorReviewFileSelection.EditorReviewFileSelectionGridHandler" op="fetchGrid" monographId=$monographId}
	{load_url_in_div id="reviewFileSelection" url=$reviewFileSelectionGridUrl}
	
	<br />
	{fbvFormSection title="common.personalMessage" for="personalMessage"}
		{fbvElement type="textarea" id="personalMessage" size=$fbvStyles.size.MEDIUM}<br/>
	{/fbvFormSection}
</form>