{**
 * templates/catalog/monograph.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing monograph in the catalog.
 *}

<li class="pkp_catalog_monograph">
	<img src="{$baseUrl}/templates/images/book-default-small.png" />
	<div class="pkp_catalog_monographTitle">{$publishedMonograph->getLocalizedTitle()|strip_unsafe_html}</div>
	<div class="pkp_catalog_monoraphAbstract">{$publishedMonograph->getLocalizedAbstract()|strip_unsafe_html}</div>
</li>
