{**
 * site.tpl
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site index.
 *
 * $Id$
 *}
{strip}
{if $siteTitle}
	{assign var="pageTitleTranslated" value=$siteTitle}
{/if}
{include file="common/header.tpl"}
{/strip}

<br />

{if $intro}{$intro|nl2br}{/if}

{iterate from=presses item=press}

	{assign var="displayHomePageImage" value=$press->getLocalizedSetting('homepageImage')}
	{assign var="displayHomePageLogo" value=$press->getPressPageHeaderLogo(true)}
	{assign var="displayPageHeaderLogo" value=$press->getPressPageHeaderLogo()}

	<div style="clear:left;">
	{if $displayHomePageImage && is_array($displayHomePageImage)}
		<div class="homepageImage"><a href="{url press=$press->getPath()}" class="action"><img src="{$pressFilesPath}{$press->getId()}/{$displayHomePageImage.uploadName|escape:"url"}" {if $displayPageHeaderLogo.altText != ''}alt="{$displayPageHeaderLogo.altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} /></a></div>
	{elseif $displayHomePageLogo && is_array($displayHomePageLogo)}
		<div class="homepageImage"><a href="{url press=$press->getPath()}" class="action"><img src="{$pressFilesPath}{$press->getId()}/{$displayHomePageLogo.uploadName|escape:"url"}" {if $displayPageHeaderLogo.altText != ''}alt="{$displayPageHeaderLogo.altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} /></a></div>
	{elseif $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
		<div class="homepageImage"><a href="{url press=$press->getPath()}" class="action"><img src="{$pressFilesPath}{$press->getId()}/{$displayPageHeaderLogo.uploadName|escape:"url"}" {if $displayPageHeaderLogo.altText != ''}alt="{$displayPageHeaderLogo.altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} /></a></div>
	{/if}
	</div>

	<h3>{$press->getLocalizedName()|escape}</h3>
	{if $press->getDescription()}
		<p>{$press->getDescription()|nl2br}</p>
	{/if}

	<p><a href="{url press=$press->getPath()}" class="action">{translate key="site.pressView"}</a></p>
	<!--REGISTER | NEW RELEASES perhaps -->
{/iterate}

{include file="common/footer.tpl"}
