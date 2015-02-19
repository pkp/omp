{**
 * templates/catalog/book/dublincore.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Dublin Core metadata elements for published monographs.
 *
 *}
<link rel="schema.DC" href="http://purl.org/dc/elements/1.1/" />

{if $publishedMonograph->getSponsor(null)}{foreach from=$publishedMonograph->getSponsor(null) key=metaLocale item=metaValue}
	<meta name="DC.Contributor.Sponsor" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
{if $publishedMonograph->getCoverageSample(null)}{foreach from=$publishedMonograph->getCoverageSample(null) key=metaLocale item=metaValue}
	<meta name="DC.Coverage" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
{if $publishedMonograph->getCoverageGeo(null)}{foreach from=$publishedMonograph->getCoverageGeo(null) key=metaLocale item=metaValue}
	<meta name="DC.Coverage.spatial" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
{if $publishedMonograph->getCoverageChron(null)}{foreach from=$publishedMonograph->getCoverageChron(null) key=metaLocale item=metaValue}
	<meta name="DC.Coverage.temporal" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
{foreach from=$publishedMonograph->getAuthorString()|explode:", " item=dc_author}
	<meta name="DC.Creator.PersonalName" content="{$dc_author|escape}"/>
{/foreach}
{if is_a($publishedMonograph, 'PublishedMonograph') && $publishedMonograph->getDatePublished()}
	<meta name="DC.Date.created" scheme="ISO8601" content="{$publishedMonograph->getDatePublished()|date_format:"%Y-%m-%d"}"/>
{/if}
	<meta name="DC.Date.dateSubmitted" scheme="ISO8601" content="{$publishedMonograph->getDateSubmitted()|date_format:"%Y-%m-%d"}"/>
	<meta name="DC.Date.modified" scheme="ISO8601" content="{$publishedMonograph->getDateStatusModified()|date_format:"%Y-%m-%d"}"/>
{if $publishedMonograph->getAbstract(null)}{foreach from=$publishedMonograph->getAbstract(null) key=metaLocale item=metaValue}
	<meta name="DC.Description" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$metaValue|strip_tags|escape}"/>
{/foreach}{/if}
	<meta name="DC.Identifier" content="{$publishedMonograph->getId()|escape}"/>
	<meta name="DC.Identifier.URI" content="{url page="catalog" op="book" path=$publishedMonograph->getId()}"/>
	<meta name="DC.Language" scheme="ISO639-1" content="{$publishedMonograph->getLocale()|truncate:2:''}"/>
	<meta name="DC.Source" content="{$currentPress->getLocalizedName()|strip_tags|escape}"/>
	<meta name="DC.Source.URI" content="{url press=$currentPress->getPath()}"/>
{if $publishedMonograph->getSubject(null)}{foreach from=$publishedMonograph->getSubject(null) key=metaLocale item=metaValue}
	{foreach from=$metaValue|explode:"; " item=dcSubject}
		{if $dcSubject}
			<meta name="DC.Subject" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$dcSubject|escape}"/>
		{/if}
	{/foreach}
{/foreach}{/if}
	<meta name="DC.Title" content="{$publishedMonograph->getLocalizedTitle()|strip_tags|escape}"/>
{foreach from=$publishedMonograph->getTitle(null) item=alternate key=metaLocale}
	{if $alternate != $publishedMonograph->getLocalizedTitle()}
		<meta name="DC.Title.Alternative" xml:lang="{$metaLocale|String_substr:0:2|escape}" content="{$alternate|strip_tags|escape}"/>
	{/if}
{/foreach}
	<meta name="DC.Type" content="Text.Book"/>
