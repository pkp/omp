{**
 * templates/catalog/monograph.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing monograph in the catalog.
 *}

<li class="pkp_catalog_monograph">
	{assign var=coverImage var=$publishedMonograph->getCoverImage()}
	<a href="{url op="book" path=$publishedMonograph->getId()}"><img src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="thumbnail" monographId=$publishedMonograph->getId()}" /></a>
	<div class="pkp_catalog_monographTitle">{$publishedMonograph->getLocalizedTitle()|strip_unsafe_html}</div>
	<div class="pkp_catalog_monoraphAbstract">{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}</div>
</li>
