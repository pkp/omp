{**
 * management.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the author's submission management table.
 *
 * $Id$
 *}
<div id="submission">
<h3>{translate key="manuscript.submission"}</h3>
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{translate key="monograph.authors"}</td>
		<td width="80%" colspan="2" class="data">{$submission->getAuthorString(false)|escape}</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{translate key="monograph.title"}</td>
		<td width="80%" colspan="2" class="data">{$submission->getLocalizedTitle()|strip_unsafe_html}</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{translate key="submission.originalFile"}</td>
		<td width="80%" colspan="2" class="data">
			{if $submissionFile}
				<a href="{url op="downloadFile" path=$submission->getId()|to_array:$submissionFile->getFileId():$submissionFile->getRevision()}" class="file">{$submissionFile->getFileName()|escape}</a>&nbsp;&nbsp;{$submissionFile->getDateModified()|date_format:$dateFormatShort}
			{else}
				{translate key="common.none"}
			{/if}
		</td>
	</tr>
	<tr>
		<td class="label">{translate key="submission.submitter"}</td>
		<td colspan="2" class="value">
			{assign var="submitter" value=$submission->getUser()}
			{assign var=emailString value=$submitter->getFullName()|concat:" <":$submitter->getEmail():">"}
			{url|assign:"url" page="user" op="email" to=$emailString|to_array redirectUrl=$currentUrl subject=$submission->getMonographTitle|strip_tags monographId=$submission->getId()}
			{$submitter->getFullName()|escape} {icon name="mail" url=$url}
		</td>
	</tr>
	<tr>
		<td class="label">{translate key="common.dateSubmitted"}</td>
		<td>{$submission->getDateSubmitted()|date_format:$datetimeFormatLong}</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{translate key="submissions.series"}</td>
		<td width="80%" colspan="2" class="data">{$submission->getSeriesTitle()|escape|default:"&mdash;"}</td>
	</tr>
	<tr valign="top">
		<td width="20%" class="label">{translate key="user.role.editor"}</td>
		{assign var="editAssignments" value=$submission->getEditAssignments()}
		<td width="80%" colspan="2" class="data">
			{foreach from=$editAssignments item=editAssignment}
				{assign var=emailString value=$editAssignment->getEditorFullName()|concat:" <":$editAssignment->getEditorEmail():">"}
				{url|assign:"url" page="user" op="email" to=$emailString|to_array redirectUrl=$currentUrl subject=$submission->getMonographTitle|strip_tags monographId=$submission->getId()}
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
	{if $submission->getCommentsToEditor()}
	<tr valign="top">
		<td width="20%" class="label">{translate key="monograph.commentsToEditor"}</td>
		<td width="80%" colspan="2" class="data">{$submission->getCommentsToEditor()|strip_unsafe_html|nl2br}</td>
	</tr>
	{/if}

</table>
</div>

