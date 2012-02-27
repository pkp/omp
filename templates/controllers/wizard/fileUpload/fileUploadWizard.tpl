{**
 * templates/controllers/wizard/fileUpload/fileUploadWizard.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * A wizard to add files or revisions of files.
 *
 * Parameters:
 *   $monographId: The monograph to which files should be uploaded.
 *   $stageId: The workflow stage from which the wizard was called.
 *   $revisedFileId: A pre-selected file to be revised (optional).
 *}

<script type="text/javascript">
	// Attach the JS file upload wizard handler.
	$(function() {ldelim}
		$('#fileUploadWizard').pkpHandler(
				'$.pkp.controllers.wizard.fileUpload.FileUploadWizardHandler',
				{ldelim}
					cancelButtonText: '{translate|escape:javascript key="common.cancel"}',
					continueButtonText: '{translate|escape:javascript key="common.continue"}',
					finishButtonText: '{translate|escape:javascript key="common.finish"}',
					deleteUrl: '{url|escape:javascript component="api.file.ManageFileApiHandler" op="deleteFile" monographId=$monographId stageId=$stageId fileStage=$fileStage escape=false}',
					metadataUrl: '{url|escape:javascript op="editMetadata" monographId=$monographId stageId=$stageId reviewRoundId=$reviewRoundId fileStage=$fileStage escape=false}',
					finishUrl: '{url|escape:javascript op="finishFileSubmission" monographId=$monographId stageId=$stageId reviewRoundId=$reviewRoundId fileStage=$fileStage escape=false}'
				{rdelim});
	{rdelim});
</script>

<div id="fileUploadWizard">
	<ul>
		<li><a href="{url op="displayFileUploadForm" monographId=$monographId stageId=$stageId uploaderRoles=$uploaderRoles fileStage=$fileStage revisionOnly=$revisionOnly reviewRoundId=$reviewRoundId revisedFileId=$revisedFileId assocType=$assocType assocId=$assocId}">{translate key="submission.submit.uploadStep"}</a></li>
		<li><a href="metadata">{translate key="submission.submit.metadataStep"}</a></li>
		<li><a href="finish">{translate key="submission.submit.finishingUpStep"}</a></li>
	</ul>
</div>
