{**
 * fileInfo.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Artwork file info/metadata.
 *
 * $Id$
 *}

<script type="text/javascript">
	{literal}
	$(function() {
		$('#fileUploadTabs-').attr("id","fileUploadTabs-{/literal}{$fileId}{literal}"); // Rename container to use unique id (necessary to prevent caching)
		$('.button').button();
		$('#metadataForm-{/literal}{$fileId}{literal}').ajaxForm({
			dataType: 'json',
	        success: function(returnString) {
	    		if (returnString.status == true) {
		    		$('#loading').hide();
		    		if(returnString.isEditing) { // User was editing existing item, save and close
			    		saveAndUpdate('{/literal}{url router=$smarty.const.ROUTE_COMPONENT component="grid.files.submissionFiles.SubmissionFilesGridHandler" op="returnFileRow" fileId=$fileId}{literal}',
			    				'replace',
			    				'component-'+'{/literal}{$gridId}{literal}'+'-row-'+'{/literal}{$fileId}{literal}',
        						'#fileUploadTabs-{/literal}{$fileId}{literal}');
		    		} else {
			    		$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('url', 2, returnString.finishingUpUrl);
			    		$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('enable', 2);
			    		$('#fileUploadTabs-{/literal}{$fileId}{literal}').tabs('select', 2);
		    		}
	    		} else {

	    		}
	        }
	    });

		// Set cancel/continue button behaviors
		$("#continueButton2-{/literal}{$fileId}{literal}").click(function() {
			validator = $('#metadataForm-{/literal}{$fileId}{literal}').validate();
			if($('#metadataForm-{/literal}{$fileId}{literal}').valid()) {
				$('#metadataForm-{/literal}{$fileId}{literal}').submit();   // Hands off further actions to the ajaxForm function above
			}
			validator = null;
		});
		$("#cancelButton2-{/literal}{$fileId}{literal}").click(function() {
			//  The user has cancelled the modal without filling out the metadata form
			deleteUrl = '{/literal}{url router=$smarty.const.ROUTE_COMPONENT component="grid.files.submissionFiles.SubmissionFilesGridHandler" op="deleteFile" fileId=$fileId}{literal}';
			newFile = $('#newFile').val();
			if(newFile != "") {
				$.post(deleteUrl);
			}

			$('#fileUploadTabs-{/literal}{$fileId}{literal}').parent().dialog('close');
		});
	});
	{/literal}
</script>


<form name="metadataForm-{$fileId}" id="metadataForm-{$fileId}" action="{url component="grid.files.submissionFiles.SubmissionFilesGridHandler" op="saveMetadata" monographId=$monographId fileId=$fileId}" method="post">

<h3>{translate key='submission.artworkFileDetails'}</h3>

<!-- Editable metadata -->

{fbvFormArea id="fileMetaData"}
	{fbvFormSection title="common.name" float=$fbvStyles.float.LEFT}
		{fbvElement type="text" id="name" value=$artworkFile->getLocalizedName() maxlength="120" size=$fbvStyles.size.LARGE}
	{/fbvFormSection}
	{fbvFormSection title="grid.artworkFile.captionAndCredit"}
		{fbvTextarea id="artwork_caption" value=$artworkFile->getCaption() size=$fbvStyles.size.SMALL}
	{/fbvFormSection}
	{fbvFormSection title="submission.artwork.permissions" float=$fbvStyles.float.LEFT}
		{fbvElement type="text" label="grid.artworkFile.copyrightOwner" id="artwork_copyrightOwner" value=$artworkFile->getCopyrightOwner() size=$fbvStyles.size.LARGE}
	{/fbvFormSection}
	{fbvFormSection float=$fbvStyles.float.RIGHT}
		<br />
		{fbvElement type="text" float=$fbvStyles.float.RIGHT label="grid.artworkFile.copyrightContact" id="artwork_copyrightOwnerContact" value=$artworkFile->getCopyrightOwnerContactDetails() size=$fbvStyles.size.LARGE}
	{/fbvFormSection}
	{fbvFormSection float=$fbvStyles.float.LEFT}
		{fbvElement type="text" label="grid.artworkFile.permissionTerms" id="artwork_permissionTerms" value=$artworkFile->getPermissionTerms() size=$fbvStyles.size.LARGE}
	{/fbvFormSection}
	{fbvFormSection title='grid.artworkFile.type' layout=$fbvStyles.layout.TWO_COLUMN}
		{if !$artworkFile->getType() || $artworkFile->getType() == $smarty.const.MONOGRAPH_ARTWORK_TYPE_TABLE}
			{assign var="isTable" value=true}
		{elseif $artworkFile->getType() == $smarty.const.MONOGRAPH_ARTWORK_TYPE_FIGURE}
			{assign var="isFigure" value=true}
		{elseif $artworkFile->getType() == $smarty.const.MONOGRAPH_ARTWORK_TYPE_OTHER}
			{assign var="isOther" value=true}
		{/if}

		{fbvElement type="radio" name="artwork_type" id="artwork_type-0" value=$smarty.const.MONOGRAPH_ARTWORK_TYPE_TABLE checked=$isTable label="grid.artworkFile.type.table"}
		{fbvElement type="radio" name="artwork_type" id="artwork_type-1" value=$smarty.const.MONOGRAPH_ARTWORK_TYPE_FIGURE checked=$isFigure label="grid.artworkFile.type.figure"}
		{fbvElement float=$fbvStyles.float.LEFT type="radio" name="artwork_type" id="artwork_type-2" value=$smarty.const.MONOGRAPH_ARTWORK_TYPE_OTHER checked=$isOther label="common.other"}
		{fbvElement float=$fbvStyles.float.RIGHT type="text" id="artwork_otherType" value=$artworkFile->getCustomType()}
	{/fbvFormSection}
	{fbvFormSection title="grid.artworkFile.placement" layout=$fbvStyles.layout.THREE_COLUMN}
		{fbvElement type="radio" label="submission.chapter" name="artwork_placementType" id="artwork_placementType-0" value=$smarty.const.MONOGRAPH_ARTWORK_PLACEMENT_BY_CHAPTER}
		{fbvElement type="select" from=$componentOptions selected=$selectedComponent id="artwork_componentId" translate="true"}
		{fbvElement type="text" label="grid.artworkFile.placementDetail" id="artwork_placement"}
	{/fbvFormSection}
	{fbvFormSection layout=$fbvStyles.layout.TWO_COLUMN}
		{fbvElement type="radio" label="common.other" name="artwork_placementType" id="artwork_placementType-1" value=$smarty.const.MONOGRAPH_ARTWORK_PLACEMENT_OTHER checked="checked"}
		{fbvElement type="text" id="artwork_otherPlacement"}
	{/fbvFormSection}
	{fbvFormSection title="common.note"}
		{if $note}
			{fbvTextarea id="note" value=$note->getContents() size=$fbvStyles.size.SMALL}
		{else}
			{fbvTextarea id="note" size=$fbvStyles.size.SMALL}
		{/if}
	{/fbvFormSection}
{/fbvFormArea}

<div class="separator" />

<!-- Read-only information -->

{** get scaled thumbnail dimensions to 100px **}
{if $artworkFile->getWidth() > $artworkFile->getHeight()}
	{math assign="thumbnail_height" equation="(h*100)/w" h=$artworkFile->getHeight() w=$artworkFile->getWidth()}
	{assign var="thumbnail_width" value=100}
{else}
	{math assign="thumbnail_height" equation="(w*100)/h" w=$artworkFile->getWidth() h=$artworkFile->getHeight()}
	{assign var="thumbnail_width" value=100}
{/if}

{math assign="image_width_on_device" equation="w/300" w=$artworkFile->getWidth() format="%.2f"}
{math assign="image_height_on_device" equation="h/300" h=$artworkFile->getHeight() format="%.2f"}

<h4>{translate key="author.submit.readOnlyInfo"}</h4>
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
			{$image_width_on_device}''&nbsp;x&nbsp;{$image_height_on_device}'' @ 300 DPI/PPI<br />
			({$artworkFile->getWidth()} x {$artworkFile->getHeight()} pixels)
		{/fbvFormSection}
	{/fbvFormArea}
</div>

<div style="float:left;padding:1.5em;">
	{fbvFormArea id="fileInfo"}
		{fbvFormSection title="common.preview" float=$fbvStyles.float.Right}
			<a target="_blank" href="{url op="viewFile" monographId=$artworkFile->getMonographId() fileId=$monographFile->getFileId() fileRevision=$monographFile->getRevision()}">
				<img class="thumbnail" width={$thumbnail_width} height={$thumbnail_height} src="{url op="viewFile" monographId=$artworkFile->getMonographId() fileId=$monographFile->getFileId()}" />
			</a>
		{/fbvFormSection}
	{/fbvFormArea}
</div>

<div style="clear:both"></div>

{fbvFormArea id="buttons"}
	{fbvFormSection}
		{fbvButton id="cancelButton2-$fileId" label="common.cancel" float=$fbvStyles.float.LEFT}
		{fbvButton id="continueButton2-$fileId" label="common.continue" float=$fbvStyles.float.RIGHT}
	{/fbvFormSection}
{/fbvFormArea}

</form>

{if $gridId}
	<input type="hidden" name="gridId" value="{$gridId|escape}" />
{/if}
{if $fileId}
	<input type="hidden" name="fileId" value="{$fileId|escape}" />
{/if}
<br />