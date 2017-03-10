{**
 * plugins/generic/webFeed/templates/rss2.tpl
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * RSS 2 feed template
 *
 *}
<?xml version="1.0" encoding="{$defaultCharset|escape}"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
	<channel>
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
		{if $press->getPrimaryLocale()}
			<language>{$press->getPrimaryLocale()|replace:'_':'-'|strip|escape:"html"}</language>
		{/if}

		{if $press->getLocalizedSetting('copyrightNotice')}
			<copyright>{$press->getLocalizedSetting('copyrightNotice')|strip|escape:"html"}</copyright>
		{/if}

		{if $press->getSetting('contactEmail')}
			<managingEditor>{$press->getSetting('contactEmail')|strip|escape:"html"}{if $press->getSetting('contactName')} ({$press->getSetting('contactName')|strip|escape:"html"}){/if}</managingEditor>
		{/if}

		{if $press->getSetting('supportEmail')}
			<webMaster>{$press->getSetting('supportEmail')|strip|escape:"html"}{if $press->getSetting('contactName')} ({$press->getSetting('supportName')|strip|escape:"html"}){/if}</webMaster>
		{/if}

		{* <lastBuildDate/> *}
		{* <category/> *}
		{* <creativeCommons:license/> *}

		<generator>OMP {$ompVersion|escape}</generator>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<ttl>60</ttl>

		{foreach name=publishedMonographs from=$publishedMonographs item=publishedMonograph}
			<item>
				{* required elements *}
				<title>{$publishedMonograph->getLocalizedTitle()|strip|escape:"html"}</title>
				<link>{url page="catalog" op="book" path=$publishedMonograph->getId()}</link>
				<description>{$publishedMonograph->getLocalizedAbstract()|strip|escape:"html"}</description>

				{* optional elements *}
				<author>{$publishedMonograph->getAuthorString()|escape:"html"}</author>
				{* <category/> *}
				{* <comments/> *}
				{* <source/> *}

				<dc:rights>
					{translate|escape key="submission.copyrightStatement" copyrightYear=$publishedMonograph->getCopyrightYear() copyrightHolder=$publishedMonograph->getLocalizedCopyrightHolder()}
					{$publishedMonograph->getLicenseURL()|escape}
				</dc:rights>

				<guid isPermaLink="true">{url page="catalog" op="book" path=$publishedMonograph->getId()}</guid>
				<pubDate>{$publishedMonograph->getDatePublished()|date_format:"%a, %d %b %Y %T %z"}</pubDate>
			</item>
		{/foreach}{* publishedMonographs *}
	</channel>
</rss>
