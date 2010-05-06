{**
 * management.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the submission management table.
 *
 * $Id$
 *}
<div id="submission">
<h3>{translate key="manuscript.submission"}</h3>

{assign var="submissionFile" value=$submission->getSubmissionFile()}

<table width="100%" class="data">
	<tr>
		<td width="20%" class="label">{translate key="monograph.authors"}</td>
		<td width="80%" colspan="2" class="value">
			{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$submission->getAuthorEmails() subject=$submission->getLocalizedTitle() monographId=$submission->getId()}
			{$submission->getAuthorString()|escape} {icon name="mail" url=$url}
		</td>
	</tr>
	<tr>
		<td class="label">{translate key="monograph.title"}</td>
		<td colspan="2" class="value">{$submission->getLocalizedTitle()|strip_unsafe_html}</td>
	</tr>
	<tr>
		<td class="label">{translate key="submission.submitter"}</td>
		<td colspan="2" class="value">
			{assign var="submitter" value=$submission->getUser()}
			{assign var=emailString value=$submitter->getFullName()|concat:" <":$submitter->getEmail():">"}
			{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$emailString|to_array subject=$submission->getLocalizedTitle|strip_tags monographId=$submission->getId()}
			{$submitter->getFullName()|escape} {icon name="mail" url=$url}
		</td>
	</tr>
	<tr>
		<td class="label">{translate key="common.dateSubmitted"}</td>
		<td>{$submission->getDateSubmitted()|date_format:$dateFormatShort}</td>
	</tr>
	<tr>
		<td class="label">{translate key="submissions.series"}</td>
		<td class="value">{$submission->getSeriesAbbrev()|escape|default:"&mdash;"}</td>
		<td class="value">{if $series|@count > 0}<form action="{url op="updateSeries" path=$submission->getId()}" method="post">{translate key="submission.changeSeries"} <select name="series" size="1" class="selectMenu">{html_options options=$series selected=$submission->getSeriesId()}</select> <input type="submit" value="{translate key="common.record"}" class="button" /></form>{/if}</td>
	</tr>
	{if $submission->getCommentsToEditor()}
	<tr valign="top">
		<td width="20%" class="label">{translate key="monograph.commentsToEditor"}</td>
		<td width="80%" colspan="2" class="data">{$submission->getCommentsToEditor()|strip_unsafe_html|nl2br}</td>
	</tr>
	{/if}
</table>
</div>
