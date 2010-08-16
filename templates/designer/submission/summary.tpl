<!-- templates/designer/submission/summary.tpl -->

{**
 * summary.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the layout editor's submission summary table.
 *
 * $Id$
 *}
<div id="submission">
<h3>{translate key="manuscript.submission"}</h3>

<table width="100%" class="data">
	<tr>
		<td width="20%" class="label">{translate key="monograph.authors"}</td>
		<td width="80%">
			{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$submission->getAuthorEmails() subject=$submission->getLocalizedTitle() monographId=$submission->getId()}
			{$submission->getAuthorString()|escape} {icon name="mail" url=$url}
		</td>
	</tr>
	<tr>
		<td class="label">{translate key="monograph.title"}</td>
		<td>{$submission->getLocalizedTitle()|strip_unsafe_html}</td>
	</tr>
	<tr>
		<td class="label">{translate key="submissions.series"}</td>
		<td>{$submission->getSeriesTitle()|escape}</td>
	</tr>
</table>
</div>
<!-- / templates/designer/submission/summary.tpl -->

