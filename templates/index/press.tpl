{**
 * templates/index/press.tpl
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Press index page.
 *}
{include file="common/frontend/header.tpl"}

{if $publishedMonographs|@count > 0}
	{* Include the new releases *}
	{include file="catalog/monographs.tpl" publishedMonographs=$publishedMonographs monographListTitleKey="navigation.newReleases"}
{/if}

{if $homepageImage}
	<br />
	<div id="homepageImage">
		<img src="{$publicFilesDir}/{$homepageImage.uploadName|escape:"url"}" width="{$homepageImage.width|escape}" height="{$homepageImage.height|escape}" {if $homepageImage.altText != ''}alt="{$homepageImage.altText|escape}"{else}alt="{translate key="common.homepageImage.altText"}"{/if} />
	</div>
{/if}

{if $additionalHomeContent}
	<br />
	{$additionalHomeContent}
{/if}

{if $enableAnnouncementsHomepage}
	{* Display announcements *}
	<div id="announcementsHome">
		<h3 class="pkp_helpers_text_center">{translate key="announcement.announcementsHome"}</h3>
		{include file="announcements/announcements.tpl" displayLimit=true}
	</div>
{/if}

{if $spotlights|@count > 0}
	{include file="index/spotlights.tpl"}
{/if}

<div class="pkp_helpers_clear"></div>

{if !empty($socialMediaBlocks)}
	<div id="socialMediaBlocksContainer">
	{foreach from=$socialMediaBlocks item=block name=b}
		<div id="socialMediaBlock{$smarty.foreach.b.index}" class="socialMediaBlock pkp_helpers_clear">
			{$block}
		</div>
	{/foreach}
	</div>
{/if}

{include file="common/frontend/footer.tpl"}
