{**
 * breadcrumbs.tpl
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Breadcrumbs
 *
 *}
<div class="breadcrumbs">
	<ul class="flat_list breadcrumb">
		<li><a href="{url context=$homeContext page="index"}">{translate key="navigation.home"}</a> </li>
	{foreach from=$pageHierarchy item=hierarchyLink}
		<li><a href="{$hierarchyLink[0]|escape}" class="hierarchyLink">{if not $hierarchyLink[2]}{translate key=$hierarchyLink[1]}{else}{$hierarchyLink[1]|escape}{/if}</a> </li>
	{/foreach}
		{* Disable linking to the current page if the request is a post (form) request. Otherwise following the link will lead to a form submission error. *}
		<li>{if $requiresFormRequest}<span class="current">{else}<a href="{$currentUrl|escape}" class="current">{/if}{$pageCrumbTitleTranslated}{if $requiresFormRequest}</span>{else}</a>{/if}</li>
	</ul>
</div>

