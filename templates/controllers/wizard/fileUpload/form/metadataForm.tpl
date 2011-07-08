{**
 * metadataForm.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * File metadata form.
 *
 * Parameters:
 *  $submissionFile: The monograph or artwork file.
 *  $stageId: The workflow stage id from which the upload
 *   wizard was called.
 *  $note: Note attached to the file.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#metadataForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="metadataForm" action="{url op="saveMetadata" monographId=$submissionFile->getMonographId() stageId=$stageId fileStage=$submissionFile->getFileStage() fileId=$submissionFile->getFileId() escape=false}" method="post">

	{* Editable metadata *}

	{if is_a($submissionFile, 'ArtworkFile')}
		{assign var="metadataFormAreaTitle" value="submission.artworkFileDetails"}
	{else}
		{assign var="metadataFormAreaTitle" value="submission.fileDetails"}
	{/if}

	{fbvFormArea id="fileMetaData" title=$metadataFormAreaTitle}
		{fbvFormSection title="common.name" required=1}
			{fbvElement type="text" label="Enter a name that describes the contents of this file" id="name" value=$submissionFile->getLocalizedName() maxlength="120"}
		{/fbvFormSection}
		{if is_a($submissionFile, 'ArtworkFile')}
			{fbvFormSection title="grid.artworkFile.caption" inline=true size=$fbvStyles.size.MEDIUM}
				{fbvElement type="textarea" id="artworkCaption" value=$submissionFile->getCaption()}
			{/fbvFormSection}
			{fbvFormSection title="grid.artworkFile.credit" inline=true size=$fbvStyles.size.MEDIUM}
				{fbvElement type="textarea" id="artworkCredit" value=$submissionFile->getCredit()}
			{/fbvFormSection}
			{fbvFormSection title="submission.artwork.permissions"}
				{fbvElement type="text" inline=true size=$fbvStyles.size.MEDIUM label="grid.artworkFile.copyrightOwner" id="artworkCopyrightOwner" value=$submissionFile->getCopyrightOwner()}
				{fbvElement type="text" inline=true size=$fbvStyles.size.MEDIUM label="grid.artworkFile.permissionTerms" id="artworkPermissionTerms" value=$submissionFile->getPermissionTerms()}
			{/fbvFormSection}
			{fbvFormSection title="grid.artworkFile.placement"}
				{fbvElement type="text" id="artworkPlacement"}
			{/fbvFormSection}
		{/if}
		{fbvFormSection title="common.note"}
			{if $note}
				{fbvElement type="textarea" id="note" value=$note->getContents() height=$fbvStyles.height.SHORT}
			{else}
				{fbvElement type="textarea" id="note" height=$fbvStyles.height.SHORT}
			{/if}
		{/fbvFormSection}
	{/fbvFormArea}

	{* Read-only meta-data *}

	{fbvFormArea id="fileInfo" title="submission.submit.readOnlyInfo"}
		{fbvFormSection title="common.fileName"}
			{$submissionFile->getFileName()|escape}
		{/fbvFormSection}
		{fbvFormSection title="common.originalFileName"}
			{$submissionFile->getOriginalFileName()|escape}
		{/fbvFormSection}
		{fbvFormSection title="common.dateUploaded"}
			{$submissionFile->getDateUploaded()|date_format:$datetimeFormatShort}
		{/fbvFormSection}
		{fbvFormSection title="common.type"}
			{$submissionFile->getDocumentType()}
		{/fbvFormSection}
		{fbvFormSection title="common.fileType"}
			{$submissionFile->getExtension()|escape}
		{/fbvFormSection}
		{fbvFormSection title="common.fileSize"}
			{$submissionFile->getNiceFileSize()}
		{/fbvFormSection}

		{if is_a($submissionFile, 'ArtworkFile') && $submissionFile->getWidth() > 0 && $submissionFile->getHeight() > 0}
			{fbvFormSection title="common.preview"}
				{* Get scaled thumbnail dimensions to 100px *}
				{if $submissionFile->getWidth() > $submissionFile->getHeight()}
					{math assign="thumbnailHeight" equation="(h*100)/w" h=$submissionFile->getHeight() w=$submissionFile->getWidth()}
					{assign var="thumbnailWidth" value=100}
				{else}
					{math assign="thumbnailHeight" equation="(w*100)/h" w=$submissionFile->getWidth() h=$submissionFile->getHeight()}
					{assign var="thumbnailWidth" value=100}
				{/if}

				{if $submissionFile->getFileType() == 'image/tiff'}
					<embed width="{$thumbnailWidth}" height="{$thumbnailHeight}" src="{url component="api.file.FileApiHandler" op="viewFile" monographId=$submissionFile->getMonographId() stageId=$stageId fileStage=$submissionFile->getFileStage() fileId=$submissionFile->getFileId()}" type="image/tiff" negative=yes>
				{else}<a target="_blank" href="{url component="api.file.FileApiHandler" op="viewFile" monographId=$submissionFile->getMonographId() stageId=$stageId fileStage=$submissionFile->getFileStage() fileId=$submissionFile->getFileId() fileRevision=$submissionFile->getRevision()}">
					<img class="thumbnail" width="{$thumbnailWidth}" height="{$thumbnailHeight}" src="{url component="api.file.FileApiHandler" op="viewFile" monographId=$submissionFile->getMonographId() stageId=$stageId fileStage=$submissionFile->getFileStage() fileId=$submissionFile->getFileId()}" />
				</a>{/if}
			{/fbvFormSection}

			{math assign="imageWidthOnDevice" equation="w/300" w=$submissionFile->getWidth() format="%.2f"}
			{math assign="imageHeightOnDevice" equation="h/300" h=$submissionFile->getHeight() format="%.2f"}
			{fbvFormSection title="common.quality"}
				{$imageWidthOnDevice}''&nbsp;x&nbsp;{$imageHeightOnDevice}'' @ 300 DPI/PPI<br />
				({$submissionFile->getWidth()} x {$submissionFile->getHeight()} pixels)
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}
</form>
