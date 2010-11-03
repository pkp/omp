{**
 * fileInfo.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files grid form
 *}

<input type="hidden" name="newUpload" value="1" />
<input type="hidden" name="fileId" value="{$attachmentFile->getId()}" />
<table id="fileInfo" class="data" width="100%">
<tr valign="top">
	<td width="30%" class="label">{translate key="common.originalFileName"}</td>
	<td width="70%" class="value">{$attachmentFile->getOriginalFileName()|escape}</a></td>
</tr>
<tr valign="top">
	<td width="30%" class="label">{translate key="common.fileName"}</td>
	<td width="70%" class="value">{$attachmentFile->getFileName()|escape}</a></td>
</tr>
<tr valign="top">
	<td width="30%" class="label">{translate key="common.fileSize"}</td>
	<td width="70%" class="value">{$attachmentFile->getNiceFileSize()}</td>
</tr>
<tr valign="top">
	<td width="30%" class="label">{translate key="common.dateUploaded"}</td>
	<td width="70%" class="value">{$attachmentFile->getDateUploaded()|date_format:$datetimeFormatShort}</td>
</tr>
</table>

