{**
 * templates/frontend/objects/monograph_dublinCore.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Print Dublin Core metadata for a monograph
 *
 * @uses $monograph Monograph The monograph to be displayed
 *}
<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />

{if $monograph->getSponsor(null)}{foreach from=$monograph->getSponsor(null) key=metaLocale item=metaValue}
	<meta name="DC.Contributor.Sponsor" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
{if $monograph->getCoverageSample(null)}{foreach from=$monograph->getCoverageSample(null) key=metaLocale item=metaValue}
	<meta name="DC.Coverage" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
{if $monograph->getCoverageGeo(null)}{foreach from=$monograph->getCoverageGeo(null) key=metaLocale item=metaValue}
	<meta name="DC.Coverage.spatial" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
{if $monograph->getCoverageChron(null)}{foreach from=$monograph->getCoverageChron(null) key=metaLocale item=metaValue}
	<meta name="DC.Coverage.temporal" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
{foreach from=$monograph->getAuthorString()|explode:", " item=dc_author}
	<meta name="DC.Creator.PersonalName" content="{$dc_author|escape}"/>
{/foreach}
{if is_a($monograph, 'PublishedMonograph') && $monograph->getDatePublished()}
	<meta name="DC.Date.created" scheme="ISO8601" content="{$monograph->getDatePublished()|date_format:"%Y-%m-%d"}"/>
{/if}
	<meta name="DC.Date.dateSubmitted" scheme="ISO8601" content="{$monograph->getDateSubmitted()|date_format:"%Y-%m-%d"}"/>
	<meta name="DC.Date.modified" scheme="ISO8601" content="{$monograph->getDateStatusModified()|date_format:"%Y-%m-%d"}"/>
{if $monograph->getAbstract(null)}{foreach from=$monograph->getAbstract(null) key=metaLocale item=metaValue}
	<meta name="DC.Description" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
	<meta name="DC.Identifier" content="{$monograph->getId()|escape}"/>
	<meta name="DC.Identifier.URI" content="{url page="catalog" op="book" path=$monograph->getId()}"/>
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
	<meta name="DC.Title" content="{$monograph->getLocalizedTitle()|strip_tags|escape}"/>
{foreach from=$monograph->getTitle(null) item=alternate key=metaLocale}
	{if $alternate != $monograph->getLocalizedTitle()}
		<meta name="DC.Title.Alternative" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$alternate|strip_tags|escape}"/>
	{/if}
{/foreach}
	<meta name="DC.Type" content="Text.Book"/>
