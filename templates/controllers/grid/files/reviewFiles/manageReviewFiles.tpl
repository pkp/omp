<!-- templates/controllers/grid/files/reviewFiles/manageReviewFiles.tpl -->

{**
 * manageReviewFiles.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Allows editor to add more file to the review (that weren't added when the submission was sent to review)
 *}

{modal_title id="#existingFilesContainer" key='editor.submissionArchive.manageReviewFiles' iconClass="fileManagement" canClose=1}

<script type="text/javascript">
	$(function() {ldelim}
		$('.button').button();
	{rdelim});
</script>


<!-- Current review files -->
<h4>{translate key="editor.submissionArchive.currentFiles" round=$round}</h4>

<div id="existingFilesContainer">
	<form name="manageReviewFilesForm" id="manageReviewFilesForm" action="{url component="grid.files.reviewFiles.EditorReviewFilesGridHandler" op="updateReviewFiles" monographId=$monographId|escape reviewType=$reviewType|escape round=$round|escape}" method="post">
		<input type="hidden" name="monographId" value="{$monographId|escape}" />

		<!-- Available submission files -->
		{url|assign:availableReviewFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.reviewFiles.EditorReviewFilesGridHandler" stageId=$smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW op="fetchGrid" isSelectable=1 canUpload=1 monographId=$monographId reviewType=$reviewType round=$round escape=false}
		{load_url_in_div id="availableReviewFilesGrid" url=$availableReviewFilesGridUrl}
	</form>
</div>

<!-- / templates/controllers/grid/files/reviewFiles/manageReviewFiles.tpl -->

