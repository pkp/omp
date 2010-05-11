{**
 * metadata.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Subtemplate defining the submission metadata table.
 *
 * $Id$
 *}

{literal}
<script type="text/javascript">
<!--
function show(id) {
	var info = document.getElementById(id);
	if(info.style.display=='block') info.style.display='none';
	else info.style.display='block';
}
//-->
</script>
{/literal}

<div id="metadata">
<h3>{translate key="submission.metadata"}</h3>

<p><a href="{url op="viewMetadata" path=$submission->getId()}" class="action">{translate key="submission.editMetadata"}</a></p>

{if $submission->getWorkType() != WORK_TYPE_EDITED_VOLUME}
	<h4>{translate key="monograph.authors"}</h4>
{/if}

{assign var="authorIndex" value=0} 
{assign var="firstAuthor" value=false}
{assign var="authors" value=$submission->getAuthors()}

{iterate from=authors item=author}

	{if $submission->getWorkType() == WORK_TYPE_EDITED_VOLUME}
		{if $authorIndex == 0 && $author->getContributionType() == CONTRIBUTION_TYPE_VOLUME_EDITOR}
			<h4>{translate key="inserts.contributors.volumeEditors"}</h4>
		{/if}
		{if $firstAuthor == false && $author->getContributionType() != CONTRIBUTION_TYPE_VOLUME_EDITOR}
			<h4>{translate key="monograph.authors"}</h4>
			{assign var="firstAuthor" value=true}
		{/if}
	{/if}

	<div class="{if $authorIndex % 2}oddSideIndicator{else}evenSideIndicator{/if}">
		{assign var=emailString value=$author->getFullName()|concat:" <":$author->getEmail():">"}
		{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$emailString|to_array subject=$submission->getLocalizedTitle()|strip_tags monographId=$submission->getId()}
		&nbsp;<a href="javascript:show('authors-{$author->getId()|escape}-display')">{$author->getFullName()|escape}</a>&nbsp;{icon name="mail" url=$url}
		{if $author->getPrimaryContact()}<br />&nbsp;{translate key="author.submit.selectPrincipalContact"}{/if}
	<br />
	</div>
	<div id="authors-{$author->getId()|escape}-display" class="{if $authorIndex % 2}oddSideIndicator{else}evenSideIndicator{/if}" style="display:none">
		<table class="data">
		{if $author->getUrl()}
		<tr valign="top">
			<td class="label">{translate key="user.url"}</td>
			<td class="value"><a href="{$author->getUrl()|escape:"quotes"}">{$author->getUrl()|escape}</a></td>
		</tr>
		{/if}
		<tr valign="top">
			<td class="label">{translate key="user.affiliation"}</td>
			<td class="value">{$author->getAffiliation()|escape|nl2br|default:"&mdash;"}</td>
		</tr>
		<tr valign="top">
			<td class="label">{translate key="common.country"}</td>
			<td class="value">{$author->getCountryLocalized()|escape|default:"&mdash;"}</td>
		</tr>
		<tr valign="top">
			<td class="label">{translate key="user.biography"}</td>
			<td class="value">{$author->getAuthorBiography()|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
		</tr>
		{if !$smarty.foreach.authors.last}
		<tr>
			<td colspan="2" class="separator">&nbsp;</td>
		</tr>
		{/if}
		</table>
	</div>
	<br />
	{assign var="authorIndex" value=$authorIndex+1}
{/iterate}

<h4>{translate key="submission.titleAndAbstract"}</h4>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{translate key="monograph.title"}</td>
		<td width="80%" class="value">{$submission->getLocalizedTitle()|strip_unsafe_html|default:"&mdash;"}</td>
	</tr>

	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="monograph.description"}</td>
		<td class="value">{$submission->getLocalizedAbstract()|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
	</tr>
</table>

<h4>{translate key="submission.supportingAgencies"}</h4>
	
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{translate key="submission.agencies"}</td>
		<td width="80%" class="value">{$submission->getLocalizedSponsor()|escape|default:"&mdash;"}</td>
	</tr>
</table>
</div>
