<!-- templates/about/site.tpl -->

{**
 * site.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press site.
 *
 * $Id$
 *}
{strip}
{assign var="pageTitle" value="about.aboutSite"}
{include file="common/header.tpl"}
{/strip}

{if !empty($about)}
	<p>{$about|nl2br}</p>
{/if}

<h3>{translate key="press.presses"}</h3>
<ul class="plain">
{iterate from=presses item=press}
	<li>&#187; <a href="{url press=$press->getPath() page="about" op="index"}">{$press->getLocalizedName()|escape}</a></li>
{/iterate}
</ul>

<a href="{url op="aboutThisPublishingSystem"}">{translate key="about.aboutThisPublishingSystem"}</a>

{include file="common/footer.tpl"}

<!-- / templates/about/site.tpl -->

