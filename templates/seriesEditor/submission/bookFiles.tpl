<!-- templates/seriesEditor/submission/bookFiles.tpl -->

{**
 * bookFiles.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Book files summary.
 *
 * $Id$
 *}

<div id="bookFiles">
<h3>{translate key="common.bookFiles"}</h3>
<p>{translate key="submission.selectFilesForReview"}</p>
<form method="post" action="{url op="recordReviewFiles"}">
<input type="hidden" name="monographId" value="{$submission->getId()}" />
<table class="listing" width="100%">
<tr valign="top">
	<td width="5%">&nbsp;</td>
	<td width="35%">{translate key="common.fileName"}</td>
	<td width="10%">{translate key="common.note"}</td>
	<td width="35%">{translate key="common.type"}</td>
	<td width="15%">{translate key="common.fileSize"}</td>
</tr>
<tr>
	<td class="separator" colspan="6">&nbsp;</td>
</tr>
{foreach from=$submissionFiles item=submissionFile}
<tr valign="top">
	<td><input type="checkbox" name="selectedFiles[]" value="{$submissionFile->getFileId()}" /></td>
	<td><a href="{url op="download" path=$submission->getId()|to_array:$submissionFile->getFileId():$submissionFile->getRevision()}">{$submissionFile->getFileName()|escape}</a></td>
	<td>{icon name="comment" disabled="disabled"}</td>
	<td>
		{assign var="assocObject" value=$submissionFile->getAssocObject()}
		{$assocObject->getLocalizedName()|escape}
	</td>
	<td>{$submissionFile->getNiceFileSize()}</td>
</tr>
{foreachelse}
<tr valign="top">
	<td colspan="6" class="nodata"><em>{translate key="common.none"}</em></td>
</tr>
{/foreach}
<tr>
	<td class="separator" colspan="6">&nbsp;</td>
</tr>
</table>
<input type="submit" class="button" name="recordReviewFiles" value="{translate key="common.record"}" />
</form>

</div>

<!-- / templates/seriesEditor/submission/bookFiles.tpl -->

