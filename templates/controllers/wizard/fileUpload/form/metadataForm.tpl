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
		$('#metadataForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<form id="metadataForm" action="{url op="saveMetadata" monographId=$submissionFile->getMonographId() stageId=$stageId fileStage=$submissionFile->getFileStage() fileId=$submissionFile->getFileId() escape=false}" method="post">

	{* Editable metadata *}

	<h3>
		{if is_a($submissionFile, 'ArtworkFile')}
			{translate key='submission.artworkFileDetails'}
		{else}
			{translate key='submission.fileDetails'}
		{/if}
	</h3>

	{fbvFormArea id="fileMetaData"}
		{fbvFormSection title="common.name" required=1 float=$fbvStyles.float.LEFT}
			{fbvElement type="text" label="common.name" id="name" value=$submissionFile->getLocalizedName() maxlength="120" size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
		{if is_a($submissionFile, 'ArtworkFile')}
			{fbvFormSection title="grid.artworkFile.caption"}
				{fbvTextArea id="artworkCaption" value=$submissionFile->getCaption() size=$fbvStyles.size.SMALL}
			{/fbvFormSection}
			{fbvFormSection title="grid.artworkFile.credit"}
				{fbvTextArea id="artworkCredit" value=$submissionFile->getCredit() size=$fbvStyles.size.SMALL}
			{/fbvFormSection}
			{fbvFormSection title="submission.artwork.permissions" float=$fbvStyles.float.LEFT}
				{fbvElement type="text" label="grid.artworkFile.copyrightOwner" id="artworkCopyrightOwner" value=$submissionFile->getCopyrightOwner() size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
			{fbvFormSection float=$fbvStyles.float.RIGHT}
				<br />
				{fbvElement type="text" float=$fbvStyles.float.RIGHT label="grid.artworkFile.copyrightContact" id="artworkCopyrightOwnerContact" value=$submissionFile->getCopyrightOwnerContactDetails() size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
			{fbvFormSection float=$fbvStyles.float.LEFT}
				{fbvElement type="text" label="grid.artworkFile.permissionTerms" id="artworkPermissionTerms" value=$submissionFile->getPermissionTerms() size=$fbvStyles.size.LARGE}
			{/fbvFormSection}
			{fbvFormSection title="grid.artworkFile.placement"}
				{fbvElement type="text" id="artworkPlacement"}
			{/fbvFormSection}
		{/if}
	{/fbvFormArea}

	{fbvFormSection title="common.note"}
		{if $note}
			{fbvTextArea id="note" value=$note->getContents() size=$fbvStyles.size.SMALL}
		{else}
			{fbvTextArea id="note" size=$fbvStyles.size.SMALL}
		{/if}
	{/fbvFormSection}

	<div class="separator" />


	{* Read-only meta-data *}

	<h4>{translate key="submission.submit.readOnlyInfo"}</h4>

	<div style="float:left;width:33%;padding-left:10px;">
		{fbvFormArea id="fileInfo"}
			{fbvFormSection title="common.fileName" float=$fbvStyles.float.LEFT}
				{$submissionFile->getFileName()|escape}
			{/fbvFormSection}
			{fbvFormSection title="common.originalFileName" float=$fbvStyles.float.LEFT}
				{$submissionFile->getOriginalFileName()|escape}
			{/fbvFormSection}
			{fbvFormSection title="common.dateUploaded" float=$fbvStyles.float.LEFT}
				{$submissionFile->getDateUploaded()|date_format:$datetimeFormatShort}
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	<div style="float:left;width:25%;">
		{fbvFormArea id="fileInfo"}
			{fbvFormSection title="common.type" float=$fbvStyles.float.LEFT}
				{$submissionFile->getDocumentType()}
			{/fbvFormSection}
			{fbvFormSection title="common.fileType" float=$fbvStyles.float.Right}
				{$submissionFile->getExtension()|escape}
			{/fbvFormSection}
			{fbvFormSection title="common.fileSize" float=$fbvStyles.float.LEFT}
				{$submissionFile->getNiceFileSize()}
			{/fbvFormSection}
		{/fbvFormArea}
	</div>

	{if is_a($submissionFile, 'ArtworkFile') &&
			$submissionFile->getWidth() > 0 && $submissionFile->getHeight() > 0}
		<div style="float:left;">
			{fbvFormArea id="fileInfo"}
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
			{/fbvFormArea}
		</div>
	{/if}

	<div style="clear:both"></div>
</form>
