{**
 * registerSite.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Site registration.
 *}
{strip}
{include file="common/header.tpl"}
{/strip}

{iterate from=presses item=press}
	{if !$notFirstPress}
		{translate key="user.register.selectPress"}:
		<ul>
		{assign var=notFirstPress value=1}
	{/if}
	<li><a href="{url press=$press->getPath() page="user" op="register"}">{$press->getLocalizedName()|escape}</a></li>
{/iterate}
{if $presses->wasEmpty()}
	{translate key="user.register.noPresses"}
{else}
	</ul>
{/if}

{include file="common/footer.tpl"}

