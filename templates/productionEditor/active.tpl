{**
 * active.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show the details of active submissions.
 *
 * $Id$
 *}

<div id="submissions">
<table class="listing" width="100%">
	<tr><td colspan="6" class="headseparator">&nbsp;</td></tr>
	<tr class="heading" valign="bottom">
		<td width="5%">{translate key="common.id"}</td>
		<td width="5%"><span class="disabled">MM-DD</span><br />{translate key="submissions.submit"}</td>
		<td width="5%">{translate key="submissions.series"}</td>
		<td width="25%">{translate key="monograph.authors"}</td>
		<td width="35%">{translate key="monograph.title"}</td>
	</tr>
	<tr><td colspan="6" class="headseparator">&nbsp;</td></tr>

{iterate from=submissions item=submission}
	{assign var="monographId" value=$submission->getId()}
	{assign var="progress" value=$submission->getSubmissionProgress()}

	<tr valign="top">
		<td>{$monographId|escape}</td>
		<td>{if $submission->getDateSubmitted()}{$submission->getDateSubmitted()|date_format:$dateFormatTrunc}{else}&mdash;{/if}</td>
		<td>{$submission->getSeriesAbbrev()|escape}</td>
		<td>{$submission->getAuthorString(true)|truncate:40:"..."|escape}</td>
		<td><a href="{url op="submission" path=$monographId}" class="action">{if $submission->getLocalizedTitle()}{$submission->getLocalizedTitle()|strip_unsafe_html|truncate:60:"..."}{else}{translate key="common.untitled"}{/if}</a></td>
	</tr>

	<tr>
		<td colspan="6" class="{if $submissions->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $submissions->wasEmpty()}
	<tr>
		<td colspan="6" class="nodata">{translate key="submissions.noSubmissions"}</td>
	</tr>
	<tr>
		<td colspan="6" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td colspan="4" align="left">{page_info iterator=$submissions}</td>
		<td colspan="2" align="right">{page_links anchor="submissions" name="submissions" iterator=$submissions}</td>
	</tr>
{/if}
</table>
</div>
