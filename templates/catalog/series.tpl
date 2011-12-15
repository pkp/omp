{**
 * templates/catalog/series.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a public-facing series view in the catalog.
 *}
{strip}
{assign var="pageTitleTranslated" value=$series->getLocalizedTitle()}
{include file="common/header.tpl"}
{/strip}

<!-- Implement series view -->

{include file="common/footer.tpl"}
