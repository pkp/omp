{**
 * fileUploadConfirmationForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * File revision confirmation form.
 *
 * Parameters:
 *   $monographId: The monograph for which a file has been uploaded.
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
			'$.pkp.controllers.files.submissionFiles.form.RevisionConfirmationHandler');
	{rdelim});
</script>

<form id="uploadConfirmationForm"
		action="{url op="confirmRevision" monographId=$monographId uploadedFileId=$uploadedFile->getFileId()}"
		method="post">
	{fbvFormArea id="file"}
		<div id="possibleRevision" class="possibleRevision response" style="display:none;">
			<div id="revisionWarningIcon" class="warning"></div>
			<div id="revisionWarningText">
				<h5>{translate key="submission.upload.possibleRevision"}</h5>
				{translate key="submission.upload.possibleRevisionDescription" revisedFileName=$revisedFileName}
				{fbvSelect name="revisedFileId" id="revisedFileId" from=$monographFileOptions selected=$revisedFileId translate=false} <br />
			</div>
		</div>
	{/fbvFormArea}
	<div class="separator"></div>
</form>
