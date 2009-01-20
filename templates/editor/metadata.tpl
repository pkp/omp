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
<a name="metadata"></a>
<table class="data">
	<tr valign="middle">
		<td><h3>{translate key="submission.metadata"}</h3></td>
		<td>&nbsp;<br/><a href="{url op="viewMetadata" path=$submission->getMonographId()}" class="action">{translate key="submission.editMetadata"}</a></td>
	</tr>
</table>

<h4>{translate key="monograph.authors"}</h4>

{monographComponents}

{include file="inserts/monographComponents/monographComponentsInsert.tpl"}

<table width="100%" class="data">
	{foreach name=authors from=$authors item=author}
	<tr valign="top">
		<td width="20%" class="label">{translate key="user.name"}</td>
		<td width="80%" class="value">
			{assign var=emailString value="`$author->getFullName()` <`$author->getEmail()`>"}
			{url|assign:"url" page="user" op="email" redirectUrl=$currentUrl to=$emailString|to_array subject=$submission->getMonographTitle|strip_tags monographId=$submission->getMonographId()}
			{$author->getFullName()|escape} {icon name="mail" url=$url}
		</td>
	</tr>
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
	{if $currentPress->getSetting('requireAuthorCompetingInterests')}
	<tr valign="top">
		<td class="label">
			{url|assign:"competingInterestGuidelinesUrl" page="information" op="competingInterestGuidelines"}
			{translate key="author.competingInterests" competingInterestGuidelinesUrl=$competingInterestGuidelinesUrl}
		</td>
		<td class="value">{$author->getAuthorCompetingInterests()|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
	</tr>
	{/if}
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
	{/foreach}
</table>

<h4>{translate key="submission.titleAndAbstract"}</h4>

<table width="100%" class="data">
	<tr valign="top">
		<td width="20%" class="label">{translate key="monograph.title"}</td>
		<td width="80%" class="value">{$submission->getMonographTitle()|strip_unsafe_html|default:"&mdash;"}</td>
	</tr>

	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="monograph.abstract"}</td>
		<td class="value">{$submission->getMonographAbstract()|strip_unsafe_html|nl2br|default:"&mdash;"}</td>
	</tr>
</table>

<h4>{translate key="submission.indexing"}</h4>
	
<table width="100%" class="data">
	{if $journalSettings.metaDiscipline}
	<tr valign="top">
		<td width="20%" class="label">{translate key="monograph.discipline"}</td>
		<td width="80%" class="value">{$submission->getMonographDiscipline()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $journalSettings.metaSubjectClass}
	<tr valign="top">
		<td width="20%"  class="label">{translate key="monograph.subjectClassification"}</td>
		<td width="80%" class="value">{$submission->getMonographSubjectClass()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $journalSettings.metaSubject}
	<tr valign="top">
		<td width="20%"  class="label">{translate key="monograph.subject"}</td>
		<td width="80%" class="value">{$submission->getMonographSubject()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $journalSettings.metaCoverage}
	<tr valign="top">
		<td width="20%"  class="label">{translate key="monograph.coverageGeo"}</td>
		<td width="80%" class="value">{$submission->getMonographCoverageGeo()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="monograph.coverageChron"}</td>
		<td class="value">{$submission->getMonographCoverageChron()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	<tr valign="top">
		<td class="label">{translate key="monograph.coverageSample"}</td>
		<td class="value">{$submission->getMonographCoverageSample()|escape|default:"&mdash;"}</td>
	</tr>
	<tr>
		<td colspan="2" class="separator">&nbsp;</td>
	</tr>
	{/if}
	{if $journalSettings.metaType}
	<tr valign="top">
		<td width="20%"  class="label">{translate key="monograph.type"}</td>
		<td width="80%" class="value">{$submission->getMonographType()|escape|default:"&mdash;"}</td>
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
		<td width="80%" class="value">{$submission->getMonographSponsor()|escape|default:"&mdash;"}</td>
	</tr>
</table>
