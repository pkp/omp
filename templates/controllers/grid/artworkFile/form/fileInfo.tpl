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

<form id="artworkUploadForm-fileInfo-{$artworkFile->getId()|escape}" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.artworkFile.ArtworkFileGridHandler" op="updateArtworkFile" monographId=$artworkFile->getMonographId()}" method="post" enctype="multipart/form-data">

<input type="hidden" name="monographId" value="{$artworkFile->getMonographId()}" />
<input type="hidden" name="artworkFileId" value="{$artworkFile->getId()}" />

{assign var="fileStruct" value=$artworkFile->getFile()}
{assign var="permissionFile" value=$artworkFile->getPermissionFile()}
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

<br />

<div style="float:left;width:50%;">
<table id="fileInfo" class="data" width="100%">
<tr valign="top">
	<td width="40%" class="label">{translate key="common.fileName"}</td>
	<td width="60%" class="value">{$fileStruct->getFileName()|escape}</a></td>
</tr>
<tr valign="top">
	<td width="40%" class="label">{translate key="common.originalFileName"}</td>
	<td width="60%" class="value">{$fileStruct->getOriginalFileName()|escape}</a></td>
</tr>
<tr valign="top">
	<td width="40%" class="label">{translate key="common.fileType"}</td>
	<td width="60%" class="value">{$fileStruct->getExtension()|escape}</td>
</tr>
<tr valign="top">
	<td width="40%" class="label">{translate key="common.fileSize"}</td>
	<td width="60%" class="value">{$fileStruct->getNiceFileSize()}</td>
</tr>
<tr valign="top">
	<td width="40%" class="label">{translate key="common.quality"}</td>
	<td width="60%" class="value">
		{$image_width_on_device}''&nbsp;x&nbsp;{$image_height_on_device}'' @ 300 DPI/PPI<br />
		({$artworkFile->getWidth()} x {$artworkFile->getHeight()} pixels)
	</td>
</tr>
<tr valign="top">
	<td width="40%" class="label">{translate key="common.dateUploaded"}</td>
	<td width="60%" class="value">{$fileStruct->getDateUploaded()|date_format:$datetimeFormatShort}</td>
</tr>
</table>
</div>

<div style="float:left;padding:1.5em;border:1px solid gray">
<table>
<tr>
	<td width="100%" class="value"><strong>{translate key="common.preview"}</strong></td>
</tr>
<tr valign="middle">
	<td width="100%" class="value">
		<a target="_blank" href="{url op="viewFile" monographId=$artworkFile->getMonographId() fileId=$fileStruct->getFileId() fileRevision=$fileStruct->getRevision()}">
			<img class="thumbnail" width={$thumbnail_width} height={$thumbnail_height} src="{url op="viewFile" monographId=$artworkFile->getMonographId() fileId=$fileStruct->getFileId()}" />
		</a>
	</td>
</tr>
</table>

</div>

<div style="clear:both"></div>

<hr />

<h4>{translate key="grid.artworkFile.form.metadata"}</h4>

<table width="100%" class="data">
<tr>
	<td>{translate key="grid.artworkFile.caption"}</td><td><input type="text" name="artwork_caption" value="{$artworkFile->getCaption()|escape}" /></td>
</tr>
<tr>
	<td>{translate key="grid.artworkFile.credit"}</td><td><input type="text" name="artwork_credit" value="{$artworkFile->getCredit()|escape}" /></td>
</tr>
<tr>
	<td>{translate key="grid.artworkFile.copyrightOwner"}</td><td><input type="text" name="artwork_copyrightOwner" value="{$artworkFile->getCopyrightOwner()|escape}" /></td>
</tr>
<tr>
	<td>&nbsp;</td><td>{translate key="grid.artworkFile.copyrightContact"}: <input type="text" name="artwork_copyrightOwnerContact" value="{$artworkFile->getCopyrightOwnerContactDetails()|escape}" /></td>
</tr>
<tr>
	<td>{translate key="grid.artworkFile.permissionTerms"}</td><td><input type="text" name="artwork_permissionTerms" value="{$artworkFile->getPermissionTerms()|escape}" /></td>
</tr>
<!--
FIXME: ajax upload
<tr valign="top">
	<td>{translate key="grid.artworkFile.permissionForm"}</td>
	<td>
		{if $permissionFile}{$permissionFile->getFileName()|escape}{else}<em>{translate key="common.none"}</em>{/if}<br />
		{if $permissionFile}{translate key="common.replace"}{else}{translate key="common.upload"}{/if}&nbsp;<input type="file" name="artwork_permissionForm" size="10" />
	</td>
</tr>-->
<tr>
	<td valign="top">{translate key="grid.artworkFile.type"}</td>
	<td>
	<input type="radio" name="artwork_type" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_TABLE}"{if !$artworkFile->getType() || $artworkFile->getType() == $smarty.const.MONOGRAPH_ARTWORK_TYPE_TABLE} checked="checked"{/if} /> {translate key="grid.artworkFile.type.table"}<br />
	<input type="radio" name="artwork_type" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_FIGURE}"{if $artworkFile->getType() == $smarty.const.MONOGRAPH_ARTWORK_TYPE_FIGURE} checked="checked"{/if} /> {translate key="grid.artworkFile.type.figure"}<br />
	<input type="radio" name="artwork_type" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_OTHER}"{if $artworkFile->getType() == $smarty.const.MONOGRAPH_ARTWORK_TYPE_OTHER} checked="checked"{/if} /> {translate key="common.other"} &nbsp; <input type="text" name="artwork_otherType" value="{$artworkFile->getCustomType()|escape}" />
	</td>
</tr>
<tr>
	<td valign="top">{translate key="grid.artworkFile.placement"}</td>
	<td>
	<input type="radio" name="artwork_placementType" value="{$smarty.const.MONOGRAPH_ARTWORK_PLACEMENT_BY_CHAPTER}" /> {translate key="submission.chapter"}
	<select name="artwork_componentId">
		<option value="0">--{translate key="common.select"}--</option>
		{foreach from=$monographComponents item=component}
		<option value="{$component->getId()}">{$component->getLocalizedTitle()|escape}</option>
		{/foreach}
	</select>
	{translate key="grid.artworkFile.placementDetail"} &nbsp; <input type="text" name="artwork_placement" />
	<br />
	<input type="radio" name="artwork_placementType" value="{$smarty.const.MONOGRAPH_ARTWORK_PLACEMENT_OTHER}" checked="checked"/> {translate key="common.other"} &nbsp; <input type="text" name="artwork_otherPlacement" />
	</td>
</tr>
<tr>
	<td>{translate key="grid.artworkFile.contactAuthor"}</td>
	<td>
	<select name="artwork_contact">
	<option value="0">--{translate key="common.select"}--</option>
	{foreach from=$submission->getAuthors() item=author}
	<option value="{$author->getId()}">{$author->getFullName()|escape}</option>
	{/foreach}
	</select>
	</td>
</tr>
<tr>
	<td></td><td><input type="submit" name="uploadNewArtwork" value="{translate key="common.upload"}" class="button" /></td>
</tr>
</table>

</form>