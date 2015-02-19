{**
 * templates/catalog/newReleases.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * New Releases in the public-facing catalog
 *}
{strip}
{assign var="pageTitle" value="navigation.newReleases"}
{include file="common/header.tpl"}
{/strip}

{* Include the new release monograph list *}
{include file="catalog/monographs.tpl" publishedMonographs=$publishedMonographs}

{include file="common/footer.tpl"}
