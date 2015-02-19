{**
 * templates/about/sponsorship.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / Press Sponsorship.
 *}
{strip}
{assign var="pageTitle" value="about.pressSponsorship"}
{include file="common/header.tpl"}
{/strip}

{url|assign:editUrl page="management" op="settings" path="press" anchor="affiliationAndSupport"}
{include file="common/linkToEditPage.tpl" editUrl=$editUrl}

{if not (empty($sponsorshipInfo.sponsorNote) && empty($sponsorshipInfo.sponsors))}
<div id="sponsors">
<h3>{translate key="about.sponsors"}</h3>

{if $sponsorshipInfo.sponsorNote}<p>{$sponsorshipInfo.sponsorNote|nl2br}</p>{/if}

<ul>
	{foreach from=$sponsorshipInfo.sponsors item=sponsor}
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

{if not (empty($sponsorshipInfo.contributorNote) && empty($sponsorshipInfo.contributors))}
<div id="contributors">
<h3>{translate key="about.contributors"}</h3>

{if $sponsorshipInfo.contributorNote}<p>{$sponsorshipInfo.contributorNote|nl2br}</p>{/if}

<ul>
	{foreach from=$sponsorshipInfo.contributors item=contributor}
	{if $contributor.institution}
		{if $contributor.url}
			<li><a href="{$contributor.url|escape}">{$contributor.institution|escape}</a></li>
		{else}
			<li>{$contributor.institution|escape}</li>
		{/if}
	{/if}
	{/foreach}
</ul>
</div>
{/if}

{include file="common/footer.tpl"}
