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
				'$.pkp.controllers.WizardHandler',
				{ldelim}
					cancelButtonText: '{translate key="common.cancel"}',
					continueButtonText: '{translate key="common.continue"}',
					finishButtonText: '{translate key="common.finish"}'
				{rdelim});
	{rdelim});
</script>

<div id="fileUploadWizard">
	<ul>
		<li><a href="#fileUpload">1. {translate key="submission.submit.upload"}</a></li>
		<li><a href="#metadata">2. {translate key="submission.submit.metadata"}</a></li>
		<li><a href="#finishingUp">3. {translate key="submission.submit.finishingUp"}</a></li>
	</ul>

	{url|assign:fileUploadFormUrl op="displayFileUploadForm" monographId=$monographId revisedFileId=$revisedFileId}
	{load_url_in_div id="fileUpload" url=$fileUploadFormUrl}

	<div id="metadata"> </div>

	<div id="finishingUp"> </div>
</div>
