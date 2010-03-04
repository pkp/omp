{**
 * fileInfo.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files grid form
 *
 * $Id$
 *}

<input type="hidden" name="monographId" value="{$artworkFile->getMonographId()}" />
<input type="hidden" name="artworkFileId" value="{$artworkFile->getId()}" />

{assign var="fileStruct" value=$artworkFile->getFile()}

<table id="fileInfo" class="data" width="100%">
<tr valign="top">
	<td width="20%" class="label">{translate key="common.fileName"}</td>
	<td width="80%" class="value">{$fileStruct->getFileName()|escape}</a></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">{translate key="common.fileSize"}</td>
	<td width="80%" class="value">{$fileStruct->getNiceFileSize()}</td>
</tr>
<tr valign="top">
	<td width="20%" class="label">{translate key="common.dateUploaded"}</td>
	<td width="80%" class="value">{$fileStruct->getDateUploaded()|date_format:$datetimeFormatShort}</td>
</tr>
</table>

<h3>{translate key="grid.artworkFile.metadata"}</h3>

<table width="100%" class="data">
<tr>
	<td>{translate key="grid.artworkFile.caption"}</td><td><input type="text" name="artwork_caption" /></td>
</tr>
<tr>
	<td>{translate key="grid.artworkFile.credit"}</td><td><input type="text" name="artwork_credit" /></td>
</tr>
<tr>
	<td>{translate key="grid.artworkFile.copyrightOwner"}</td><td><input type="text" name="artwork_copyrightOwner" /></td>
</tr>
<tr>
	<td>&nbsp;</td><td>{translate key="grid.artworkFile.copyrightContact"}: <input type="text" name="artwork_copyrightOwnerContact" /></td>
</tr>
<tr>
	<td>{translate key="grid.artworkFile.permissionTerms"}</td><td><input type="text" name="artwork_permissionTerms" /></td>
</tr>
<tr>
	<td>{translate key="grid.artworkFile.permissionForm"}</td><td><input type="file" name="artwork_permissionForm" size="10" class="uploadField" /></td>
</tr>
<tr>
	<td valign="top">{translate key="grid.artworkFile.type"}</td>
	<td>
	<input type="radio" name="artwork_type" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_TABLE}" checked="checked"/> {translate key="grid.artworkFile.type.table"}<br />
	<input type="radio" name="artwork_type" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_FIGURE}" /> {translate key="grid.artworkFile.type.figure"}<br />
	<input type="radio" name="artwork_type" value="{$smarty.const.MONOGRAPH_ARTWORK_TYPE_OTHER}" /> {translate key="common.other"} &nbsp; <input type="text" name="artwork_otherType" />
	</td>
</tr>
<tr>
	<td valign="top">{translate key="grid.artworkFile.placement"}</td>
	<td>
	<input type="radio" name="artwork_placementType" value="{$smarty.const.MONOGRAPH_ARTWORK_PLACEMENT_BY_CHAPTER}" /> {translate key="submission.chapter"}
	<select name="artwork_componentId">
	<option value="0">--{translate key="common.select"}--</option>
	{foreach from=$submission->getComponents() item=component}
	<option value="{$component->getId()}">{$component->getLocalizedTitle()}</option>
	{/foreach}
	</select>{translate key="grid.artworkFile.placementDetail"} &nbsp; <input type="text" size="4" name="artwork_placement" />
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