{**
 * fileUploadWizard.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * A wizard to add files or revisions of files.
 *
 * Parameters:
 *   $monographId: The monograph to which files should be uploaded.
 *   $revisedFileId: A pre-selected file to be revised (optional).
 *}

<script type="text/javascript">
	// Attach the JS file upload wizard handler.
	$(function() {ldelim}
		$('#fileUploadWizard').pkpHandler(
				'$.pkp.controllers.files.submissionFiles.FileUploadWizardHandler',
				{ldelim}
					cancelButtonText: '{translate|escape:javascript key="common.cancel"}',
					continueButtonText: '{translate|escape:javascript key="common.continue"}',
					finishButtonText: '{translate|escape:javascript key="common.finish"}',
					deleteUrl: '{url|escape:javascript op="deleteFile" monographId=$monographId escape=false}',
					metadataUrl: '{url|escape:javascript op="editMetadata" monographId=$monographId}'
				{rdelim});
	{rdelim});
</script>

<div id="fileUploadWizard">
	<ul>
		<li><a href="{url op="displayFileUploadForm" monographId=$monographId revisedFileId=$revisedFileId}">1. {translate key="submission.submit.upload"}</a></li>
		<li><a href="#editMetadata">2. {translate key="submission.submit.metadata"}</a></li>
		<li><a href="#finishingUp">3. {translate key="submission.submit.finishingUp"}</a></li>
	</ul>

	<div id="finishingUp"> </div>
</div>
