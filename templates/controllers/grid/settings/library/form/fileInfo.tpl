{**
 * fileInfo.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Information listing for library file
 *}

<input type="hidden" name="fileId" value="{$libraryFile->getId()|escape}" />
<table id="fileInfo" class="data" width="100%">
<tr valign="top">
	<td width="20%" class="label">{translate key="common.fileName"}</td>
	<td width="80%" class="value">{$libraryFile->getFileName()|escape}</a></td>
</tr>
<tr valign="top">
	<td class="label">{translate key="common.fileSize"}</td>
	<td class="value">{$libraryFile->getNiceFileSize()}</td>
</tr>
<tr valign="top">
	<td class="label">{translate key="common.dateUploaded"}</td>
	<td class="value">{$libraryFile->getDateUploaded()|date_format:$datetimeFormatShort}</td>
</tr>
</table>

