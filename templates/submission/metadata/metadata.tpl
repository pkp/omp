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
<div id="metadata">
<h3>{translate key="submission.metadata"}</h3>

{if $canEditMetadata}
	<p><a href="{url op="viewMetadata" path=$submission->getMonographId()}" class="action">{translate key="submission.editMetadata"}</a></p>
{/if}

<h4>{translate key="monograph.authors"}</h4>
	
<table width="100%" class="data">
	{foreach name=authors from=$submission->getAuthors() item=author}
	<tr valign="top">
		<td width="20%" class="label">{translate key="user.name"}</td>
		<td width="80%" class="value">
			{assign var=emailString value="`$author->getFullName()` <`$author->getEmail()`>"}
			{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$emailString|to_array subject=$submission->getLocalizedTitle()|strip_tags monographId=$submission->getMonographId()}
			{$author->getFullName()|escape} {icon name="mail" url=$url}
		</td>
	</tr>
	{if $author->getUrl()}
		<tr valign="top">
			<td class="label">{translate key="user.url"}</td>
			<td class="value"><a href="{$author->getUrl()|escape:"quotes"}">{$author->getUrl()|escape}</a></td>
		</tr>
	{/if}
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
		<td class="value">{$author->getAuthorBiography()|nl2br|strip_unsafe_html|default:"&mdash;"}</td>
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
	{/foreach}
</table>

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
	{if $currentPress->getSetting('metaDiscipline')}
	<tr valign="top">
		<td width="20%" class="label">{translate key="monograph.discipline"}</td>
		<td width="80%" class="value">{$submission->getLocalizedDiscipline()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $currentPress->getSetting('metaSubjectClass')}
	<tr valign="top">
		<td width="20%"  class="label">{translate key="monograph.subjectClassification"}</td>
		<td width="80%" class="value">{$submission->getLocalizedSubjectClass()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $currentPress->getSetting('metaSubject')}
	<tr valign="top">
		<td width="20%"  class="label">{translate key="monograph.subject"}</td>
		<td width="80%" class="value">{$submission->getLocalizedSubject()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $currentPress->getSetting('metaCoverage')}
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
	{if $currentPress->getSetting('metaType')}
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
</div>
