{**
 * pressSponsorship.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / Press Sponsorship.
 *}
{strip}
{assign var="pageTitle" value="about.pressSponsorship"}
{include file="common/header.tpl"}
{/strip}

{if not (empty($sponsorNote) && empty($sponsors))}
<div id="sponsors">
<h3>{translate key="about.sponsors"}</h3>

{if $sponsorNote}<p>{$sponsorNote|nl2br}</p>{/if}

<ul>
	{foreach from=$sponsors item=sponsor}
	{if $sponsor.url}
		<li><a href="{$sponsor.url|escape}">{$sponsor.institution|escape}</a></li>
	{else}
		<li>{$sponsor.institution|escape}</li>
	{/if}
	{/foreach}
</ul>
</div>
<div class="separator"></div>
{/if}

{if !empty($contributorNote) || (!empty($contributors) && !empty($contributors[0].name))}
<div id="contributors">
<h3>{translate key="about.contributors"}</h3>

{if $contributorNote}<p>{$contributorNote|nl2br}</p>{/if}

<ul>
	{foreach from=$contributors item=contributor}
	{if $contributor.name}
		{if $contributor.url}
			<li><a href="{$contributor.url|escape}">{$contributor.name|escape}</a></li>
		{else}
			<li>{$contributor.name|escape}</li>
		{/if}
	{/if}
	{/foreach}
</ul>
</div>
{/if}

{include file="common/footer.tpl"}

