{**
 * peerReview.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the peer review table.
 *
 * $Id$
 *}
<div id="submission">
<h3>{translate key="manuscript.submission"}</h3>

<table width="100%" class="data">
	<tr>
		<td width="20%" class="label">{translate key="monograph.authors"}</td>
		<td width="80%">
			{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$submission->getAuthorEmails() subject=$submission->getLocalizedTitle() monographId=$submission->getMonographId()}
			{$submission->getAuthorString()|escape} {icon name="mail" url=$url}
		</td>
	</tr>
	<tr>
		<td class="label">{translate key="monograph.title"}</td>
		<td>{$submission->getLocalizedTitle()|strip_unsafe_html}</td>
	</tr>
	<tr>
		<td class="label">{translate key="section.section"}</td>
		<td>{*$submission->getSectionTitle()|escape*}</td>
	</tr>
	<tr>
		<td class="label">{translate key="user.role.editor"}</td>
		<td>
			{assign var=editAssignments value=$submission->getEditAssignments()}
			{foreach from=$editAssignments item=editAssignment}
				{assign var=emailString value="`$editAssignment->getEditorFullName()` <`$editAssignment->getEditorEmail()`>"}
				{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$emailString|to_array subject=$submission->getLocalizedTitle|strip_tags monographId=$submission->getMonographId()}
				{$editAssignment->getEditorFullName()|escape} {icon name="mail" url=$url}
				{if !$editAssignment->getCanEdit() || !$editAssignment->getCanReview()}
					{if $editAssignment->getCanEdit()}
						({translate key="submission.editing"})
					{else}
						({translate key="submission.review"})
					{/if}
				{/if}
				<br/>
			{foreachelse}
				{translate key="common.noneAssigned"}
			{/foreach}
		</td>
	</tr>
	<tr valign="top">
		<td class="label" width="20%">{translate key="submission.reviewVersion"}</td>
		{if $reviewFile}
			<td width="80%" class="value">
				<a href="{url op="downloadFile" path=$submission->getMonographId()|to_array:$reviewFile->getFileId():$reviewFile->getRevision()}" class="file">{$reviewFile->getFileName()|escape}</a>&nbsp;&nbsp;
				{$reviewFile->getDateModified()|date_format:$dateFormatShort}{if $currentJournal->getSetting('showEnsuringLink')}&nbsp;&nbsp;&nbsp;&nbsp;<a class="action" href="javascript:openHelp('{get_help_id key="editorial.sectionEditorsRole.review.blindPeerReview" url="true"}')">{translate key="reviewer.monograph.ensuringBlindReview"}</a>{/if}
			</td>
		{else}
			<td width="80%" class="nodata">{translate key="common.none"}</td>
		{/if}
	</tr>
	<tr valign="top">
		<td>&nbsp;</td>
		<td>
			<form method="post" action="{url op="uploadReviewVersion"}" enctype="multipart/form-data">
				{translate key="editor.monograph.uploadReviewVersion"}
				<input type="hidden" name="monographId" value="{$submission->getMonographId()}" />
				<input type="file" name="upload" class="uploadField" />
				<input type="submit" name="submit" value="{translate key="common.upload"}" class="button" />
			</form>
		</td>
	</tr>
	{foreach from=$suppFiles item=suppFile}
		<tr valign="top">
			{if !$notFirstSuppFile}
				<td class="label" rowspan="{$suppFiles|@count}">{translate key="monograph.suppFilesAbbrev"}</td>
				{assign var=notFirstSuppFile value=1}
			{/if}
			<td width="80%" class="value nowrap">
				<form method="post" action="{url op="setSuppFileVisibility"}">
				<input type="hidden" name="monographId" value="{$submission->getMonographId()}" />
				<input type="hidden" name="fileId" value="{$suppFile->getSuppFileId()}" />

				<a href="{url op="downloadFile" path=$submission->getMonographId()|to_array:$suppFile->getFileId():$suppFile->getRevision()}" class="file">{$suppFile->getFileName()|escape}</a>&nbsp;&nbsp;
				{$suppFile->getDateModified()|date_format:$dateFormatShort}&nbsp;&nbsp;
				<label for="show">{translate key="editor.monograph.showSuppFile"}</label>
				<input type="checkbox" name="show" id="show" value="1"{if $suppFile->getShowReviewers()==1} checked="checked"{/if}/>
				<input type="submit" name="submit" value="{translate key="common.record"}" class="button" />
				</form>
			</td>
		</tr>
	{foreachelse}
	<tr valign="top">
		<td class="label">{translate key="monograph.suppFilesAbbrev"}</td>
		<td class="nodata">{translate key="common.none"}</td>
	</tr>
{/foreach}
</table>

<div class="separator"></div>
</div>

<div id="peerReview">
{include file="acquisitionsEditor/submission/internalReview.tpl"}
<div class="separator"></div>
{include file="acquisitionsEditor/submission/externalReview.tpl"}
</div>