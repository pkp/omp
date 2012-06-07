{**
 * templates/common/breadcrumbs.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Breadcrumbs
 *}
<ul class="pkp_helpers_flatlist pkp_structure_breadcrumb align_left">
	{url|assign:homeUrl context=$homeContext page="index"}
	{translate|assign:"defaultSiteTitle" key="common.omp"}
	<li class="no_bullet">
		<a href="{$homeUrl}">{$siteTitle|default:$defaultSiteTitle}</a>
	</li>
	{foreach from=$pageHierarchy item=hierarchyLink}
		<li><a href="{$hierarchyLink[0]|escape}">{if not $hierarchyLink[2]}{translate key=$hierarchyLink[1]}{else}{$hierarchyLink[1]|escape}{/if}</a></li>
	{/foreach}
	{if $currentUrl != $homeUrl}
		<li class="current">{if !$requiresFormRequest}<a href="{$currentUrl|escape}" class="current">{/if}{$pageCrumbTitleTranslated}{if !$requiresFormRequest}</a>{/if}</li>
	{/if}
</ul>
