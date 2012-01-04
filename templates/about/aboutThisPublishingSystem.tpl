{**
 * aboutThisPublishingSystem.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Press / About This Publishing System.
 *
 * TODO: Display the image describing the system.
 *}
{strip}
{assign var="pageTitle" value="about.aboutThisPublishingSystem"}
{include file="common/header.tpl"}
{/strip}

<p>
{if $currentPress}
	{translate key="about.aboutOMPPress" ompVersion=$ompVersion}
{else}
	{translate key="about.aboutOMPSite" ompVersion=$ompVersion}
{/if}
</p>

<p align="center">

	<img src="{$baseUrl}/{$pubProcessFile}" style="border: 0;" alt="{translate key="about.aboutThisPublishingSystem.altText"}" />
</p>

{include file="common/footer.tpl"}
