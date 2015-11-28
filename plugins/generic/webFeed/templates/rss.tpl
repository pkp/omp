{**
 * plugins/generic/webFeed/templates/rss.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * RSS feed template
 *
 *}
<?xml version="1.0" encoding="{$defaultCharset|escape}"?>
<rdf:RDF
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns="http://purl.org/rss/1.0/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:prism="http://prismstandard.org/namespaces/1.2/basic/"
	xmlns:cc="http://web.resource.org/cc/">

	<channel rdf:about="{url press=$press->getPath()}">
		{* required elements *}
		<title>{$press->getLocalizedName()|strip|escape:"html"}</title>
		<link>{url press=$press->getPath()}</link>

		{if $press->getLocalizedDescription()}
			{assign var="description" value=$press->getLocalizedDescription()}
		{elseif $press->getLocalizedSetting('searchDescription')}
			{assign var="description" value=$press->getLocalizedSetting('searchDescription')}
		{/if}

		<description>{$description|strip|escape:"html"}</description>

		{* optional elements *}
		{assign var="publisherInstitution" value=$press->getSetting('publisherInstitution')}
		{if $publisherInstitution}
			<dc:publisher>{$publisherInstitution|strip|escape:"html"}</dc:publisher>
		{/if}

		{if $press->getPrimaryLocale()}
			<dc:language>{$press->getPrimaryLocale()|replace:'_':'-'|strip|escape:"html"}</dc:language>
		{/if}

		<prism:publicationName>{$press->getLocalizedName()|strip|escape:"html"}</prism:publicationName>

		{if $press->getSetting('printIssn')}
			{assign var="ISSN" value=$press->getSetting('printIssn')}
		{elseif $press->getSetting('onlineIssn')}
			{assign var="ISSN" value=$press->getSetting('onlineIssn')}
		{/if}

		{if $ISSN}
			<prism:issn>{$ISSN|escape}</prism:issn>
		{/if}

		{if $press->getLocalizedSetting('copyrightNotice')}
			<prism:copyright>{$press->getLocalizedSetting('copyrightNotice')|strip|escape:"html"}</prism:copyright>
		{/if}

		<items>
			<rdf:Seq>
				{foreach from=$publishedMonographs item=publishedMonograph}
					<rdf:li rdf:resource="{url page="catalog" op="book" path=$publishedMonograph->getId()}"/>
				{/foreach}{* publishedMonographs *}
			</rdf:Seq>
		</items>
	</channel>

{foreach name=publishedMonographs from=$publishedMonographs item=publishedMonograph}
	<item rdf:about="{url page="catalog" op="book" path=$publishedMonograph->getId()}">

		{* required elements *}
		<title>{$publishedMonograph->getLocalizedTitle()|strip|escape:"html"}</title>
		<link>{url page="catalog" op="book" path=$publishedMonograph->getId()}</link>

		{* optional elements *}
		{if $publishedMonograph->getLocalizedAbstract()}
			<description>{$publishedMonograph->getLocalizedAbstract()|strip|escape:"html"}</description>
		{/if}

		{foreach from=$publishedMonograph->getAuthors() item=author name=authorList}
			<dc:creator>{$author->getFullName()|strip|escape:"html"}</dc:creator>
		{/foreach}

		<dc:rights>
			{translate|escape key="submission.copyrightStatement" copyrightYear=$publishedMonograph->getCopyrightYear() copyrightHolder=$publishedMonograph->getLocalizedCopyrightHolder()}
			{$publishedMonograph->getLicenseURL()|escape}
		</dc:rights>

		<dc:date>{$publishedMonograph->getDatePublished()|date_format:"%Y-%m-%d"}</dc:date>
		<prism:publicationDate>{$publishedMonograph->getDatePublished()|date_format:"%Y-%m-%d"}</prism:publicationDate>
	</item>
{/foreach}{* publishedMonographs *}

</rdf:RDF>

