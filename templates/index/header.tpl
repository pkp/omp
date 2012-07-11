{**
 * templates/index/header.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site header div contents.
 *}
<div class="pkp_structure_content">
	<div class="unit size1of5">
		<div class="pkp_structure_masthead">
			<h1 style="margin: 0; padding: 0;">
				{url|assign:homeUrl context=$homeContext page="index"}
				{if $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
					<a href="{$homeUrl}"><img src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" width="{$displayPageHeaderLogo.width|escape}" height="{$displayPageHeaderLogo.height|escape}" {if $displayPageHeaderLogoAltText != ''}alt="{$displayPageHeaderLogoAltText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} /></a>
				{elseif $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}
					<a href="{$homeUrl}"><img src="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" width="{$displayPageHeaderTitle.width|escape}" height="{$displayPageHeaderTitle.height|escape}" {if $displayPageHeaderTitleAltText != ''}alt="{$displayPageHeaderTitleAltText|escape}"{else}alt="{translate key="common.pageHeader.altText"}"{/if} /></a>
				{elseif $displayPageHeaderTitle}
					<a href="{$homeUrl}">{$displayPageHeaderTitle}</a>
				{elseif $alternatePageHeader}
					<a href="{$homeUrl}">{$alternatePageHeader}</a>
				{else}
					<a href="{$homeUrl}"><img src="{$baseUrl}/templates/images/structure/omp_logo.png" alt="{$applicationName|escape}" title="{$applicationName|escape}" width="180" height="90" /></a>
				{/if}
			</h1>
		</div><!-- pkp_structure_masthead -->
	</div>
	<div class="unit size4of5">
		<div class="pkp_structure_navigation">
			{include file="index/sitenav.tpl"}
			{include file="index/localnav.tpl"}
		</div>
	</div>
</div><!-- pkp_structure_content -->
