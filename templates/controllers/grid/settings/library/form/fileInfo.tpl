<!-- templates/controllers/grid/settings/library/form/fileInfo.tpl -->

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

<input type="hidden" name="newUpload" value="1" />
<input type="hidden" name="fileId" value="{$libraryFile->getId()}" />
<table id="fileInfo" class="data" width="100%">
<tr valign="top">
	<td width="20%" class="label">{translate key="common.fileName"}</td>
	<td width="80%" class="value">{$libraryFile->getFileName()|escape}</a></td>
</tr>
<tr valign="top">
	<td width="20%" class="label">{translate key="common.fileSize"}</td>
	<td width="80%" class="value">{$libraryFile->getNiceFileSize()}</td>
</tr>
<tr valign="top">
	<td width="20%" class="label">{translate key="common.dateUploaded"}</td>
	<td width="80%" class="value">{$libraryFile->getDateUploaded()|date_format:$datetimeFormatShort}</td>
</tr>
</table>

<!-- / templates/controllers/grid/settings/library/form/fileInfo.tpl -->

