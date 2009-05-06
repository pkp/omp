{**
 * metadata.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
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

<a name="metadata"></a>
<table class="data">
	<tr valign="middle">
		<td><h3>{translate key="submission.metadata"}</h3></td>
		<td>&nbsp;<br/><!--<a href="{url op="viewMetadata" path=$submission->getMonographId()}" class="action">{translate key="submission.editMetadata"}</a>--></td>
	</tr>
</table>

<h4>{translate key="monograph.authors"}</h4>

	{assign var="authorIndex" value=0} 
	<div style="border:1px solid #E0E0E0">
	{foreach name=authors from=$authors item=author}
		{assign var="authorIndex" value=$authorIndex+1}

		<div style="background-color:{if $authorIndex % 2}#FFFFFF{else}#E0E0E0{/if}">
			{assign var=emailString value="`$author->getFullName()` <`$author->getEmail()`>"}
			{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$emailString|to_array subject=$submission->getLocalizedTitle()|strip_tags monographId=$submission->getMonographId()}
			<a style="text-decoration:none" href="javascript:show('authors-{$author->getAuthorId()|escape}-display')">(+){$author->getFullname()}</a>&nbsp;{icon name="mail" url=$url}
		<br />
		</div>

		<div id="authors-{$author->getAuthorId()|escape}-display" style="display:none;border-left:1px solid black;padding-left:10px;background-color:{if $authorIndex % 2}#FFFFFF{else}#E0E0E0{/if}">
			<table class="data">

			{if $author->getEmail()}<tr valign="top">
				<td class="label">{translate key="user.url"}</td>
				<td class="value"><a href="{$author->getUrl()|escape:"quotes"}">{$author->getUrl()|escape}</a></td>
			</tr>{/if}
			<tr valign="top">
				<td class="label">{translate key="user.affiliation"}</td>
				<td class="value">{$author->getAffiliation()|escape|default:"&mdash;"}</td>
			</tr>
			<tr valign="top">
				<td class="label">{translate key="common.country"}</td>
				<td class="value">{$author->getCountryLocalized()|escape|default:"&mdash;"}</td>
			</tr>
			<tr valign="top">
				<td class="label">{translate key="user.biography"}</td>
				<td class="value">{$author->getAuthorBiography()|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
			</tr>
			{if $author->getPrimaryContact()}
			<tr valign="top">
				<td colspan="2" class="label">{translate key="author.submit.selectPrincipalContact"}</td>
			</tr>
			{/if}
			{if !$smarty.foreach.authors.last}
			<tr>
				<td colspan="2" class="separator">&nbsp;</td>
			</tr>
			{/if}
			</table>
		</div>
	{/foreach}
	</div>

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
		<td class="label">{translate key="monograph.abstract"}</td>
		<td class="value">{$submission->getLocalizedAbstract()|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
	</tr>
</table>

<h4>{translate key="submission.indexing"}</h4>
	
<table width="100%" class="data">
	{if $journalSettings.metaDiscipline}
	<tr valign="top">
		<td width="20%" class="label">{translate key="monograph.discipline"}</td>
		<td width="80%" class="value">{$submission->getLocalizedDiscipline()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $journalSettings.metaSubjectClass}
	<tr valign="top">
		<td width="20%"  class="label">{translate key="monograph.subjectClassification"}</td>
		<td width="80%" class="value">{$submission->getLocalizedSubjectClass()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $journalSettings.metaSubject}
	<tr valign="top">
		<td width="20%"  class="label">{translate key="monograph.subject"}</td>
		<td width="80%" class="value">{$submission->getLocalizedSubject()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $journalSettings.metaCoverage}
	<tr valign="top">
		<td width="20%"  class="label">{translate key="monograph.coverageGeo"}</td>
		<td width="80%" class="value">{$submission->getLocalizedCoverageGeo()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="monograph.coverageChron"}</td>
		<td class="value">{$submission->getLocalizedCoverageChron()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="monograph.coverageSample"}</td>
		<td class="value">{$submission->getLocalizedCoverageSample()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $journalSettings.metaType}
	<tr valign="top">
		<td width="20%"  class="label">{translate key="monograph.type"}</td>
		<td width="80%" class="value">{$submission->getLocalizedType()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	<tr valign="top">
		<td width="20%" class="label">{translate key="monograph.language"}</td>
		<td width="80%" class="value">{$submission->getLanguage()|escape|default:"&mdash;"}</td>
	</tr>
</table>

<h4>{translate key="submission.supportingAgencies"}</h4>
	
<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{translate key="author.submit.agencies"}</td>
		<td width="80%" class="value">{$submission->getLocalizedSponsor()|escape|default:"&mdash;"}</td>
	</tr>
</table>
