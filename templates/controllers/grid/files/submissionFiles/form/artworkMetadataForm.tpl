{**
 * artworkMetadataForm.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Artwork file info/metadata.
 *
 * $Id$
 *}
{assign var='uniqueId' value=""|uniqid}

<script type="text/javascript">
	{literal}
	$(function() {
		$('.button').button();
		$('#metadataForm').ajaxForm({
			dataType: 'json',
	        	success: function(returnString) {
	    			if (returnString.status == true) {
			    		$('#loading').hide();
			    		if(returnString.isEditing) { // User was editing existing item, save and close
				    		saveAndUpdate('{/literal}{url router=$smarty.const.ROUTE_COMPONENT op="returnFileRow" monographId=$monographId fileId=$fileId escape=false}{literal}',
				    				'replace',
				    				'#component-'+'{/literal}{$gridId}{literal}'+'-row-'+'{/literal}{$fileId}{literal}',
	        						'div#fileUploadTabs');
			    		} else {
				    		$('div#fileUploadTabs').last().tabs('url', 2, returnString.finishingUpUrl);
				    		$('div#fileUploadTabs').last().tabs('enable', 2);
				    		$('div#fileUploadTabs').last().tabs('select', 2);
			    		}
		    		}
			}
	    });

		// Set cancel/continue button behaviors
		$("#continueButton2").click(function() {
			validator = $('#metadataForm').validate();
			if($('#metadataForm').valid()) {
				$('#metadataForm').submit();   // Hands off further actions to the ajaxForm function above
			}
			validator = null;
		});
		$("#cancelButton2").click(function() {
			$('div#fileUploadTabs').last().parent().dialog('close');
			return false;
		});
	});
	{/literal}
</script>


<form name="metadataForm" id="metadataForm" action="{url op="saveMetadata" monographId=$monographId fileId=$fileId}" method="post">

<h3>{translate key='submission.artworkFileDetails'}</h3>

<!-- Editable metadata -->

{fbvFormArea id="fileMetaData"}
	{fbvFormSection required=1 title="common.name" float=$fbvStyles.float.LEFT}
		{fbvElement type="text" id="name" value=$artworkFile->getLocalizedName() maxlength="120" size=$fbvStyles.size.LARGE}
	{/fbvFormSection}
	{fbvFormSection title="grid.artworkFile.captionAndCredit"}
		{fbvTextarea id="artworkCaption" value=$artworkFile->getCaption() size=$fbvStyles.size.SMALL}
	{/fbvFormSection}
	{fbvFormSection title="submission.artwork.permissions" float=$fbvStyles.float.LEFT}
		{fbvElement type="text" label="grid.artworkFile.copyrightOwner" id="artworkCopyrightOwner" value=$artworkFile->getCopyrightOwner() size=$fbvStyles.size.LARGE}
	{/fbvFormSection}
	{fbvFormSection float=$fbvStyles.float.RIGHT}
		<br />
		{fbvElement type="text" float=$fbvStyles.float.RIGHT label="grid.artworkFile.copyrightContact" id="artworkCopyrightOwnerContact" value=$artworkFile->getCopyrightOwnerContactDetails() size=$fbvStyles.size.LARGE}
	{/fbvFormSection}
	{fbvFormSection float=$fbvStyles.float.LEFT}
		{fbvElement type="text" label="grid.artworkFile.permissionTerms" id="artworkPermissionTerms" value=$artworkFile->getPermissionTerms() size=$fbvStyles.size.LARGE}
	{/fbvFormSection}
	{fbvFormSection title='grid.artworkFile.type' layout=$fbvStyles.layout.TWO_COLUMN}
		{if !$artworkFile->getType() || $artworkFile->getType() == $smarty.const.MONOGRAPH_ARTWORK_TYPE_TABLE}
			{assign var="isTable" value=true}
		{elseif $artworkFile->getType() == $smarty.const.MONOGRAPH_ARTWORK_TYPE_FIGURE}
			{assign var="isFigure" value=true}
		{elseif $artworkFile->getType() == $smarty.const.MONOGRAPH_ARTWORK_TYPE_PHOTO}
			{assign var="isPhoto" value=true}
		{elseif $artworkFile->getType() == $smarty.const.MONOGRAPH_ARTWORK_TYPE_ILLUSTRATION}
			{assign var="isIllustration" value=true}
		{elseif $artworkFile->getType() == $smarty.const.MONOGRAPH_ARTWORK_TYPE_OTHER}
			{assign var="isOther" value=true}
		{/if}

		{fbvElement type="radio" name="artworkType" id="artworkType-0" value=$smarty.const.MONOGRAPH_ARTWORK_TYPE_TABLE checked=$isTable label="grid.artworkFile.type.table"}
		{fbvElement type="radio" name="artworkType" id="artworkType-1" value=$smarty.const.MONOGRAPH_ARTWORK_TYPE_FIGURE checked=$isFigure label="grid.artworkFile.type.figure"}
		{fbvElement type="radio" name="artworkType" id="artworkType-1" value=$smarty.const.MONOGRAPH_ARTWORK_TYPE_PHOTO checked=$isPhoto label="grid.artworkFile.type.photo"}
		{fbvElement type="radio" name="artworkType" id="artworkType-1" value=$smarty.const.MONOGRAPH_ARTWORK_TYPE_ILLUSTRATION checked=$isIllustration label="grid.artworkFile.type.illustration"}
		{fbvElement float=$fbvStyles.float.LEFT type="radio" name="artworkType" id="artworkType-2" value=$smarty.const.MONOGRAPH_ARTWORK_TYPE_OTHER checked=$isOther label="common.other"}
		{fbvElement float=$fbvStyles.float.RIGHT type="text" id="artworkOtherType" value=$artworkFile->getCustomType()}
	{/fbvFormSection}
	{fbvFormSection title="grid.artworkFile.placement"}
		{fbvElement type="text" id="artworkPlacement"}
	{/fbvFormSection}
	{fbvFormSection title="common.note"}
		{if $note}
			{fbvTextarea id="note" value=$note->getContents() size=$fbvStyles.size.LARGE}
		{else}
			{fbvTextarea id="note" size=$fbvStyles.size.SMALL}
		{/if}
	{/fbvFormSection}
{/fbvFormArea}

<div class="separator" />

<!-- Read-only information -->

{** get scaled thumbnail dimensions to 100px **}
{if $artworkFile->getWidth() > $artworkFile->getHeight()}
	{math assign="thumbnailHeight" equation="(h*100)/w" h=$artworkFile->getHeight() w=$artworkFile->getWidth()}
	{assign var="thumbnailWidth" value=100}
{else}
	{math assign="thumbnailHeight" equation="(w*100)/h" w=$artworkFile->getWidth() h=$artworkFile->getHeight()}
	{assign var="thumbnailWidth" value=100}
{/if}

{math assign="imageWidthOnDevice" equation="w/300" w=$artworkFile->getWidth() format="%.2f"}
{math assign="imageHeightOnDevice" equation="h/300" h=$artworkFile->getHeight() format="%.2f"}

<h4>{translate key="submission.submit.readOnlyInfo"}</h4>
<div style="float:left;width:33%;">
	{fbvFormArea id="fileInfo"}
		{fbvFormSection title="common.fileName" float=$fbvStyles.float.LEFT}
			{$monographFile->getFileName()|escape}
		{/fbvFormSection}
		{fbvFormSection title="common.originalFileName" float=$fbvStyles.float.LEFT}
			{$monographFile->getOriginalFileName()|escape}
		{/fbvFormSection}
		{fbvFormSection title="common.dateUploaded" float=$fbvStyles.float.LEFT}
			{$monographFile->getDateUploaded()|date_format:$datetimeFormatShort}
		{/fbvFormSection}
	{/fbvFormArea}
</div>

<div style="float:left;width:33%;">
	{fbvFormArea id="fileInfo"}
		{fbvFormSection title="common.fileType" float=$fbvStyles.float.Right}
			{$monographFile->getExtension()|escape}
		{/fbvFormSection}
		{fbvFormSection title="common.fileSize" float=$fbvStyles.float.LEFT}
			{$monographFile->getNiceFileSize()}
		{/fbvFormSection}
		{fbvFormSection title="common.quality" float=$fbvStyles.float.LEFT}
			{$imageWidthOnDevice}''&nbsp;x&nbsp;{$imageHeightOnDevice}'' @ 300 DPI/PPI<br />
			({$artworkFile->getWidth()} x {$artworkFile->getHeight()} pixels)
		{/fbvFormSection}
	{/fbvFormArea}
</div>

<div style="float:left;padding:1.5em;">
	{fbvFormArea id="fileInfo"}
		{fbvFormSection title="common.preview" float=$fbvStyles.float.Right}
			{if $monographFile->getFileType() == 'image/tiff'}
				<embed width={$thumbnailWidth} height={$thumbnailHeight} src="{url op="viewFile" monographId=$artworkFile->getMonographId() fileId=$monographFile->getFileId()}" type="image/tiff" negative=yes>
			{else}<a target="_blank" href="{url op="viewFile" monographId=$artworkFile->getMonographId() fileId=$monographFile->getFileId() fileRevision=$monographFile->getRevision()}">
				<img class="thumbnail" width={$thumbnailWidth} height={$thumbnailHeight} src="{url op="viewFile" monographId=$artworkFile->getMonographId() fileId=$monographFile->getFileId()}" />
			</a>{/if}

		{/fbvFormSection}
	{/fbvFormArea}
</div>

<div style="clear:both"></div>

{fbvFormArea id="buttons"}
    {fbvFormSection}
        {fbvLink id="cancelButton2" label="common.cancel"}
        {fbvButton id="continueButton2" label="common.continue" align=$fbvStyles.align.RIGHT}
    {/fbvFormSection}
{/fbvFormArea}

</form>
