{**
 * templates/controllers/wizard/fileUpload/form/metadataForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * File metadata form.
 *
 * Parameters:
 *  $submissionFile: The monograph or artwork file.
 *  $stageId: The workflow stage id from which the upload
 *   wizard was called.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#metadataForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="metadataForm" action="{url op="saveMetadata" monographId=$submissionFile->getMonographId() stageId=$stageId reviewRoundId=$reviewRoundId fileStage=$submissionFile->getFileStage() fileId=$submissionFile->getFileId() escape=false}" method="post">

	{* Editable metadata *}
	{fbvFormArea id="fileMetaData"}
		{fbvFormSection title="submission.form.name" required=true}
			{fbvElement type="text" id="name" value=$submissionFile->getLocalizedName() maxlength="120"}
		{/fbvFormSection}
		{if is_a($submissionFile, 'ArtworkFile')}
			{fbvFormSection title="grid.artworkFile.caption" inline=true size=$fbvStyles.size.MEDIUM}
				{fbvElement type="textarea" id="artworkCaption" height=$fbvStyles.height.SHORT value=$submissionFile->getCaption()}
			{/fbvFormSection}
			{fbvFormSection title="grid.artworkFile.credit" inline=true size=$fbvStyles.size.MEDIUM}
				{fbvElement type="textarea" id="artworkCredit" height=$fbvStyles.height.SHORT value=$submissionFile->getCredit()}
			{/fbvFormSection}
			{fbvFormSection title="submission.artwork.permissions"}
				{fbvElement type="text" inline=true size=$fbvStyles.size.MEDIUM label="grid.artworkFile.copyrightOwner" id="artworkCopyrightOwner" value=$submissionFile->getCopyrightOwner()}
				{fbvElement type="text" inline=true size=$fbvStyles.size.MEDIUM label="grid.artworkFile.permissionTerms" id="artworkPermissionTerms" value=$submissionFile->getPermissionTerms()}
			{/fbvFormSection}
		{/if}
		{fbvFormSection title="submission.upload.noteToAccompanyFile"}
			{fbvElement type="textarea" id="note" height=$fbvStyles.height.SHORT}
		{/fbvFormSection}
	{/fbvFormArea}

	{* Read-only meta-data *}

	{fbvFormArea id="fileInfo" title="submission.submit.fileInformation"}
		{fbvFormSection title="common.fileName" inline=true size=$fbvStyles.size.MEDIUM}
			{$submissionFile->getFileName()|escape}
		{/fbvFormSection}
		{fbvFormSection title="common.fileType" inline=true size=$fbvStyles.size.MEDIUM}
			{$submissionFile->getExtension()|escape}
		{/fbvFormSection}
		{fbvFormSection title="common.fileSize" inline=true size=$fbvStyles.size.MEDIUM}
			{$submissionFile->getNiceFileSize()}
		{/fbvFormSection}

		{if is_a($submissionFile, 'ArtworkFile') && $submissionFile->getWidth() > 0 && $submissionFile->getHeight() > 0}
			{assign var=dpi value=300}
			{math assign="imageWidthOnDevice" equation="w/dpi" w=$submissionFile->getWidth() dpi=$dpi format="%.2f"}
			{math assign="imageHeightOnDevice" equation="h/dpi" h=$submissionFile->getHeight() dpi=$dpi format="%.2f"}
			{fbvFormSection title="common.quality" inline=true size=$fbvStyles.size.MEDIUM}
				{translate key="common.dimensionsInches" width=$imageWidthOnDevice height=$imageHeightOnDevice dpi=$dpi}
				<br/>
				({translate key="common.dimensionsPixels" width=$submissionFile->getWidth() height=$submissionFile->getHeight()})
			{/fbvFormSection}
			{fbvFormSection title="common.preview" inline=true size=$fbvStyles.size.MEDIUM}
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
				{else}<a target="_blank" href="{url component="api.file.FileApiHandler" op="viewFile" monographId=$submissionFile->getMonographId() stageId=$stageId fileStage=$submissionFile->getFileStage() fileId=$submissionFile->getFileId() revision=$submissionFile->getRevision()}">
					<img class="thumbnail" width="{$thumbnailWidth}" height="{$thumbnailHeight}" src="{url component="api.file.FileApiHandler" op="viewFile" monographId=$submissionFile->getMonographId() stageId=$stageId fileStage=$submissionFile->getFileStage() fileId=$submissionFile->getFileId()}" />
				</a>{/if}
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}
</form>
