{**
 * templates/frontend/objects/monograph_dublinCore.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Dublin Core metadata for a chapter
 *
 * @uses $monograph Monograph The monograph this file belongs to
 * @uses $publicationFormat PublictionFormat The publication format this file belongs to
 * @uses $submissionFile SubmissionFile The submission file to be presented
 * @uses $chapter Chapter The (optional) chapter associated with this file
 *}
<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />

{if $monograph->getSponsor(null)}{foreach from=$monograph->getSponsor(null) key=metaLocale item=metaValue}
	<meta name="DC.Contributor.Sponsor" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
{if $monograph->getCoverage(null)}{foreach from=$monograph->getCoverage(null) key=metaLocale item=metaValue}
	<meta name="DC.Coverage" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}

{* Authors *}
{if $chapter}
	{* Only include metadata for authors associated with this chapter *}
	{assign var=authors value=$chapter->getAuthors()}
	{assign var=authors value=$authors->toArray()}
{else}
	{* Include metadata for all authors of the submission *}
	{assign var=authors value=$monograph->getAuthors()}
{/if}
{foreach from=$authors item=author}
	<meta name="DC.Creator.PersonalName" content="{$author->getLastName()|escape}, {$author->getFirstName()|escape}{if $author->getMiddleName()} {$author->getMiddleName()|escape}{/if}"/>
{/foreach}

{if is_a($monograph, 'PublishedSubmission') && $monograph->getDatePublished()}
	<meta name="DC.Date.created" scheme="ISO8601" content="{$monograph->getDatePublished()|date_format:"%Y-%m-%d"}"/>
{/if}
<meta name="DC.Date.dateSubmitted" scheme="ISO8601" content="{$monograph->getDateSubmitted()|date_format:"%Y-%m-%d"}"/>
<meta name="DC.Date.modified" scheme="ISO8601" content="{$monograph->getDateStatusModified()|date_format:"%Y-%m-%d"}"/>
{if $monograph->getAbstract(null)}{foreach from=$monograph->getAbstract(null) key=metaLocale item=metaValue}
	<meta name="DC.Description" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
<meta name="DC.Identifier" content="{$monograph->getId()|escape}/{$publicationFormat->getId()|escape}/{$submissionFile->getFileIdAndRevision()|escape}"/>
<meta name="DC.Identifier.URI" content="{url page="catalog" op="book" path=$monograph->getId()|to_array:$publicationFormat->getId():$submissionFile->getFileIdAndRevision()}"/>
<meta name="DC.Language" scheme="ISO639-1" content="{$monograph->getLocale()|truncate:2:''}"/>
<meta name="DC.Source" content="{$currentPress->getLocalizedName()|strip_tags|escape}"/>
<meta name="DC.Source.URI" content="{url press=$currentPress->getPath()}"/>
{if $monograph->getSubject(null)}{foreach from=$monograph->getSubject(null) key=metaLocale item=metaValue}
	{foreach from=$metaValue|explode:"; " item=dcSubject}
		{if $dcSubject}
			<meta name="DC.Subject" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$dcSubject|escape}"/>
		{/if}
	{/foreach}
{/foreach}{/if}
{if $chapter}
	<meta name="DC.Title" content="{$chapter->getLocalizedTitle()|escape}"/>
	{foreach from=$chapter->getTitle(null) item=alternate key=metaLocale}
		{if $alternate != $chapter->getLocalizedTitle()}
		<meta name="DC.Title.Alternative" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$alternate|strip_tags|escape}"/>
	{/if}
	{/foreach}
{else}
	<meta name="DC.Title" content="{$monograph->getLocalizedTitle()|escape}"/>
	{foreach from=$monograph->getTitle(null) item=alternate key=metaLocale}
		{if $alternate != $monograph->getLocalizedTitle()}
		<meta name="DC.Title.Alternative" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$alternate|strip_tags|escape}"/>
	{/if}
	{/foreach}
{/if}
{if $chapter}
	<meta name="DC.Type" content="Text.Chapter"/>
{else}
	<meta name="DC.Type" content="Text.Book"/>
{/if}
