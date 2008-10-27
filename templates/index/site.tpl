{**
 * site.tpl
 *
 * Copyright (c) 2003-2008 John Willinsky
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

{if $intro}
<p>{$intro|nl2br}</p>
{/if}

{iterate from=presses item=press}

	{assign var="displayHomePageImage" value=$press->getLocalizedSetting('homepageImage')}
	{assign var="displayHomePageLogo" value=$press->getPressPageHeaderLogo(true)}
	{assign var="displayPageHeaderLogo" value=$press->getPressPageHeaderLogo()}

	<div style="clear:left;">
	{if $displayHomePageImage && is_array($displayHomePageImage)}
		<div class="homepageImage"><a href="{url context=$press->getPath()}" class="action"><img src="{$pressFilesPath}{$press->getPressId()}/{$displayHomePageImage.uploadName|escape:"url"}" {if $displayPageHeaderLogo.altText != ''}alt="{$displayPageHeaderLogo.altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} /></a></div>
	{elseif $displayHomePageLogo && is_array($displayHomePageLogo)}
		<div class="homepageImage"><a href="{url context=$press->getPath()}" class="action"><img src="{$pressFilesPath}{$press->getPressId()}/{$displayHomePageLogo.uploadName|escape:"url"}" {if $displayPageHeaderLogo.altText != ''}alt="{$displayPageHeaderLogo.altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} /></a></div>
	{elseif $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
		<div class="homepageImage"><a href="{url context=$press->getPath()}" class="action"><img src="{$pressFilesPath}{$press->getPressId()}/{$displayPageHeaderLogo.uploadName|escape:"url"}" {if $displayPageHeaderLogo.altText != ''}alt="{$displayPageHeaderLogo.altText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} /></a></div>
	{/if}
	</div>

	<h3>{$press->getPressName()|escape}</h3>
	{if $press->getPressDescription()}
		<p>{$press->getPressDescription()|nl2br}</p>
	{/if}

	<p><a href="{url context=$press->getPath()}" class="action">{translate key="site.pressView"}</a></p>
	<!--REGISTER | NEW RELEASES perhaps -->
{/iterate}

{include file="common/footer.tpl"}
