{**
 * fileUploadConfirmationForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * File revision confirmation form.
 *
 * Parameters:
 *   $monographId: The monograph for which a file has been uploaded.
 *   $stageId: The workflow stage in which the file uploader was called.
 *   $uploadedFile: The MonographFile object of the uploaded file.
 *   $revisedFileId: The id of the potential revision.
 *   $revisedFileName: The name of the potential revision.
 *   $monographFileOptions: A list of monograph files that can be
 *    revised.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the revision confirmation handler.
		$('#uploadConfirmationForm').pkpHandler(
			'$.pkp.controllers.wizard.fileUpload.form.RevisionConfirmationHandler');
	{rdelim});
</script>

<form class="pkp_form pkp_controllers_grid_files" id="uploadConfirmationForm"
		action="{url op="confirmRevision" monographId=$monographId stageId=$stageId fileStage=$fileStage uploadedFileId=$uploadedFile->getFileId()}"
		method="post">
	{fbvFormArea id="file"}
		<div id="possibleRevision" class="pkp_controllers_grid_files_possibleRevision" style="display:none;">
			<div id="revisionWarningIcon" class="pkp_controllers_grid_files_warning"></div>
			<div id="revisionWarningText">
				<h5>{translate key="submission.upload.possibleRevision"}</h5>
				{translate key="submission.upload.possibleRevisionDescription" revisedFileName=$revisedFileName}
				{fbvElement type="select" name="revisedFileId" id="revisedFileId" from=$monographFileOptions selected=$revisedFileId translate=false} <br />
			</div>
		</div>
	{/fbvFormArea}
	<div class="separator"></div>
</form>
