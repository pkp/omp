{**
 * metadata_view.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * View (but not edit) metadata of a monograph.
 *
 * $Id$
 *}
{if !$contentOnly}
	{strip}
	{assign var="pageTitle" value="submission.viewMetadata"}
	{include file="common/header.tpl"}
	{/strip}
{/if}

{if $canViewAuthors}
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

{if $workType != WORK_TYPE_EDITED_VOLUME}
	<h4>{translate key="monograph.authors"}</h4>
{/if}

{assign var="authorIndex" value=0} 
{assign var="firstAuthor" value=false}

{foreach name=authors from=$contributors item=author}

	{if $workType == WORK_TYPE_EDITED_VOLUME}
		{if $authorIndex == 0 && $author.contributionType == CONTRIBUTION_TYPE_VOLUME_EDITOR}
			<h4>{translate key="inserts.contributors.volumeEditors"}</h4>
		{/if}
		{if $firstAuthor == false && $author.contributionType != CONTRIBUTION_TYPE_VOLUME_EDITOR}
			<h4>{translate key="monograph.authors"}</h4>
			{assign var="firstAuthor" value=true}
		{/if}
	{/if}

	<div class="{if $authorIndex % 2}oddSideIndicator{else}evenSideIndicator{/if}">
		{assign var=emailString value=$author.fullName|concat:" <":$author.email:">"}
		{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$emailString|to_array subject=$title[$formLocale]|strip_tags monographId=$monographId}
		&nbsp;<a href="javascript:show('authors-{$author.authorId|escape}-display')">{$author.fullName}</a>&nbsp;{icon name="mail" url=$url}
		{if $primaryContact == $author.authorId}<br />&nbsp;{translate key="submission.submit.selectPrincipalContact"}{/if}
	<br />
	</div>
	<div id="authors-{$author.authorId|escape}-display" class="{if $authorIndex % 2}oddSideIndicator{else}evenSideIndicator{/if}" style="display:none">
		<table class="data">
		{if $author.url}
		<tr valign="top">
			<td class="label">{translate key="user.url"}</td>
			<td class="value"><a href="{$author.url|escape:"quotes"}">{$author.url()|escape}</a></td>
		</tr>
		{/if}
		<tr valign="top">
			<td class="label">{translate key="user.affiliation"}</td>
			<td class="value">{$author.affiliation.$formLocale|escape|nl2br|default:"&mdash;"}</td>
		</tr>
		<tr valign="top">
			<td class="label">{translate key="common.country"}</td>
			<td class="value">{$author.country|escape|default:"&mdash;"}</td>
		</tr>
		<tr valign="top">
			<td class="label">{translate key="user.biography"}</td>
			<td class="value">{$author.biography.$formLocale|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
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
{/foreach}

<div class="separator"></div>
{/if}


<h3>{translate key="submission.titleAndAbstract"}</h3>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{translate key="monograph.title"}</td>
		<td width="80%" class="value">{$title[$formLocale]|strip_unsafe_html|default:"&mdash;"}</td>
	</tr>

	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="monograph.description"}</td>
		<td class="value">{$abstract[$formLocale]|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
	</tr>
</table>

<div class="separator"></div>

<h3>{translate key="submission.supportingAgencies"}</h3>
	
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{translate key="submission.agencies"}</td>
		<td width="80%" class="value">{$sponsor[$formLocale]|escape|default:"&mdash;"}</td>
	</tr>
</table>

{if !$contentOnly}
	{include file="common/footer.tpl"}
{/if}
