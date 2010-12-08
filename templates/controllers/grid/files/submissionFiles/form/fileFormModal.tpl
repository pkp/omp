{**
 * fileFormModal.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * A wizard to add files or revisions of files.
 *
 * This is a wrapper with the modal frame and shared JS
 * around fileForm.tpl which provides the actual upload
 * wizard.
 *
 * Parameters:
 *  $revisionOnly
 *  $monographId
 *  $revisedFileId
 *}

{* Initialize the file upload wizard. *}
{if $revisionOnly}
	{assign var=titleKey value="submission.submit.uploadRevision"}
{else}
	{assign var=titleKey value="submission.submit.uploadSubmissionFile"}
{/if}
{modal_title id="div#fileUploadModal" key=$titleKey iconClass="fileManagement" canClose=1}

<script type="text/javascript">
	<!--
	$(function() {ldelim}
		// Clear out default modal buttons.
		$('div#fileUploadModal').parent().dialog('option', 'buttons', null);
	{rdelim});
	// -->
{/literal}</script>

{url|assign:uploadWizardUrl op="displayFileForm" monographId=$monographId revisedFileId=$revisedFileId}
{load_url_in_div id="fileUploadModal" url=$uploadWizardUrl}
