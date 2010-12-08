{**
 * fileForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files grid form.
 *
 * Parameters:
 *   $monographId: The monograph for which a file is being uploded.
 *   $uploadedFile: As soon as a file has been uploaded, this will
 *    be the MonographFile object of the uploaded file, otherwise empty.
 *   $revisionOnly: Whether the user can upload new files or not.
 *   $revisedFileId: The id of the file to be revised (optional).
 *    When set to a number then the user may not choose the file
 *    to be revised.
 *   $revisedFileName: The name of the file to be revised (if any).
 *   $genreId: The preset genre of the file to be uploaded (optional).
 *   $monographFileOptions: A list of monograph files that can be
 *    revised.
 *   $currentMonographFileGenres: An array that assignes genres to the monograph
 *    files that can be revised.
 *   $monographFileGenres: A list of all available monograph file genres.
 *
 * This form implements several states:
 *
 * 1) Uploading of a revision to an existing file with the
 *    file to be revised already known:
 *    - $uploadedFile is empty.
 *    - $revisionOnly is true.
 *    - $revisedFileId is set to a number.
 *    - $monographFileOptions will be ignored.
 *    -> No file selector will be shown.
 *    -> A file genre cannot be set.
 *
 * 2) Uploading of a revision to an existing file where the
 *    file to be revised must still be selected by the user.
 *    - $uploadedFile is empty.
 *    - $revisionOnly is true.
 *    - $revisedFileId is not set to a number.
 *    - $monographFileOptions must not be empty.
 *    -> A selector with files that can be revised will
 *       be shown. Selection of a revised file is mandatory.
 *       If a revised file id is given then that file will
 *       be pre-selected.
 *    -> A file genre cannot be set.
 *
 * 3) Uploading of a file that may or may not be a revision
 *    of an existing file (free upload).
 *    - $uploadedFile is empty.
 *    - $revisionOnly is false.
 *    - $revisedFileId does not have to be a number.
 *    - $monographFileOptions is not empty.
 *    -> A selector with files that can be revised will
 *       be shown. Selection of a revised file is optional.
 *       If the revised file id is set then this file will
 *       be pre-selected in the drop-down.
 *    -> A file genre selector will be shown but will be
 *       deactivated as soon as the user selects a file
 *       to be revised. Otherwise selection of a genre is
 *       mandatory.
 *    -> Uploaded files will be checked against existing
 *       files to identify possible revisions.
 *
 * 4) Uploading of a new file when no previous files
 *    exist at all at this workflow stage.
 *    - $uploadedFile is empty.
 *    - $revisionOnly is false.
 *    - $revisedFileId must not be a number.
 *    - $monographFileOptions is empty.
 *    -> No file selector will be shown.
 *    -> A file genre selector will be shown. Selection of
 *       a genre is mandatory.
 *
 * 5) Confirmation of a possible revision.
 *    - $uploadedFile contains a MonographFile object.
 *    - $revisionOnly will be ignored.
 *    - $revisedFileId is a number and different from the
 *      uploaded file's id.
 *    - $monographFileOptions must be an array.
 *    -> No file selector will be shown.
 *    -> No file genre selector will be shown.
 *    -> No file uploader will be shown.
 *    -> The "possible revision" control will be shown.
 *
 * 6) Metadata editing and upload finished.
 *    - $uploadedFile contains a MonographFile object.
 *    - $revisionOnly will be ignored.
 *    - $revisedFileId is either not a number or equal
 *      to the uploaded file's id.
 *    - $monographFileOptions will be ignored.
 *    -> No file selector will be shown.
 *    -> No file genre selector will be shown.
 *    -> No file uploader will be shown.
 *    -> The "possible revision" control will be shown.
 *
 * The following decision tree shows the input parameters
 * and the corresponding use cases (UF: $uploadedFile, RO:
 * $revisionOnly, RF: $revisedFileId, FO: $monographFileOptions,
 * y=given, n=not given, o=any/ignored):
 *
 *   UF  RO  RF  FO
 *   n   y   y   o  -> 1)
 *   |   |   n   y  -> 2)
 *   |   |   |   n  -> not allowed
 *
 *           FO  RF
 *   |   n   y   o  -> 3)
 *   |   |   n   y  -> not allowed
 *   |   |   |   n  -> 4)
 *
 *           FO  RF*
 *   y   o   y   y  -> 5)
 *   |   |   n   y  -> not allowed
 *   |   |   o   n  -> 6)
 *               *) and not equal to uploaded file id
 *}

{* Implement the above decision tree and configure the form based on the identified use case. *}
{assign var="showFileNameOnly" value=false}
{assign var="showPossibleRevision" value=false}
{assign var="metadataEditing" value=false}
{if is_a($uploadedFile, 'MonographFile')}
	{if is_numeric($revisedFileId) && $revisedFileId != $uploadedFile->getFileId()}
		{* Use case 5: Confirmation of a possible revision *}
		{if empty($monographFileOptions)}{$"File list may not be empty when showing a possible revision!":fatalError}{/if}
		{assign var="showPossibleRevision" value=true}
	{else}
		{* Use case 6: Metadata editing and upload finished *}
		{assign var="metadataEditing" value=true}
	{/if}
{else}
	{if $revisionOnly}
		{assign var="showGenreSelector" value=false}
		{if is_numeric($revisedFileId)}
			{* Use case 1: Revision of a known file *}
			{assign var="showFileSelector" value=false}
			{assign var="showFileNameOnly" value=true}
		{else}
			{* Use case 2: Revision of a file which still must be chosen *}
			{if empty($monographFileOptions)}{$"File list may not be empty when choosing a revision is mandatory!":fatalError}{/if}
			{assign var="showFileSelector" value=true}
		{/if}
	{else}
		{assign var="showGenreSelector" value=true}
		{if empty($monographFileOptions)}
			{* Use case 4: Upload a new file *}
			{if is_numeric($revisedFileId)}{$"A revised file id cannot be given when uploading a new file!":fatalError}{/if}
			{assign var="showFileSelector" value=false}
		{else}
			{* Use case 3: Upload a new file or a revision *}
			{assign var="showFileSelector" value=true}
		{/if}
	{/if}
{/if}

{init_tabs id="div#fileUploadTabs"}

<div id="fileUploadTabs">
	<script type="text/javascript">
		<!--
		{if $metadataEditing}
			$(function() {ldelim}
				// Open the meta-data tab and disable the upload tab.
				$('div#fileUploadTabs').last().tabs('select', 1);
				$('div#fileUploadTabs').last().tabs('option', 'disabled', [0]);
			{rdelim});
		{else}
			{if $showPossibleRevision}{literal}
				$(function() {
					// Disable finish tab while choosing a revision.
					$('div#fileUploadTabs').last().tabs('option', 'disabled', [2]);

					// Show the possible revision message.
					$('#uploadForm #possibleRevision').show('slide');

					// Set the "continue button" behavior.
					$("#continueButton").click(function() {
						revisedFileId = $('#revisedFileId').val();
						if (revisedFileId) {
							$form = $('#uploadForm');
							$.post($form.attr('action'), $form.serialize(), function(jsonData) {
								if (jsonData.status === true) {
									// Replace the upload modal content.
									$('div#fileUploadModal').html(jsonData.content);
								} else {
									alert(jsonData.content);
								}
							}, 'json');
						}
						return false;
					});
				});
			{/literal}{else}{literal}
				// Disable the meta-data and finish tabs while uploading a file.
				$('div#fileUploadTabs').last().tabs('option', 'disabled', [1,2]);

				// Style buttons.
				$('.button').button();

				// Create callbacks to handle plupload actions.
				function attachCallbacks(uploader) {
					// Prevent > 1 files from being added.
					uploader.bind('FilesAdded', function(up, files) {
						if(up.files.length > 1) {
							up.splice(0,1);
							up.refresh();
						}
					});

					// Add the file genre field to the form.
					uploader.bind('QueueChanged', function(up, files) {
						{/literal}{if $showFileSelector}
							var $revisedFileId = $('#uploadForm #revisedFileId');
							$revisedFileId.attr("disabled", "disabled");
						{/if}
						{if $showGenreSelector}
							var $genreId = $('#uploadForm #genreId');
							$genreId.attr("disabled", "disabled");
						{/if}{literal}

						$("#uploadForm #plupload").pluploadQueue().settings.multipart_params = {
							{/literal}{if $showFileSelector}
								revisedFileId: $revisedFileId.val(),
							{else}
								revisedFileId: '{$revisedFileId}',
							{/if}
							genreId: {if $showGenreSelector}$genreId.val(){else}''{/if}{literal}
						};
					});

					// Handle the server's JSON response.
					uploader.bind('FileUploaded', function(up, files, ret) {
						jsonData = eval("("+ret.response+")");
						if (jsonData.status == true) {
							// Replace the upload modal content.
							$('div#fileUploadModal').html(jsonData.content);
						} else {
							alert(jsonData.content);
						}
					});
				}

				$(function() {
					// Setup the upload widget.
					$("#plupload").pluploadQueue({
						// General settings
						setup: attachCallbacks,
						runtimes: 'html5,flash,silverlight,html4',
						url: $("#uploadForm").attr('action'),
						max_file_size: '20mb',
						multi_selection: false,
						file_data_name: 'submissionFile',
						multipart: true,
						// Flash settings
						flash_swf_url : '{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/plupload/plupload.flash.swf',
						// Silverlight settings
						silverlight_xap_url : '{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/plupload/plupload.silverlight.xap'
					});

					{/literal}{if $showFileSelector && showGenreSelector}{literal}
						// When a user selects a submission to revise then the
						// the file genre chooser must be disabled.
						var $revisedFileId = $('#uploadForm #revisedFileId');
						var $genreId = $('#uploadForm #genreId');
						$revisedFileId.change(function() {
							// All file genres currently assigned to monograph files.
							var monographFileGenres = {
								{/literal}{foreach name=currentMonographFileGenres from=$currentMonographFileGenres key=monographFileId item=fileGenre}
									{$monographFileId}: {$fileGenre}{if !$smarty.foreach.currentMonographFileGenres.last},{/if}
								{/foreach}{literal}
							};
							if ($revisedFileId.val() == 0) {
								// New file...
								$genreId.attr('disabled', '');
							} else {
								// Revision...
								$genreId.val(monographFileGenres[$revisedFileId.val()]);
								$genreId.attr('disabled', 'disabled');
							}
						});
					{/literal}{/if}
				});
			{/if}
		{/if}

		$(function() {ldelim}
			// Set cancel/close button behavior.
			$("#uploadForm #cancelButton, .modalTitleBar .close").unbind('click').click(function() {ldelim}
				// If the user presses cancel after uploading a file then delete the file.
				deleteUrl = '{if $uploadedFile}{url op="deleteFile" monographId=$monographId fileId=$uploadedFile->getFileId() revision=$uploadedFile->getRevision() escape=false}{/if}';
				if(deleteUrl != '') {ldelim}
					$.post(deleteUrl);
				{rdelim}

				// Close the modal.
				$('div#fileUploadModal').last().parent().dialog('close');
				return false;
			{rdelim});
		{rdelim});
		// -->
	</script>

	<ul>
		<li><a href="#uploadFormTab">1. {translate key="submission.submit.upload"}</a></li>
		<li><a href="{if $metadataEditing}{url op="editMetadata" monographId=$monographId fileId=$uploadedFile->getFileId()}{else}#{/if}">2. {translate key="submission.submit.metadata"}</a></li>
		<li><a href="{if $metadataEditing}{url op="finishFileSubmissions" monographId=$monographId fileId=$uploadedFile->getFileId()}{else}#{/if}">3. {translate key="submission.submit.finishingUp"}</a></li>
	</ul>

	<div id="uploadFormTab">
		{if !$metadataEditing}
			<form name="uploadForm" id="uploadForm" action="{strip}
				{if $showPossibleRevision}
					{url op="confirmRevision" monographId=$monographId uploadedFileId=$uploadedFile->getFileId()}
				{else}
					{url op="uploadFile" monographId=$monographId}
				{/if}
			{/strip}" method="post">
				{fbvFormArea id="file"}
					{if $showPossibleRevision}
						<div id="possibleRevision" class="possibleRevision response" style="display:none;">
							<div id="revisionWarningIcon" class="warning"></div>
							<div id="revisionWarningText">
								<h5>{translate key="submission.upload.possibleRevision"}</h5>
								{translate key="submission.upload.possibleRevisionDescription" revisedFileName=$revisedFileName}
								{fbvSelect name="revisedFileId" id="revisedFileId" from=$monographFileOptions selected=$revisedFileId translate=false} <br />
							</div>
						</div>
					{else}
						{if $showFileNameOnly}
							{fbvFormSection title="submission.submit.currentFile"}
								{$revisedFileName}
							{/fbvFormSection}

							{* Save the revised file ID in a hidden input field. *}
							<input type="hidden" id="revisedFileId" name="revisedFileId" value="{$revisedFileId}" />
						{elseif $showFileSelector}
							{fbvFormSection title="submission.originalFile" required=$revisionOnly}
								<p>
									{if $revisionOnly}
										{translate key="submission.upload.selectMandatoryFileToRevise"}
									{else}
										{translate key="submission.upload.selectOptionalFileToRevise"}
									{/if}
								</p>
								{fbvSelect name="revisedFileId" id="revisedFileId" from=$monographFileOptions selected=$revisedFileId translate=false} <br />
							{/fbvFormSection}
						{/if}

						{if $showGenreSelector}
							{fbvFormSection title="common.fileType" required=1}
								{fbvSelect name="genreId" id="genreId" from=$monographFileGenres translate=false selected=$genreId}
							{/fbvFormSection}
						{/if}

						{fbvFormSection title="submission.submit.selectFile" required=1}
							<div id="plupload"></div>
						{/fbvFormSection}
					{/if}
				{/fbvFormArea}

				<div class="separator"></div>

				{fbvFormArea id="buttons"}
					{fbvFormSection}
						{fbvLink id="cancelButton" label="common.cancel"}
	 					{if $showPossibleRevision}
	 						{fbvButton id="continueButton" label="common.continue" align=$fbvStyles.align.RIGHT}
	 					{/if}
	 				{/fbvFormSection}
				{/fbvFormArea}
			</form>
		{/if}
	</div>
</div>
